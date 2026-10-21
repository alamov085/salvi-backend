<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 19.03.2020
 * Time: 17:25
 */

namespace api\models\forms;

use api\helpers\NumberHelper;
use common\models\Cart;
use common\models\CartProduct;
use common\models\OrderProduct;
use common\models\Product;
use common\models\User;
use yii\base\Model;
use yii\helpers\ArrayHelper;

/**
 * Class NewCartToOldForm
 * @package api\models\forms
 */
class NewCartToOldForm extends Model
{
    public $old_id;

    public function rules()
    {
        return [
            ['old_id', 'integer'],
            ['old_id', 'required']
        ];
    }

    public function save()
    {
        /** @var User $user */
        $user = \Yii::$app->user->getIdentity();
        $cart = Cart::find()
            ->where(['order_id' => 0])
            ->andWhere(['company_id' => $user->company_id])
            ->andWhere(['user_id' => $user->id])
            ->one();
        $oldCart = Cart::find()
            ->where(['id' => $this->old_id])
            ->andWhere(['company_id' => $user->company_id])
            ->one();
        if ($cart && $oldCart) {
            $newProduct = CartProduct::find()
                ->where(['cart_id' => $cart->id])
                ->all();
            if ($newProduct) {
                $oldProduct = CartProduct::find()
                    ->where(['cart_id' => $oldCart->id])
                    ->andWhere(['in', 'product_uuid', ArrayHelper::getColumn($newProduct, 'product_uuid')])
                    ->all();
                if ($oldProduct) {
                    $newProduct = ArrayHelper::index($newProduct, 'product_uuid');
                    foreach ($oldProduct as $product) {
                        $orderProduct = OrderProduct::find()
                            ->where(['order_id' => $oldCart->order_id])
                            ->andWhere(['product_uuid' => $product->product_uuid])
                            ->one();
                        $quantity = ArrayHelper::getValue($newProduct, $product->product_uuid . '.new_quantity', 0);
                        if (!$quantity) {
                            $quantity = ArrayHelper::getValue($newProduct, $product->product_uuid . '.quantity', 0);
                        }
                        if ($product->new_quantity) {
                            $product->new_quantity += $quantity;
                        } else {
                            $product->new_quantity = $product->quantity + $quantity;
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
                        $product->total_sum = NumberHelper::calculateTaxPriceItem(
                            $product->product,
                            $product->price,
                            ($product->new_quantity) ?: $product->quantity
                        );
                        if ($product->save()) {
                            $message = 'Змінена кількість товару ' . ArrayHelper::getValue($product->product, 'name', '')
                                . ' з ' . $product->quantity . ' до ' . $product->new_quantity . ' ' .
                                ArrayHelper::getValue($product->product, 'unit.name', 'шт.');
                            $product->addHistory($message);
                            unset($newProduct[$product->product_uuid]);
                        }
                    }
                }
                foreach ($newProduct as $item) {
                    $product = Product::find()
                        ->where(['id' => $item->product_id])
                        ->one();
                    $model = new CartProduct([
                        'cart_id' => $oldCart->id,
                        'product_uuid' => $item->product_uuid,
                        'product_id' => $item->product_id,
                        'status' => CartProduct::STATUS_NEW,
                        'quantity' => $item->quantity,
                        'new_quantity' => 0,
                        'price' => $item->price,
                        'total_sum' => NumberHelper::calculateTaxPriceItem($product, $item->price, $item->quantity)
                    ]);
                    $model->save();
                }
                $oldCart->recalculateTotal();
                CartProduct::deleteAll(['cart_id' => $cart->id]);
                Cart::deleteAll(['id' => $cart->id]);
                return true;
            }
            return false;
        }
        return false;
    }
}
