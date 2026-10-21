<?php

namespace common\models;

use api\exceptions\MinProductQuantityException;
use api\helpers\NotificationHelper;
use api\helpers\OrderHelper;
use Throwable;
use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\StaleObjectException;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "order_product".
 *
 * @property int $id
 * @property int $order_id
 * @property int $product_id
 * @property string $product_uuid
 * @property int $quantity
 * @property string $price
 * @property int $created_at
 * @property Product|ActiveQuery $product
 * @property int $updated_at
 * @property int $total_sum
 * @property int $view_only
 * @property int $custom
 * @property string $name
 * @property ActiveQuery|SpecificationProduct $specificationProduct
 * @property mixed|int $availableQuantity
 * @property ActiveQuery|Order $order
 * @property void $productStatus
 * @property string $comment
 * @property int $approved
 * @property int $approved_quantity
 * @property int $spec_quantity
 * @property ActiveQuery|SpecificationProduct $specificationsProduct
 * @property int $start_quantity
 * @property string $parent_category_uuid
 * @property OrderProductHistory|ActiveQuery $orderProductHistory
 * @property int $catalog
 * @property int $on_approve_quantity
 * @property boolean $is_delete
 */
class OrderProduct extends ActiveRecord
{
    const STATUS_AVAILABLE = 1;
    const STATUS_NOT_ENOUGH_QUANTITY = 2;
    const STATUS_NOT_AVAILABLE_LOCATION = 3;
    const STATUS_APPROVED = 4;
    const STATUS_APPROVED_PART = 5;

    const NOT_APPROVED = 0;
    const APPROVED_ALL = 1;
    const APPROVED_PART = 2;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'order_product';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['order_id', 'product_id', 'created_at', 'updated_at', 'view_only', 'custom',
                'approved', 'approved_quantity', 'spec_quantity', 'start_quantity', 'catalog',
                'on_approve_quantity'], 'integer'],
            [['price', 'total_sum','quantity'], 'number'],
            [['product_uuid', 'name'], 'string', 'max' => 255],
            [['comment', 'parent_category_uuid'], 'string'],
            ['is_delete', 'boolean']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'           => 'ID',
            'order_id'     => 'Order ID',
            'product_id'   => 'Product ID',
            'product_uuid' => 'Product Uuid',
            'quantity'     => 'Quantity',
            'price'        => 'Price',
            'created_at'   => 'Created At',
            'updated_at'   => 'Updated At',
        ];
    }

    /**
     * @return ActiveQuery | Product
     */
    public function getProduct()
    {
        return $this->hasOne(Product::class, ['uuid' => 'product_uuid']);
    }

    /**
     * @return false|int
     * @throws MinProductQuantityException
     * @throws StaleObjectException
     * @throws Throwable
     */
    public function deleteFromOrder()
    {
        $orderId = $this->order_id;
        $productUuid = $this->product_uuid;
        $this->addToSpecification();
        if ($this->order->status != Order::STATUS_ON_APPROVE) {
            $result = $this->delete();
        } else {
            $totalProducts = $this->order->getProduct()->count();
            if ($totalProducts > 1) {
                $result = $this->delete();
            } else {
                throw new MinProductQuantityException('В замовленні повинен бути хоча б один товар.');
            }
        }
        if ($result) {
            $order = Order::find()->where(['id' => $orderId])->one();
            if ($order) {
                $order->total_sum = OrderHelper::getTotalSum($orderId);
                $order->save();
                $cart = Cart::find()
                    ->where(['order_id' => $orderId])
                    ->one();
                if ($cart) {
                    $cartProduct = CartProduct::find()
                        ->where(['product_uuid' => $productUuid])
                        ->andWhere(['cart_id' => $cart->id])
                        ->one();
                    if ($cartProduct) {
                        if ($cartProduct->new_quantity) {
                            $cartProduct->quantity = $cartProduct->new_quantity;
                            $cartProduct->new_quantity = 0;
                        }
                        $cartProduct->status = CartProduct::STATUS_NEW;
                        $cartProduct->saveWithSocket($orderId);
                        try {
                            $message = 'У замовленні був видалений товар ' . ArrayHelper::getValue($cartProduct->product, 'name', '')
                                . '. У кошику його кількість складає ' . $cartProduct->quantity . ' ' .
                                ArrayHelper::getValue($cartProduct->product, 'unit.name', 'шт.');
                            $cartProduct->addHistory($message);
                        } catch (\Exception $e) {
                            \Yii::error($e);
                        }
                    }
                }
            }
        }
        return $result;
    }

    public function addToSpecification()
    {
        $specification = Specification::find()
            ->joinWith('locations')
            ->where(['company_id' => $this->order->company_id])
            ->andWhere(['specification_location.location_id' => $this->order->location_id])
            ->all();
        $query = OrderProduct::find()->joinWith([
            'product',
            'specificationsProduct' => function ($query) use ($specification) {
                if ($specification) {
                    $query->onCondition([
                        'in', 'specification_product.specification_id', ArrayHelper::getColumn($specification, 'id')]);
                }
            }
        ]);
        $query->where(['order_product.id' => $this->id]);
        $product = $query->one();
        if ($product && $product->specificationsProduct) {
            $status = ArrayHelper::getColumn($product->specificationsProduct, 'status');
            $key = array_search(1, $status);
            if (false !== $key && $product->specificationsProduct[$key]->quantity != '-1') {
                $object = $product->specificationsProduct[$key];
                $newQuantity = $object->quantity + $product->spec_quantity;
                if ($newQuantity >= 0) {
                    $object->quantity = $newQuantity;
                    $product->spec_quantity = $product->quantity;
                } else {
                    if ($product->approved != OrderProduct::NOT_APPROVED) {
                        $product->spec_quantity = $object->quantity;
                    }
                    $object->quantity = 0;
                }
                $result = $product->save();
                $result2 = $object->save();
                return $result && $result2;
            }
        }
        return false;
    }

    public function beforeDelete()
    {
        $order = Order::findOne(['id' => $this->order_id]);
        /** @var  $user User */
        $user = Yii::$app->user->getIdentity();
        NotificationHelper::deleteProductFromOrder($order, $user, $this);
        NotificationHelper::deleteProductFromOrderOwner($order, $user, $this);
        return parent::beforeDelete(); // TODO: Change the autogenerated stub
    }

    /**
     * @return ActiveQuery | SpecificationProduct
     */
    public function getSpecificationProduct()
    {
        return $this->hasOne(SpecificationProduct::class, ['product_uuid' => 'product_uuid']);
    }

    /**
     * @return ActiveQuery | SpecificationProduct
     */
    public function getSpecificationsProduct()
    {
        return $this->hasMany(SpecificationProduct::class, ['product_uuid' => 'product_uuid']);
    }

    /**
     * @return int|mixed
     */
    public function getAvailableQuantity()
    {
        if ($this->specificationsProduct) {
            $status = ArrayHelper::getColumn($this->specificationsProduct, 'status');
            $key = array_search(1, $status);
            if ($key !== false) {
                return $this->specificationsProduct[$key]->quantity;
            }
        }
        return -1;
    }

    /**
     * @return int|mixed
     */
    public function getCatalogValue()
    {
        return $this->catalog;
    }

    /**
     * @return ActiveQuery | Order
     */
    public function getOrder()
    {
        return $this->hasOne(Order::class, ['id' => 'order_id']);
    }

    public function getProductStatus()
    {
        $specQuantity = $this->availableQuantity - $this->quantity;
        if ($this->approved === self::APPROVED_ALL) {
            return self::STATUS_APPROVED;
        } elseif ($this->approved === self::APPROVED_PART) {
            return self::STATUS_APPROVED_PART;
        } elseif ((!$this->view_only && ($this->availableQuantity == -1 || $specQuantity >= 0)) ||
            (!$this->view_only && !in_array($this->order->status, [Order::STATUS_OPEN, Order::STATUS_MODERATION])) ||
            ($this->availableQuantity == -1 && $specQuantity >= 0)) {
            return self::STATUS_AVAILABLE;
        } elseif ($this->availableQuantity != -1 && $specQuantity < 0) {
            return self::STATUS_NOT_ENOUGH_QUANTITY;
        } else {
            return self::STATUS_NOT_AVAILABLE_LOCATION;
        }
    }

    public static function getStatuses()
    {
        return [
            self::STATUS_AVAILABLE              => [
                'id'    => self::STATUS_AVAILABLE,
                'icon'  => 'done',
                'text'  => 'Обрана кількість товарів повністю доступна',
                'style' => 'color: white; background-color: #19B200;'
            ],
            self::STATUS_NOT_ENOUGH_QUANTITY    => [
                'id'    => self::STATUS_NOT_ENOUGH_QUANTITY,
                'icon'  => 'priority_high',
                'text'  => 'Обрана кількість товарів більше, ніж доступно для вашої локації. Замовлення буде відправлене на попереднє узгодження вашим менеджером',
                'style' => 'color: white; background-color: #fcbd00;'
            ],
            self::STATUS_NOT_AVAILABLE_LOCATION => [
                'id'    => self::STATUS_NOT_AVAILABLE_LOCATION,
                'icon'  => 'priority_high',
                'text'  => 'Обрані товари недоступні для зазначеної локації. Замовлення буде відправлене на попереднє узгодження вашим менеджером',
                'style' => 'color: white; background-color: #F24949;'
            ],
            self::STATUS_APPROVED               => [
                'id'    => self::STATUS_APPROVED,
                'icon'  => 'done',
                'text'  => 'Вся кількість товарів узгоджена',
                'style' => 'color: white; background-color: #19B200;'
            ],
            self::STATUS_APPROVED_PART          => [
                'id'    => self::STATUS_APPROVED_PART,
                'icon'  => 'priority_high',
                'text'  => 'Запрошена кількість товарів не узгоджена менеджером',
                'style' => 'color: white; background-color: #fcbd00;'
            ],
        ];
    }

    /**
     * @param $to
     * @return bool
     */
    public function changeProduct($to)
    {
        $to = trim($to);
        $key = $to . __CLASS__;
        $cache = Yii::$app->cache;
        if ($cache->exists($key)) {
            $newProduct = $cache->get($key);
        } else {
            $newProduct = Product::find()
                ->where(['uuid' => $to])
                ->one();
            $cache->set($key, $newProduct, 3);
        }
        if ($newProduct) {
            $categories = Category::find()
                ->where(['is', 'parent_id', null])
                ->asArray()
                ->all();
            $productsCat = ProductCategory::find()
                ->where(['in', 'category_uuid', ArrayHelper::getColumn($categories, 'uuid')])
                ->andWhere(['product_uuid' => $newProduct->uuid])
                ->asArray()
                ->one();
            if ($productsCat) {
                $oldUuid = $this->product_uuid;
                $this->product_id = $newProduct->id;
                $this->product_uuid = $newProduct->uuid;
                $this->parent_category_uuid = ArrayHelper::getValue($productsCat, 'category_uuid', '');
                $result = $this->save();
                if ($result) {
                    $history = new OrderProductHistory([
                        'created_at'       => date('U'),
                        'from_uuid'        => $oldUuid,
                        'to_uuid'          => $newProduct->uuid,
                        'order_id'         => $this->order_id,
                        'user_id'          => Yii::$app->user->getId(),
                        'order_product_id' => $this->id
                    ]);
                    $history->save();
                    return $this->order_id;
                }
                return false;
            }
        }
        return false;
    }

    /**
     * @return ActiveQuery | OrderProductHistory
     */
    public function getOrderProductHistory()
    {
        return $this->hasOne(OrderProductHistory::class, ['order_product_id' => 'id'])
            ->orderBy(['id' => SORT_DESC]);
    }
}
