<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 13.08.2019
 * Time: 10:55
 */

namespace api\models\forms;

use api\helpers\NotificationHelper;
use api\helpers\NumberHelper;
use api\helpers\OrderHelper;
use common\models\Order;
use common\models\OrderProduct;
use common\models\User;
use yii\base\Model;

/**
 * Class CustomProductForm
 * @package api\models\forms
 */
class CustomProductForm extends Model
{
    public $id;
    public $order_id;
    public $name;
    public $price;
    public $comment;
    public $quantity;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['order_id', 'price', 'name', 'quantity'], 'required'],
            [['name', 'comment'], 'string'],
            [['quantity', 'order_id'], 'integer', 'min' => 0],
            ['price', 'double', 'min' => '0.01'],
            ['id', 'safe']
        ];
    }

    /**
     * @return bool
     * @throws \Throwable
     */
    public function addProduct()
    {
        $model = new OrderProduct([
            'order_id' => $this->order_id,
            'price' => $this->price,
            'custom' => 1,
            'name' => $this->name,
            'comment' => $this->comment,
            'quantity' => $this->quantity,
            'total_sum' => $this->quantity * $this->price
        ]);
        $model->total_sum = NumberHelper::calculateTaxPrice($model);
        $result = $model->save();
        if ($result) {
            $order = Order::findOne(['id' => $this->order_id]);
            /** @var  $user User */
            $user = \Yii::$app->user->getIdentity();
            NotificationHelper::addProductsToOrder($order, $user, [$this->name]);
            NotificationHelper::addProductsToOrderOwner($order, $user, [$this->name]);
            if ($order) {
                $order->total_sum = OrderHelper::getTotalSum($this->order_id);
                $order->save();
            }
        }
        return $result;
    }

    public function editProduct()
    {
        $model = OrderProduct::find()->where(['id' => $this->id])->one();
        if ($model) {
            $model->price = $this->price;
            $model->name = $this->name;
            $model->comment = $this->comment;
            $model->quantity = $this->quantity;
            $model->total_sum = NumberHelper::calculateTaxPrice($model);
            $result = $model->save();
            if ($result) {
                $order = Order::findOne(['id' => $this->order_id]);
                /** @var  $user User */
                $user = \Yii::$app->user->getIdentity();
                NotificationHelper::addProductsToOrder($order, $user, [$this->name]);
                NotificationHelper::addProductsToOrderOwner($order, $user, [$this->name]);
                if ($order) {
                    $order->total_sum = OrderHelper::getTotalSum($this->order_id);
                    $order->save();
                }
            }
            return $result;
        }
        return false;
    }

    public function attributeLabels()
    {
        return [
            'name' => 'Назва',
            'quantity' => 'Кількість',
            'price' => 'Ціна',
            'comment' => 'Примітка',
        ];
    }
}
