<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 13.08.2019
 * Time: 11:37
 */

namespace api\models\forms;

use api\exceptions\BudgetNotAvailableException;
use api\exceptions\MissingProductException;
use api\exceptions\NotEnoughBudgetException;
use api\exceptions\NotEnoughQuantityProductException;
use api\helpers\NotificationHelper;
use api\helpers\NumberHelper;
use api\helpers\OrderHelper;
use api\services\OrderCoordinationHelper;
use common\models\Budget;
use common\models\Cart;
use common\models\CartProduct;
use common\models\CatalogLimitation;
use common\models\Category;
use common\models\CategoryExclude;
use common\models\Company;
use common\models\Order;
use common\models\OrderCoordination;
use common\models\OrderProduct;
use common\models\Product;
use common\models\ProductCategory;
use common\models\Specification;
use common\models\SpecificationLocation;
use common\models\SpecificationProduct;
use common\models\User;
use Exception;
use Throwable;
use Yii;
use yii\base\Model;
use yii\db\StaleObjectException;
use yii\helpers\ArrayHelper;

/**
 * Class AddProductForm
 * @package api\models\forms
 */
class AddProductForm extends Model
{
    public $order_id;
    public $products;

    public function rules()
    {
        return [
            [['order_id', 'products'], 'required'],
            [['order_id'], 'validateStatus']
        ];
    }

    /**
     * @return bool|void
     */
    public function validateStatus()
    {
        $order = Order::find()
            ->where(['id' => $this->order_id])
            ->one();
        if ($order && $order->status != Order::STATUS_OPEN) {
            return $this->addError('order_id', 'Статус заказа не позволяет добавить к нему товар');
        }
        return true;
    }

    /**
     * @param bool $byId
     * @return bool
     * @throws Throwable
     */
    public function addProducts($byId = true)
    {
        /** @var  $user  User */
        $user = Yii::$app->user->getIdentity();
        $company = Company::findOne(['id' => $user->company_id]);
        $order = Order::findOne(['id' => $this->order_id]);
        if (!empty($this->products) && $order && $company) {
            $specificationProductsIds = [];
            $catalogProductIds = [];
            $specifications = Specification::find()
                ->where(['company_id' => $user->company_id])
                ->active()
                ->asArray()
                ->all();
            if ($specifications) {
                $specification = SpecificationLocation::find()
                    ->where(['in', 'specification_id', ArrayHelper::getColumn($specifications, 'id')])
                    ->andWhere(['location_id' => $order->location_id])
                    ->all();
                if ($specification) {
                    $specificationProducts = SpecificationProduct::find()
                        ->where(['in', 'specification_id', ArrayHelper::getColumn($specification, 'specification_id')])
                        ->andWhere(['status' => 1])
                        ->asArray()
                        ->all();
                    $specificationProductsIds = ArrayHelper::getColumn($specificationProducts, 'product_uuid');
                    $specificationProducts = ArrayHelper::index($specificationProducts, function ($element) {
                        return $element['product_uuid'] . '-' . $element['status'];
                    });
                }
            }
            $limitation = CategoryExclude::find()
                ->where(['company_id' => $user->company_id])
                ->asArray()
                ->all();
            $catalogProductsQuery = ProductCategory::find();
            if ($limitation) {
                $catalogProductsQuery->where([
                    'not in',
                    'category_uuid',
                    ArrayHelper::getColumn($limitation, 'category_uuid')
                ]);
            }
            $catalogProducts = $catalogProductsQuery->asArray()
                ->all();
            if ($catalogProducts) {
                $catalogProductIds = ArrayHelper::getColumn($catalogProducts, 'product_uuid');
            }
            $orderProducts = [];
            $orderProductsName = [];
            $categories = Category::find()
                ->where(['is', 'parent_id', null])
                ->asArray()
                ->all();
            $productsCat = ProductCategory::find()
                ->where(['in', 'category_uuid', ArrayHelper::getColumn($categories, 'uuid')])
                ->asArray()
                ->all();
            $products = ArrayHelper::index($productsCat, 'product_uuid');
            foreach ($this->products as $product) {
                if ($byId) {
                    $item = Product::find()->where(['id' => $product['id']])->one();
                } else {
                    $item = Product::find()
                        ->where(['uuid' => ArrayHelper::getValue($product, 'product_uuid')])
                        ->one();
                }
                if (Yii::$app->user->can('orderShowBtn'))
                {
                    if (!empty($specificationProductsIds) && in_array($item->uuid, $specificationProductsIds) || !empty($catalogProductIds) && in_array($item->uuid, $catalogProductIds))
                    {
                        if (in_array($item->uuid, $specificationProductsIds))
                        {
                            $use_markup = ArrayHelper::getValue($specificationProducts, $item->uuid . '-1.use_markup');
                            $markup = ArrayHelper::getValue($specificationProducts, $item->uuid . '-1.markup');
                            $prodPrice = (ArrayHelper::getValue($specificationProducts, $item->uuid . '-1.price') > 0) ?
                                ArrayHelper::getValue($specificationProducts, $item->uuid . '-1.price') :
                                $item->price;
                            $price = ($use_markup) ?
                                $item->buy_price * ((100 + $markup) / 100) :
                                $prodPrice;
                        }
                        else
                        {
                            $price = $item->price;
                        }
                        $viewOnly = 0;
                    }
                    else
                    {
                        $price = $item->price;
                        $viewOnly = 1;
                    }
                }
                else
                {
                    if (!empty($specificationProductsIds) && in_array($item->uuid, $specificationProductsIds))
                    {
                        $use_markup = ArrayHelper::getValue($specificationProducts, $item->uuid . '-1.use_markup');
                        $markup = ArrayHelper::getValue($specificationProducts, $item->uuid . '-1.markup');
                        $prodPrice = (ArrayHelper::getValue($specificationProducts, $item->uuid . '-1.price') > 0) ? ArrayHelper::getValue($specificationProducts, $item->uuid . '-1.price') : $item->price;
                        $price = ($use_markup) ? $item->buy_price * ((100 + $markup) / 100) : $prodPrice;
                        $viewOnly = 0;
                    }
                    else
                    {
                        $price = $item->price;
                        $viewOnly = 1;
                    }
                }

                if ($item) {
                    $catalog = 1;
                    if (!empty($specificationProductsIds) && in_array($item->uuid, $specificationProductsIds)) {
                        $catalog = 0;
                    }
                    $quantity = $product['quantity'];
                    $newQuantity = ArrayHelper::getValue($product, 'new_quantity', 0);
                    if ($newQuantity) {
                        $quantity = $newQuantity;
                    }
                    $orderProducts[] = [
                        'order_id'             => $this->order_id,
                        'product_id'           => $item->id,
                        'product_uuid'         => $item->uuid,
                        'quantity'             => $quantity,
                        'price'                => $price,
                        'total_sum'            => NumberHelper::calculateTaxPriceItem($item, $price, $quantity),
                        'created_at'           => date('U'),
                        'updated_at'           => date('U'),
                        'view_only'            => $viewOnly,
                        'parent_category_uuid' => ArrayHelper::getValue($products, $item->uuid . '.category_uuid', ''),
                        'catalog'              => $catalog
                    ];
                    $orderProductsName[] = $item->name;
                }
            }

            if (!empty($orderProducts)) {
                $orderProducts = ArrayHelper::index($orderProducts, 'product_uuid');
                $currentProduct = OrderProduct::find()
                    ->where(['order_id' => $this->order_id])
                    ->andWhere(['in', 'product_uuid', ArrayHelper::getColumn($orderProducts, 'product_uuid')])
                    ->all();
                if ($currentProduct) {
                    foreach ($currentProduct as $curProduct) {
                        /** @var  $curProduct OrderProduct */
                        $quantity = ArrayHelper::getValue(
                            $orderProducts,
                            $curProduct->product_uuid . '.quantity'
                        );
                        $curProduct->quantity = $quantity;
                        $curProduct->total_sum = NumberHelper::calculateTaxPrice($curProduct);
                        $curProduct->view_only = $orderProducts[$curProduct->product_uuid]['view_only'];
                        $curProduct->save();
                        unset($orderProducts[$curProduct->product_uuid]);
                    }
                }
                if (!empty($orderProducts)) {
                    try {
                        Yii::$app->db->createCommand()->batchInsert(
                            OrderProduct::tableName(),
                            [
                                'order_id',
                                'product_id',
                                'product_uuid',
                                'quantity',
                                'price',
                                'total_sum',
                                'created_at',
                                'updated_at',
                                'view_only',
                                'parent_category_uuid',
                                'catalog'
                            ],
                            $orderProducts
                        )->execute();
                    } catch (Exception $e) {
                        Yii::error($e);
                        return false;
                    }
                }
            }
        }
        $total = OrderProduct::find()
            ->where(['order_id' => $this->order_id])
            ->andWhere(['view_only' => 0])
            ->andWhere(['is_delete' => 0])
            ->sum('total_sum');
        $order = Order::findOne(['id' => $this->order_id]);
        if (isset($orderProductsName) && !empty($orderProductsName)) {
            NotificationHelper::addProductsToOrder($order, $user, $orderProductsName);
            NotificationHelper::addProductsToOrderOwner($order, $user, $orderProductsName);
        }
        if ($order) {
            $order->total_sum = NumberHelper::convert($total);
            $order->save();
        }
        return true;
    }

    /**
     * @param $id
     * @param $quantity
     * @return bool
     * @throws NotEnoughBudgetException
     * @throws Throwable
     * @throws NotEnoughQuantityProductException
     * @throws \yii\db\Exception
     * @throws StaleObjectException
     */
    public static function changeQuantity($id, $quantity, $changeQty)
    {
        if (is_numeric($quantity)) {
            $needModeration = false;
            $transaction = Yii::$app->db->beginTransaction();
            $model = OrderProduct::find()
                ->joinWith([
                    'order',
                    'specificationsProduct'
                ])
                ->where(['order_product.id' => $id])
                ->one();
            if ($model) {
                $oldQuantity = $model->quantity;
                if ($model->order->status === Order::STATUS_MODERATION) {
                    if ($quantity > $model->start_quantity) {
                        $model->quantity = $model->start_quantity;
                        $model->approved_quantity = $quantity;
                        $model->approved = OrderProduct::APPROVED_ALL;
                    } elseif ($model->start_quantity != $quantity && $quantity >= 0) {
                        $model->approved_quantity = $quantity;
                        $model->quantity = $quantity;
                        $model->approved = OrderProduct::APPROVED_PART;
                        $model->total_sum = NumberHelper::calculateTaxPrice($model);
                    } else {
                        $model->approved_quantity = $quantity;
                        $model->quantity = $quantity;
                        $model->approved = OrderProduct::APPROVED_ALL;
                        $model->total_sum = NumberHelper::calculateTaxPrice($model);
                    }
                    $model->save();
                } elseif ($model->order->status === Order::STATUS_MOD_APPROVE) {
                    if ($quantity > 0) {
                        if ($model->approved_quantity && $quantity > $model->approved_quantity) {
                            $model->approved = OrderProduct::NOT_APPROVED;
                            $model->order->status = Order::STATUS_OPEN;
                            $model->order->save();
                        }
                        $model->quantity = $quantity;
                        $model->total_sum = NumberHelper::calculateTaxPrice($model);
                        $model->save();
                    } else {
                        $model->delete();
                    }
                } elseif ($model->order->status === Order::STATUS_ON_APPROVE) {
                    if ($quantity > 0) {
                        if ($model->availableQuantity != -1 &&
                            $quantity > $model->availableQuantity + $model->spec_quantity) {
                            $model->approved = OrderProduct::NOT_APPROVED;
                        }
                        $oldQuantity = $model->quantity;
                        $model->quantity = $quantity;
                        $model->total_sum = NumberHelper::calculateTaxPrice($model);
                        $model->save();
                        if ($model->availableQuantity != -1 &&
                            $quantity > $model->availableQuantity + $model->spec_quantity) {
                            $needModeration = true;
                        }
//                        if ($model->order->budget && $model->order->budget->budget_restriction) {
//                            $availableBudget = ArrayHelper::getValue($model->order->budget, 'availableSum', 0);
//                            $minus = ($quantity - $oldQuantity) * $model->price;
//                            if ($quantity > $oldQuantity && $availableBudget - $minus < 0) {
//                                $model->order->changeStatusToStart();
//                            }
//                        }
                    } else {
                        $model->delete();
                    }
                } else {
                    if ($quantity > 0) {
                        if ($model->approved_quantity && $quantity > $model->approved_quantity) {
                            $model->approved = OrderProduct::NOT_APPROVED;
                        }
                        $model->quantity = $quantity;
                        $model->total_sum = NumberHelper::calculateTaxPrice($model);
                        $model->save();
                    } else {
                        $model->delete();
                    }
                }

                $order = Order::findOne(['id' => $model->order_id]);
                if ($order) {
                    $order->total_sum = OrderHelper::getTotalSum($model->order_id);
                    $order->save();
                    if ($order->status == Order::STATUS_ON_APPROVE) {
                        (new OrderCoordinationHelper($order))->goToNextStep();
                    }
                }
                $budget = Budget::find()
                    ->where(['id' => $order->budget_id])
                    ->andWhere(['budget_restriction' => 1])
                    ->one();
                if ($budget && $budget->availableSum < 0) {
                    $transaction->rollBack();
                    throw new NotEnoughBudgetException('Перевищено бюджет - зміни не збережено!');
                }
                if ($needModeration) {
                    $model->order->calculateAvailableSpecificationProduct(false);
                    $model->order->setStatusModeration();
                }
                $transaction->commit();
                $cart = Cart::find()
                    ->where(['order_id' => $model->order_id])
                    ->one();
                if ($cart) {
                    $cartProduct = CartProduct::find()
                        ->where(['product_uuid' => $model->product_uuid])
                        ->andWhere(['cart_id' => $cart->id])
                        ->one();
                    if ($cartProduct) {
                        if ($cartProduct->new_quantity) {
                            if ($cartProduct->new_quantity == $model->quantity) {
                                $cartProduct->status = CartProduct::STATUS_AT_ORDER;
                                $cartProduct->quantity = $cartProduct->new_quantity;
                                $cartProduct->new_quantity = 0;
                            } else {
                                if($changeQty != false) {
                                    $cartProduct->status = CartProduct::STATUS_CHANGE_QUANTITY;
                                }

                                $cartProduct->quantity = $oldQuantity;
                                $cartProduct->new_quantity = $quantity;
                            }
                        } else {
                            if ($cartProduct->quantity == $model->quantity) {
                                $cartProduct->status = CartProduct::STATUS_AT_ORDER;
                                $cartProduct->new_quantity = 0;
                            } else {
                                if($changeQty != false) {
                                    $cartProduct->status = CartProduct::STATUS_CHANGE_QUANTITY;
                                }
//                                $cartProduct->new_quantity = $cartProduct->quantity;
//                                $cartProduct->quantity = $model->quantity;
                                $cartProduct->quantity = $oldQuantity;
                                $cartProduct->new_quantity = $quantity;
                            }
                        }
                        $cartProduct->saveWithSocket($model->order_id);
                        try {
                            $message = 'У замовленні була змінена кількість товару ' .
                                ArrayHelper::getValue($cartProduct->product, 'name', '') . ' з ' . $oldQuantity .
                                ' на ' . $quantity . ' ' .
                                ArrayHelper::getValue($cartProduct->product, 'unit.name', 'шт.') .
                                '. У кошику його кількість складає ' . $cartProduct->quantity .
                                ' ' . ArrayHelper::getValue($cartProduct->product, 'unit.name', 'шт.');
                            $cartProduct->addHistory($message);
                        } catch (Exception $e) {
                            Yii::error($e);
                        }
                    }
                }
                return true;
            }
        }
        return false;
    }
}
