<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 17.07.2019
 * Time: 13:38
 */

namespace api\models\forms;

use api\helpers\NotificationHelper;
use common\models\Order;
use common\models\OrderComment;
use common\models\User;
use Throwable;
use Yii;
use yii\base\Model;

/**
 * Class CommentForm
 * @package api\models\forms
 */
class CommentForm extends Model
{
    public $order_id;
    public $text;

    public function rules()
    {
        return [
            [['text', 'order_id'], 'required'],
            ['text', 'string'],
            ['order_id', 'safe']
        ];
    }

    /**
     * @return bool
     * @throws Throwable
     */
    public function create()
    {
        /** @var  $user User */
        $user = Yii::$app->user->getIdentity();
        $model = new OrderComment();
        $model->order_id = $this->order_id;
        $model->text = $this->text;
        $model->user_id = $user->id;
        $result = $model->save();
        if ($result) {
            $order = Order::findOne(['id' => $model->order_id]);
            NotificationHelper::commentToOrder($order, $user);
            NotificationHelper::commentToOrderOwner($order, $user);
        }
        return $result;
    }
}
