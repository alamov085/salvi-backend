<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 13.05.2020
 * Time: 14:10
 */

namespace api\models\forms;

use common\models\Order;
use common\models\User;
use yii\base\Model;

/**
 * Class PurchaseOrderForm
 * @package api\models\forms
 */
class PurchaseOrderForm extends Model
{
    public $id;
    public $ro_number;

    public function rules()
    {
        return [
            ['id', 'required'],
            ['ro_number', 'string']
        ];
    }

    public function update()
    {
        /** @var User $user */
        $user = \Yii::$app->user->getIdentity();
        $model = Order::find()
            ->where(['id' => $this->id])
            ->andWhere(['company_id' => $user->company_id])
            ->andWhere(['in', 'status', [Order::STATUS_OPEN, Order::STATUS_ON_APPROVE, Order::STATUS_MOD_APPROVE, Order::STATUS_MODERATION, Order::STATUS_AT_MANAGER]])
            ->one();
        if ($model) {
            $model->ro_number = $this->ro_number;
            return $model->save();
        }
        return false;
    }
}
