<?php

namespace common\models;

use api\helpers\NotificationHelper;
use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "coordination".
 *
 * @property int $id
 * @property int $company_id
 * @property string $name
 * @property string $sum
 * @property int $type
 * @property int $status
 * @property int $order
 * @property int $all_roles
 * @property int $created_at
 * @property ActiveQuery|CoordinationUsers $users
 * @property ActiveQuery|CoordinationRoles $roles
 * @property string $statusName
 * @property int $updated_at
 * @property History|ActiveQuery $history
 * @property int $additional_type
 * @property int $enable_notification
 */
class Coordination extends ActiveRecord
{
    const TYPE_ALL = 1;
    const TYPE_LESS_OR_EQUAL = 2;
    const TYPE_BIGGER = 3;

    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;

    const ADD_TYPE_NOTHING = 1;
    const ADD_TYPE_HIGHER_THAN_BUDGET = 2;
    const ADD_TYPE_WITHOUT_BUDGET = 3;
    const ADD_TYPE_CATALOG_PRODUCT = 4;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'coordination';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['company_id', 'type', 'status', 'order', 'created_at', 'updated_at', 'additional_type'], 'integer'],
            [['all_roles', 'enable_notification'], 'boolean'],
            [['sum'], 'number'],
            [['name'], 'string', 'max' => 255]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'         => 'ID',
            'company_id' => 'Company ID',
            'name'       => 'Name',
            'sum'        => 'Sum',
            'type'       => 'Type',
            'status'     => 'Status',
            'order'      => 'Order',
            'all_roles'  => 'All Roles',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return ActiveQuery | CoordinationRoles
     */
    public function getRoles()
    {
        return $this->hasMany(CoordinationRoles::class, ['coordination_id' => 'id']);
    }

    /**
     * @return ActiveQuery | CoordinationUsers
     */
    public function getUsers()
    {
        return $this->hasMany(CoordinationUsers::class, ['coordination_id' => 'id']);
    }

    public function save($runValidation = true, $attributeNames = null)
    {
        if ($this->isNewRecord) {
            $this->created_at = date('U');
        }
        $this->updated_at = date('U');
        return parent::save($runValidation, $attributeNames);
    }

    /**
     * @return mixed
     */
    public function getStatusName()
    {
        return ArrayHelper::getValue(self::statuses(), $this->status . '.name', '');
    }

    public static function statuses()
    {
        return [
            self::STATUS_INACTIVE => [
                'id'    => self::STATUS_INACTIVE,
                'name'  => 'Не активний',
                'class' => ''
            ],
            self::STATUS_ACTIVE   => [
                'id'    => self::STATUS_ACTIVE,
                'name'  => 'Активний',
                'class' => ''
            ],
        ];
    }

    /**
     * @param $status
     * @return bool
     */
    public function changeStatus($status)
    {
        $statuses = ArrayHelper::getColumn(self::statuses(), 'id');
        if (in_array($status, $statuses)) {
            $this->status = $status;
            return $this->save();
        }
        return false;
    }

    public static function checkCoordination($role)
    {
        /** @var  $user User */
        $user = Yii::$app->user->getIdentity();
        $coordination = self::find()
            ->joinWith(['roles', 'users'])
            ->where(['coordination.status' => self::STATUS_ACTIVE])
            ->andWhere(['coordination.company_id' => $user->company_id])
            ->andWhere(['coordination_roles.role' => $role])
            ->all();
        if ($coordination) {
            foreach ($coordination as $model) {
                $usersIsset = false;
                if ($model->all_roles) {
                    $users = AuthAssignment::find()
                        ->where(['in', 'item_name', ArrayHelper::getColumn($model->roles, 'role')])
                        ->all();
                    if ($users) {
                        $activeUsers = User::find()->where(['status' => User::STATUS_ACTIVE])
                            ->andWhere(['in', 'id', ArrayHelper::getColumn($users, 'user_id')])
                            ->andWhere(['manager' => 0])
                            ->andWhere(['company_id' => $user->company_id])
                            ->all();
                        if ($activeUsers) {
                            $usersIsset = true;
                        }
                    }
                } else {
                    $users = ArrayHelper::getColumn($model->users, 'user_id');
                    $activeUsers = User::find()->where(['status' => User::STATUS_ACTIVE])
                        ->andWhere(['in', 'id', $users])
                        ->andWhere(['manager' => 0])
                        ->all();
                    if ($activeUsers) {
                        $usersIsset = true;
                    }
                }
                if (!$usersIsset) {
                    NotificationHelper::coordinationNoActiveUserOrder($model);
                }
            }
        }
    }

    public function afterSave($insert, $changedAttributes)
    {
        if ($insert) {
            History::saveHistory(History::COORDINATION, $this->id);
        }
        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * @return ActiveQuery | History
     */
    public function getHistory()
    {
        return $this->hasOne(History::class, ['entity_id' => 'id'])
            ->andOnCondition(['entity' => History::COORDINATION]);
    }
}
