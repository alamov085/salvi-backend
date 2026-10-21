<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 24.12.2019
 * Time: 10:37
 */

namespace api\models\forms;

use api\helpers\NumberHelper;
use common\dto\CartResponseDto;
use common\models\Cart;
use common\models\CartProduct;
use common\models\CartProductHistory;
use common\models\OrderProductChangeHistory;
use common\models\Order;
use common\models\OrderProduct;
use common\models\Product;
use common\models\User;
use Exception;
use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;

/**
 * Class CartForm
 * @package api\models\forms
 */
class CartForm extends Model
{
    public $id;
    public $order_id;
    public $product_id;
    public $quantity;
    public $price;
    public $is_specification;
    public $is_strict_multiplicity;
    public $is_temperature_conditions;
    public $discount_price;

    public function rules()
    {
        return [
            [['quantity', 'order_id', 'product_id', 'id','is_specification','is_strict_multiplicity','is_temperature_conditions'], 'integer'],
            [['order_id'], 'required'],
            [['discount_price'], 'safe'],
            ['price', 'string']
        ];
    }

    public function addProduct()
    {
        /** @var  $user User */
        $user = \Yii::$app->user->getIdentity();

        if ($this->order_id) {
            $cart = Cart::find()
                ->where(['company_id' => $user->company_id])
                ->andWhere(['order_id' => $this->order_id])
                ->one();
        } else {
            $cart = Cart::find()
                ->where(['order_id' => 0])
                ->andWhere(['company_id' => $user->company_id])
                ->andWhere(['user_id' => $user->id])
                ->one();
        }
        if ($this->product_id && $cart) {
            $product = Product::find()
                ->where(['id' => $this->product_id])
                ->one();
            if ($product) {
                $price = +str_replace(',', '', $this->price);
                $model = CartProduct::find()
                    ->where(['cart_id' => $cart->id])
                    ->andWhere(['product_uuid' => $product->uuid])
                    ->one();
                if ($model) {
                    $model->new_quantity = $this->quantity;
                    $model->total_sum = NumberHelper::calculateTaxPriceItem(
                        $model->product,
                        $model->price,
                        $this->quantity
                    );
                    if ($model->status == CartProduct::STATUS_AT_ORDER) {
                        $model->status = CartProduct::STATUS_CHANGE_QUANTITY;
                    }
                    $result = $model->save();
                    if ($result) {
                        $message = 'Змінена кількість товару ' . ArrayHelper::getValue($model->product, 'name', '')
                            . ' з ' . $model->quantity . ' до ' . $model->new_quantity . ' ' .
                            ArrayHelper::getValue($model->product, 'unit.name', 'шт.');
                        $model->addHistory($message);
                    }
                } else {

                    if(!is_null($this->discount_price))
                    {
                        $this->discount_price = '0.00';
                    }

                    $model = new CartProduct(
                        [
                            'cart_id'      => $cart->id,
                            'product_uuid' => $product->uuid,
                            'product_id'   => $product->id,
                            'status'       => CartProduct::STATUS_NEW,
                            'quantity'     => $this->quantity,
                            'new_quantity' => 0,
                            'price'        => $price,
                            'total_sum'    => NumberHelper::calculateTaxPriceItem($product, $price, $this->quantity),
                            'is_delete'    => $product->deleted,
                            'is_specification'    => $this->is_specification,
                            'is_strict_multiplicity'    => $this->is_strict_multiplicity,
                            'is_temperature_conditions'    => $this->is_temperature_conditions,
                            'discount_price'    => $this->discount_price,
                        ]
                    );
                    $result = $model->save();
                }

                return new CartResponseDto(['result' => $result, 'total' => $cart->recalculateTotal()]);
            }
        }
        return new CartResponseDto();
    }

    public function deleteProduct()
    {
        /** @var  $user User */
        $user = \Yii::$app->user->getIdentity();

        if ($this->order_id) {
            $cart = Cart::find()
                ->where(['company_id' => $user->company_id])
                ->andWhere(['order_id' => $this->order_id])
                ->one();
        } else {
            $cart = Cart::find()
                ->where(['order_id' => 0])
                ->andWhere(['company_id' => $user->company_id])
                ->andWhere(['user_id' => $user->id])
                ->one();
        }

        if ($this->id && $cart) {
            $product = CartProduct::find()
                ->where(
                    [
                        'and',
                        ['id' => $this->id],
                        ['cart_id' => $cart->id]
                    ]
                )->one();
            if (!$product) {
                return new CartResponseDto(['result' => false, 'total' => $cart->total]);
            }
            $result = $product->delete();
            return new CartResponseDto(['result' => $result, 'total' => $cart->recalculateTotal()]);
        } elseif ($cart) {
            $product = CartProduct::find()
                ->where(
                    [
                        'and',
                        ['product_id' => $this->product_id],
                        ['cart_id' => $cart->id]
                    ]
                )->one();
            if (!$product) {
                return new CartResponseDto(['result' => false, 'total' => $cart->total]);
            }
            $result = $product->delete();
            return new CartResponseDto(['result' => $result, 'total' => $cart->recalculateTotal()]);
        }
        return new CartResponseDto();
    }

    public function updateProduct()
    {
        if ($this->quantity <= 0) {
            return new CartResponseDto();
        }
        /** @var  $user User */
        $user = \Yii::$app->user->getIdentity();

        if ($this->order_id) {
            $cart = Cart::find()
                ->where(['company_id' => $user->company_id])
                ->andWhere(['order_id' => $this->order_id])
                ->one();
        } else {
            $cart = Cart::find()
                ->where(['order_id' => 0])
                ->andWhere(['company_id' => $user->company_id])
                ->andWhere(['user_id' => $user->id])
                ->one();
        }
        if ($this->id && $cart) {
            $product = CartProduct::find()
                ->where(['id' => $this->id])
                ->andWhere(['cart_id' => $cart->id])
                ->one();
        } elseif ($cart) {
            $product = CartProduct::find()
                ->where(['product_id' => $this->product_id])
                ->andWhere(['cart_id' => $cart->id])
                ->one();
        } else {
            return new CartResponseDto();
        }
        if ($product) {
            $orderProduct = OrderProduct::find()
                ->where(['order_id' => $cart->order_id])
                ->andWhere(['product_uuid' => $product->product_uuid])
                ->one();
            $product->new_quantity = $this->quantity;
            
            if(!is_null($this->is_strict_multiplicity) || isset($this->is_strict_multiplicity))
            {
	            $product->is_strict_multiplicity = $this->is_strict_multiplicity;
            }
            
            if(!is_null($this->is_temperature_conditions) || isset($this->is_temperature_conditions))
            {
	            $product->is_temperature_conditions = $this->is_temperature_conditions;
            }

            if((!is_null($this->discount_price) || isset($this->discount_price)) && !empty($this->discount_price))
            {
	            $product->discount_price = $this->discount_price;
            }
            
            $product->total_sum = NumberHelper::calculateTaxPriceItem(
                $product->product,
                $product->price,
                $this->quantity
            );
            if ($product->status == CartProduct::STATUS_AT_ORDER) {
                $product->status = CartProduct::STATUS_CHANGE_QUANTITY;
            }
            if ($orderProduct) {
                if ($orderProduct->quantity == $product->new_quantity) {
                    $product->status = CartProduct::STATUS_AT_ORDER;
                    $product->quantity = $product->new_quantity;
                    $product->new_quantity = 0;
                } else {
                    $product->status = CartProduct::STATUS_CHANGE_QUANTITY;
                }
            }
            $result = $product->save();
            if ($result) {
                $message = 'Змінена кількість товару ' . ArrayHelper::getValue($product->product, 'name', '')
                    . ' з ' . $product->quantity . ' до ' . $product->new_quantity . ' ' .
                    ArrayHelper::getValue($product->product, 'unit.name', 'шт.');
                $product->addHistory($message);
            }
            return new CartResponseDto(['result' => $result, 'total' => $cart->recalculateTotal()]);
        }
        return new CartResponseDto();
    }

    public function updateCart()
    {
        $cart = Cart::find()
            ->where(['id' => $this->id])
            ->one();
        if ($cart) {
            $products = CartProduct::find()
                ->where(['cart_id' => $cart->id])
                ->all();
            if ($products) {
                OrderProduct::deleteAll(
                    [
                        'and',
                        ['not in', 'product_uuid', ArrayHelper::getColumn($products, 'product_uuid')],
                        ['custom' => 0],
                        ['order_id' => $cart->order_id]
                    ]
                );
                $model = new AddProductForm(
                    [
                        'order_id' => $cart->order_id,
                        'products' => $products
                    ]
                );
                $result = $model->addProducts(false);
                if ($result) {
                    $orderProducts = OrderProduct::find()
                        ->where(['order_id' => $cart->order_id])
                        ->andWhere(['custom' => 0])->all();
                    if ($orderProducts) {
                        $cartProduct = [];
                        foreach ($orderProducts as $product) {
                            $cartProduct[] = [
                                'cart_id'      => $cart->id,
                                'product_id'   => $product->product_id,
                                'product_uuid' => $product->product_uuid,
                                'quantity'     => $product->quantity,
                                'new_quantity' => 0,
                                'price'        => $product->price,
                                'total_sum'    => $product->total_sum,
                                'status'       => CartProduct::STATUS_AT_ORDER,
                                'is_delete'    => $product->is_delete,
                            ];
                        }
                        CartProduct::deleteAll(['cart_id' => $cart->id]);
                        if (!empty($cartProduct)) {
                            try {
                                Yii::$app->db->createCommand()->batchInsert(
                                    CartProduct::tableName(),
                                    [
                                        'cart_id',
                                        'product_id',
                                        'product_uuid',
                                        'quantity',
                                        'new_quantity',
                                        'price',
                                        'total_sum',
                                        'status',
                                        'is_delete'
                                    ],
                                    $cartProduct
                                )->execute();
                            } catch (Exception $e) {
                                Yii::error($e);
                            }
                        }
                    }
                    $user = Yii::$app->user->getIdentity();
                    $model = new CartProductHistory(
                        [
                            'cart_id'  => $cart->id,
                            'user_id'  => $user->id,
                            'username' => $user->email,
                            'message'  => 'Кошик перенесений до замовлення'
                        ]
                    );
                    $model->save();
                }
                $cart->recalculateTotal();
                return $result;
            }
        }
        return false;
    }

    public function clearCart()
    {
        if ($this->id) {
            $cart = Cart::find()
                ->where(['id' => $this->id])
                ->one();
        } else {
            /** @var $user User */
            $user = Yii::$app->user->getIdentity();
            $cart = Cart::find()
                ->where(['order_id' => 0])
                ->andWhere(['company_id' => $user->company_id])
                ->andWhere(['user_id' => $user->id])
                ->one();
        }
        if ($cart) {
            $orderProducts = OrderProduct::find()
                ->where(['order_id' => $cart->order_id])
                ->andWhere(['custom' => 0])->all();
            CartProduct::deleteAll(['cart_id' => $cart->id]);
            $user = Yii::$app->user->getIdentity();
            $model = new CartProductHistory(
                [
                    'cart_id'  => $cart->id,
                    'user_id'  => $user->id,
                    'username' => $user->email,
                    'message'  => 'Зміни скинуто'
                ]
            );
            $model->save();
            if ($orderProducts) {
                $cartProduct = [];
                foreach ($orderProducts as $product) {
                    $cartProduct[] = [
                        'cart_id'      => $cart->id,
                        'product_id'   => $product->product_id,
                        'product_uuid' => $product->product_uuid,
                        'quantity'     => $product->quantity,
                        'new_quantity' => 0,
                        'price'        => $product->price,
                        'total_sum'    => $product->total_sum,
                        'status'       => CartProduct::STATUS_AT_ORDER,
                        'is_delete'    => $product->is_delete
                    ];
                }
                if (!empty($cartProduct)) {
                    try {
                        Yii::$app->db->createCommand()->batchInsert(
                            CartProduct::tableName(),
                            [
                                'cart_id',
                                'product_id',
                                'product_uuid',
                                'quantity',
                                'new_quantity',
                                'price',
                                'total_sum',
                                'status',
                                'is_delete'
                            ],
                            $cartProduct
                        )->execute();
                    } catch (Exception $e) {
                        Yii::error($e);
                    }
                }
            }
            $cart->recalculateTotal();
            return true;
        }
        return false;
    }

    public function cartIsChanged() {
        $cartIsChanged = false;

        $cart = Cart::find()
            ->where(['id' => $this->id])
            ->one();
        if ($cart) {
            $cartProducts = CartProduct::find()->where(['cart_id' => $cart->id])->all();

            if (!empty($cartProducts)) {
                foreach ($cartProducts as $cartProduct) {
                    $orderProduct = OrderProduct::find()->where([
                        "order_id" => $this->order_id,
                        'product_id' => $cartProduct->product_id,
                    ])->one();

                    if (!$orderProduct || $orderProduct->quantity != $cartProduct->quantity) {
                        $cartIsChanged = true;
                    }
                }
            }
        }

        return $cartIsChanged;
    }
}
