<?php

namespace common\models;

use api\helpers\NotificationHelper;
use Throwable;
use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "order_coordination".
 *
 * @property int $id
 * @property int $coordination_id
 * @property int $order_id
 * @property int $company_id
 * @property string $name
 * @property string $sum
 * @property int $type
 * @property int $status
 * @property int $order
 * @property int $all_roles
 * @property int $created_at
 * @property int $updated_at
 * @property ActiveQuery|OrderCoordinationRoles | array $roles
 * @property bool $canApprove
 * @property ActiveQuery|OrderCoordinationUsers | array $users
 * @property int $next_step
 * @property int $user_id
 * @property int $additional_type
 * @property int $enable_notification
 * @property int $status_cancel
 * @property ActiveQuery|User $user

 */
class OrderCoordination extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'order_coordination';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['coordination_id', 'order_id', 'company_id', 'type', 'status',
                'order', 'all_roles', 'created_at', 'updated_at', 'next_step',
                'user_id', 'additional_type'], 'integer'],
            [['sum'], 'number'],
            [['name'], 'string', 'max' => 255],
            ['enable_notification', 'integer']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'              => 'ID',
            'coordination_id' => 'Coordination ID',
            'order_id'        => 'Order ID',
            'company_id'      => 'Company ID',
            'name'            => 'Name',
            'sum'             => 'Sum',
            'type'            => 'Type',
            'status'          => 'Status',
            'order'           => 'Order',
            'all_roles'       => 'All Roles',
            'created_at'      => 'Created At',
            'updated_at'      => 'Updated At',
        ];
    }

    /**
     * @return ActiveQuery | OrderCoordinationRoles | array
     */
    public function getRoles()
    {
        return $this->hasMany(OrderCoordinationRoles::class, ['coordination_id' => 'coordination_id'])
            ->andOnCondition(['order_id' => $this->order_id]);
    }

    /**
     * @return ActiveQuery | OrderCoordinationUsers | array
     */
    public function getUsers()
    {
        return $this->hasMany(OrderCoordinationUsers::class, ['coordination_id' => 'coordination_id'])
            ->andOnCondition(['order_id' => $this->order_id]);
    }

    /**
     * Проверяем может ли пользователь делать подтверждение
     * @return bool
     * @throws Throwable
     */
    public function getCanApprove()
    {
        /** @var  $user User */
        $user = Yii::$app->user->getIdentity();
        if (Yii::$app->user->can('coordinationApproveOrder')) {
            return true;
        } else {
            if ($this->all_roles) {
                $userRole = Yii::$app->authManager->getRolesByUser($user->id);
                $oneRole = '';
                foreach ($userRole as $k => $value) {
                    $oneRole = $k;
                    break;
                }
                $roles = ArrayHelper::getColumn($this->roles, 'role');
                if (in_array($oneRole, $roles)) {
                    return true;
                }
            } else {
                $users = ArrayHelper::getColumn($this->users, 'user_id');
                if (in_array($user->id, $users)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * @param OrderCoordination $model
     * @param int $userId
     */
    public static function setAsApprove(OrderCoordination $model, int $userId = 0)
    {
        $model->status = 1;
        $model->next_step = 0;
        $model->user_id = $userId;
        $model->updated_at = time();
        $model->save();
    }

    /**
     * True - есть еще шаги, false - это был последний шаг
     * @param $userId
     * @return bool
     */
    public function approveStatus($userId)
    {
        $order = Order::find()->where(['id' => $this->order_id])->one();
	    $user = Yii::$app->user->getIdentity();
        if($userId == 0)
        {
	        $userId = $user->id;
        }
        $this->setAsApprove($this, $userId);
        $product = OrderProduct::find()
            ->where(['order_id' => $order->id])
            ->andWhere(['catalog' => 1])
            ->all();
        $coordination = self::find()
            ->where(['order_id' => $this->order_id])
            ->andWhere(['status' => 0])
            ->orderBy('order ASC')
            ->all();
        if ($coordination) {
            $i = 0;
            foreach ($coordination as $model) {
                /** @var  $model OrderCoordination */
                //Если нет ни одного активного пользователя нужно пропустить шаг
                $usersIsset = false;
                $activeUsers = [];
                if ($model->all_roles) {
                    $users = AuthAssignment::find()
                        ->where(['in', 'item_name', ArrayHelper::getColumn($model->roles, 'role')])
                        ->all();
                    if ($users) {
                        $activeUsers = User::find()
                            ->leftJoin('user_location ul','user.id = ul.user_id')
                            ->where(['user.status' => User::STATUS_ACTIVE])
                            ->andWhere(['in', 'user.id', ArrayHelper::getColumn($users, 'user_id')])
                            ->andWhere(['user.company_id' => $model->company_id])
                            ->andWhere(['ul.location_id' => $order->location_id])
                            ->all();
                        if ($activeUsers) {
                            $usersIsset = true;
                        }
                    }
                } else {
                    $users = ArrayHelper::getColumn($model->users, 'user_id');

                    $activeUsers = User::find()
                            ->leftJoin('user_location ul','user.id = ul.user_id')
                            ->where(['user.status' => User::STATUS_ACTIVE])
                            ->andWhere(['in', 'user.id', $users])
                            ->andWhere(['ul.location_id' => $order->location_id])
                            ->all();

                    if ($activeUsers) {
                        $usersIsset = true;
                    }
                }
                $i++;
	
	           
                
                if (($model->additional_type == Coordination::ADD_TYPE_WITHOUT_BUDGET && !$order->non_budget_order) ||
                    ($model->additional_type == Coordination::ADD_TYPE_WITHOUT_BUDGET && $order->non_budget_order &&
                        $order->budget)) {
                    self::setAsApprove($model,$userId);
                } elseif (($model->additional_type == Coordination::ADD_TYPE_HIGHER_THAN_BUDGET && !$order->budget) ||
                    ($model->additional_type == Coordination::ADD_TYPE_HIGHER_THAN_BUDGET && $order->budget &&
                        $order->budget->availableSum >= 0)) {
                    self::setAsApprove($model,$userId);
                } elseif ($model->additional_type == Coordination::ADD_TYPE_CATALOG_PRODUCT && !$product) {
                    self::setAsApprove($model,$userId);
                } elseif ($model->type == Coordination::TYPE_BIGGER && $order->total_sum < $model->sum) {
                    self::setAsApprove($model,$userId);
                } elseif (!$usersIsset) {
                    self::setAsApprove($model,$userId);
                } else {
                    $model->next_step = 1;
	                $model->user_id = $userId;
                    $model->save();
                    if ($model->enable_notification && $activeUsers) {
                        NotificationHelper::coordinationOrderWithoutRbac($model->order_id, $activeUsers);
                    }
                    NotificationHelper::coordinationOrder($model, $activeUsers);
                    break;
                }
            }
            if (isset($model) && ArrayHelper::getValue($model, 'next_step', 0)) {
                return true;
            }
        }
        return false;
    }


    /**
     * @return ActiveQuery | User
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }
}
