<?php

namespace common\models;

use api\helpers\UserEmailHelper;
use Yii;
use yii\base\Exception;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\web\IdentityInterface;

/**
 * User model
 *
 * @property integer $id
 * @property string $username
 * @property string $password_hash
 * @property string $password_reset_token
 * @property string $verification_token
 * @property string $email
 * @property string $auth_key
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $authKey
 * @property string $password write-only password
 * @property integer $company_id
 * @property string $first_name
 * @property string $last_name
 * @property integer $phone_number
 * @property string $position
 * @property integer $currency_id
 * @property integer $location_id
 * @property integer $password_difficult
 * @property Currency|ActiveQuery $currency
 * @property mixed|int $mainCompanyId
 * @property ActiveQuery|CompanyEntity $companyEntity
 * @property AuthAssignment|ActiveQuery $role
 * @property array|mixed $companyList
 * @property string $reason
 * @property UserCostCenter|ActiveQuery $costCenters
 * @property ActiveQuery|UserLocation $locations
 * @property ActiveQuery|Company $company
 * @property integer $all_location;
 * @property integer $all_company;
 * @property array $managerCompanies
 * @property ActiveQuery|UserCompany $userCompany
 * @property ActiveQuery|UserLocation $userLocation
 * @property integer $manager;
 * @property ActiveQuery|Company $userCompanyList
 * @property History|ActiveQuery $history
 * @property array $allLocations
 * @property string $old_role;
 * @property array $allCost
 * @property int $all_cost;
 */
class User extends ActiveRecord implements IdentityInterface
{
    const STATUS_DELETED = 0;
    const STATUS_INACTIVE = 9;
    const STATUS_NON_APPROVED_EMAIL = 10;
    const STATUS_ACTIVE = 20;

    public static function statuses()
    {
        return [
            self::STATUS_ACTIVE => [
                'id' => self::STATUS_ACTIVE,
                'name' => 'Активний'
            ],
            self::STATUS_INACTIVE => [
                'id' => self::STATUS_INACTIVE,
                'name' => 'Не активний'
            ],
            self::STATUS_NON_APPROVED_EMAIL => [
                'id' => self::STATUS_NON_APPROVED_EMAIL,
                'name' => 'Пошта не підтверджена'
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user}}';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['status', 'default', 'value' => self::STATUS_INACTIVE],
            ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_INACTIVE,
                self::STATUS_DELETED, self::STATUS_NON_APPROVED_EMAIL]],
            [['company_id', 'phone_number', 'currency_id', 'password_difficult',], 'integer'],
            [['first_name', 'last_name', 'old_role'], 'string'],
            [['password_difficult', 'username', 'reason', 'company_id', 'position', 'all_location',
                'all_cost'], 'safe'],
            [['all_company', 'manager'], 'boolean'],
            ['company_id', 'required']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id)
    {
        return static::find()
            ->where(['id' => $id])
            ->andWhere(['in', 'status', [self::STATUS_ACTIVE, self::STATUS_NON_APPROVED_EMAIL]])
            ->one();
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return static::find()
            ->where(['username' => $username])
            ->andWhere(['in', 'status', [self::STATUS_ACTIVE, self::STATUS_NON_APPROVED_EMAIL]])
            ->one();
    }

    /**
     * Finds user by password reset token
     *
     * @param string $token password reset token
     * @return static|null
     */
    public static function findByPasswordResetToken($token)
    {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }
        return static::find()
            ->where(['password_reset_token' => $token])
            ->andWhere(['in', 'status', [self::STATUS_ACTIVE, self::STATUS_NON_APPROVED_EMAIL]])
            ->one();
    }

    /**
     * Finds user by verification email token
     *
     * @param string $token verify email token
     * @return static|null
     */
    public static function findByVerificationToken($token)
    {
        return static::findOne([
            'verification_token' => $token,
            'status' => self::STATUS_NON_APPROVED_EMAIL
        ]);
    }

    /**
     * Finds out if password reset token is valid
     *
     * @param string $token password reset token
     * @return bool
     */
    public static function isPasswordResetTokenValid($token)
    {
        if (empty($token)) {
            return false;
        }

        $timestamp = (int)substr($token, strrpos($token, '_') + 1);
        $expire = Yii::$app->params['user.passwordResetTokenExpire'];
        return $timestamp + $expire >= time();
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     * @throws Exception
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * Generates new password reset token
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    public function generateEmailVerificationToken()
    {
        $this->verification_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Removes password reset token
     */
    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }

    /**
     * @param mixed $token
     * @param null $type
     * @return User|IdentityInterface|null
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::find()
            ->joinWith(['company'])
            ->where([
                'and',
                ['user.auth_key' => $token],
                ['user.status' => self::STATUS_ACTIVE],
                ['user.manager' => 0],
                ['company.status' => Company::STATUS_ACTIVE]
            ])
            ->orWhere([
                'and',
                ['user.auth_key' => $token],
                ['user.status' => self::STATUS_ACTIVE],
                ['user.manager' => 1],
            ])
            ->one();
    }

    public function fields()
    {
        $fields = parent::fields();
        unset(
            $fields['password_hash'],
            $fields['auth_key'],
            $fields['password_reset_token'],
            $fields['created_at'],
            $fields['updated_at'],
            $fields['verification_token']
        );
        return $fields;
    }

    /**
     * Подтверждение пользователя администратором
     * @return bool
     * @throws Exception
     */
    public function approveUser()
    {
        $this->status = self::STATUS_NON_APPROVED_EMAIL;
        $password = Yii::$app->security->generateRandomString(8);
        $this->setPassword($password);
        if ($this->save()) {
            UserEmailHelper::registerUser($this, $password);
            return true;
        }
        return false;
    }

    /** Отклонить заявку на регистрацию пользователя
     * @param $reason
     * @return bool
     */
    public function refuseUser($reason)
    {
        $this->reason = $reason;
        $this->status = self::STATUS_DELETED;
        if ($this->save()) {
            UserEmailHelper::refuseUser($this, $reason);
            return true;
        }
        return false;
    }

    /**
     * @return ActiveQuery | Currency
     */
    public function getCurrency()
    {
        return $this->hasOne(Currency::class, ['id' => 'currency_id']);
    }

    /**
     * @return int|mixed
     */
    public function getMainCompanyId()
    {
        return $this->company_id;
    }

    /**
     * @return array|mixed
     */
    public function getCompanyList()
    {
        $model = CompanyEntity::find()->where(['company_id' => $this->mainCompanyId])->all();
        if ($model != false) {
            return ArrayHelper::getColumn($model, 'id');
        }
        return [];
    }

    /**
     * @return ActiveQuery | AuthAssignment
     */
    public function getRole()
    {
        return $this->hasOne(AuthAssignment::class, ['user_id' => 'id']);
    }

    public function extraFields()
    {
        return ['role', 'companyEntity'];
    }

    /**
     * @return ActiveQuery | CompanyEntity
     */
    public function getCompanyEntity()
    {
        return $this->hasOne(CompanyEntity::class, ['id' => 'company_id']);
    }

    /**
     * @return ActiveQuery | Company
     */
    public function getCompany()
    {
        return $this->hasOne(Company::class, ['id' => 'company_id']);
    }

    /**
     * @return ActiveQuery | UserLocation
     */
    public function getLocations()
    {
        return $this->hasMany(UserLocation::class, ['user_id' => 'id']);
    }

    /**
     * @return ActiveQuery | UserCostCenter
     */
    public function getCostCenters()
    {
        return $this->hasMany(UserCostCenter::class, ['user_id' => 'id']);
    }

    /**
     * @return ActiveQuery | UserCompany
     */
    public function getUserCompany()
    {
        return $this->hasMany(UserCompany::class, ['user_id' => 'id']);
    }
    /**
     * @return ActiveQuery | UserLocation
     */
    public function getUserLocation()
    {
        return $this->hasMany(UserLocation::class, ['location_id' => 'id']);
    }

    /**
     * @return ActiveQuery | Company
     */
    public function getUserCompanyList()
    {
        return $this->hasMany(Company::class, ['id' => 'company_id'])->via('userCompany');
    }

    /**
     * @return array
     */
    public function getManagerCompanies()
    {
        if ($this->all_company) {
            $model = Company::find()->all();
            return ArrayHelper::getColumn($model, 'id');
        } else {
            return ArrayHelper::getColumn($this->userCompany, 'company_id');
        }
    }

    /**
     * Список менеджером по компании
     * @param int $company_id
     * @param array $users
     * @return array
     */
    public static function getManagersByCompany(int $company_id, array $users = []): array
    {
        $query = self::find()
            ->joinWith(['userCompany'])
            ->andWhere(['manager' => 1]);
        if (!empty($users)) {
            $query->andWhere(['in', 'user.id', $users]);
        }
        $models = $query->all();
        if ($models) {
            $data = [];
            foreach ($models as $model) {
                if (in_array($company_id, $model->managerCompanies)) {
                    $data[] = $model->id;
                }
            }
            return $data;
        }
        return [];
    }

    public function afterSave($insert, $changedAttributes)
    {
        if ($insert) {
            History::saveHistory(History::USER, $this->id);
        }
        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * @return ActiveQuery | History
     */
    public function getHistory()
    {
        return $this->hasOne(History::class, ['entity_id' => 'id'])
            ->andOnCondition(['entity' => History::USER]);
    }

    /**
     * @return array
     */
    public function getAllLocations()
    {
        if ($this->all_location) {
            $companyLocation = Locations::find()
                ->where(['company_id' => $this->company_id])
                ->all();
            $locations = ArrayHelper::getColumn($companyLocation, 'id');
        } else {
            $locations = ArrayHelper::getColumn($this->locations, 'location_id');
        }
        return $locations;
    }

    /**
     * @return array
     */
    public function getAllCost()
    {
        if ($this->all_cost) {
            $costList = CostCenter::find()
                ->where(['in', 'location_id', $this->allLocations])
                ->asArray()
                ->all();
            $cost = ArrayHelper::getColumn($costList, 'id');
        } else {
            $cost = ArrayHelper::getColumn($this->costCenters, 'cost_center_id');
        }
        return $cost;
    }
}
