<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 17.06.2019
 * Time: 13:11
 */

namespace api\models\forms;

use api\helpers\NotificationHelper;
use api\helpers\UserEmailHelper;
use common\components\RbacManager;
use common\helpers\UserSecurity;
use common\models\CostCenter;
use common\models\User;
use common\models\UserCostCenter;
use common\models\UserLocation;
use Yii;
use yii\base\Exception;
use yii\base\Model;

/**
 * Class CreateUserForm
 * @package api\models\forms
 */
class CreateUserForm extends Model
{
    public $email;
    public $phone_number;
    public $first_name;
    public $last_name;
    public $position;
    public $role_name;
    public $password;
    public $repeatPassword;
    public $all_location;
    public $all_cost;
    public $locations;
    public $cost_centers;
    public $isActive;
    public $activeTill;
    public $companyEntity;
    public $id;

    private $_passwordDifficult;

    public function rules()
    {
        return [
            [['first_name', 'last_name', 'role_name'], 'required'],
            [['password', 'repeatPassword', 'phone_number'], 'required', 'on' => 'create'],

            ['phone_number', 'match', 'pattern' => '/^\d{9}$/', 'message' => 'Поле має містити 9 цифр'],

            ['email', 'required'],
            ['email', 'trim'],
            ['email', 'email'],
            ['email', 'string', 'max' => 255],
            [
                'email',
                'unique',
                'targetClass' => \common\models\User::class,
                'targetAttribute' => 'email',
                'filter' => function ($query) {
                    if ($this->id) {
                        $query->andWhere(['<>', 'id', $this->id]);
                    }
                },
                'message' => 'Цей email вже зайнятий',
            ],
            [['position', 'role_name', 'all_location', 'all_cost', 'locations', 'cost_centers', 'isActive',
                'activeTill', 'id', 'password', 'repeatPassword'], 'safe'],

            ['password', 'passwordValidate'],
            [['password', 'repeatPassword', 'phone_number'], 'safe'],
            ['password', 'compare', 'compareAttribute' => 'repeatPassword',
                'message' => 'Паролі повинні бути однакові'],
            ['phone_number', 'validateUniq']
        ];
    }


    /**
     * @return bool
     */
    public function passwordValidate()
    {
        $this->_passwordDifficult = UserSecurity::getPassLevel($this->password);
        if ($this->_passwordDifficult) {
            return true;
        } else {
            $this->addError(
                'password',
                'Пароль повинен бути написаний латиницею, містити мінімум 8 символів,
                 з яких обов\'язково 1 заголовну букву і 1 цифру'
            );
            return false;
        }
    }

    /**
     * @return bool
     * @throws Exception
     * @throws \Throwable
     */
    public function createUser()
    {
        $model = new User();
        $model->first_name = $this->first_name;
        $model->last_name = $this->last_name;
        $model->email = $this->email;
        $model->username = $this->email;
        $model->phone_number = $this->phone_number;
        $model->all_location = ($this->all_location) ? 1 : 0;
        $model->all_cost = ($this->all_cost) ? 1 : 0;
        $model->position = $this->position;
        $model->old_role = $this->role_name;
        $model->password_difficult = $this->_passwordDifficult;
        $model->setPassword($this->password);
        $model->generateAuthKey();
        if (Yii::$app->user->getIdentity()->company_id) {
            $model->company_id = Yii::$app->user->getIdentity()->company_id;
        }
        $model->status = User::STATUS_NON_APPROVED_EMAIL;
        if ($model->save()) {
            if (!empty($this->role_name)) {
                $manager = Yii::$app->authManager;
                $role = $manager->getRole($this->role_name);
                $manager->assign($role, $model->id);
            }
            if (!$this->all_cost && !empty($this->cost_centers)) {
                foreach ($this->cost_centers as $cost) {
                    $modelCost = new UserCostCenter();
                    $modelCost->user_id = $model->id;
                    $modelCost->cost_center_id = $cost;
                    $modelCost->save();
                }
            }
            if (!empty($this->locations)) {
                foreach ($this->locations as $location) {
                    $modelLocation = new UserLocation();
                    $modelLocation->user_id = $model->id;
                    $modelLocation->location_id = $location;
                    $modelLocation->save();
                }
            }
            NotificationHelper::createUserManager($model);
            UserEmailHelper::createUser($model, $this->password);
            return true;
        }
        return false;
    }

    public function updateUser()
    {
        $model = User::find()->where(['id' => $this->id])->one();
        if ($model != false) {
            $notify = false;
            if ($model->old_role != $this->role_name) {
                $notify = true;
            }

            $emailExists = User::find()
                ->where(['email' => $this->email])
                ->andWhere(['<>','id', $model->id])
                ->exists();

            if(!$emailExists ) {
                $model->email = $this->email;
                $model->username = $this->email;
            } else {
                return false;
            }

            $model->first_name = $this->first_name;
            $model->last_name = $this->last_name;
            $model->phone_number = $this->phone_number;
            $model->all_location = ($this->all_location) ? 1 : 0;
            $model->all_cost = ($this->all_cost) ? 1 : 0;
            $model->position = $this->position;
            $model->old_role = $this->role_name;
            if (!is_null($this->password)) {
                $model->setPassword($this->password);
                $model->generateAuthKey();
                $model->password_difficult = $this->_passwordDifficult;
            }
            if (Yii::$app->user->getIdentity()->company_id) {
                $model->company_id = Yii::$app->user->getIdentity()->company_id;
            }
            if ($model->save()) {
                if ($notify) {
                    NotificationHelper::changeUserManager($model);
                }
                if (!empty($this->role_name)) {
                    $manager = Yii::$app->authManager;
                    $manager->revokeAll($model->id);
                    $role = $manager->getRole($this->role_name);
                    $manager->assign($role, $model->id);
                }
                UserCostCenter::deleteAll(['user_id' => $model->id]);
                UserLocation::deleteAll(['user_id' => $model->id]);
                if ($this->all_location) {
                    if (!$this->all_cost && !empty($this->cost_centers)) {
                        foreach ($this->cost_centers as $cost) {
                            $modelCost = new UserCostCenter();
                            $modelCost->user_id = $model->id;
                            $modelCost->cost_center_id = $cost;
                            $modelCost->save();
                        }
                    }
                } else {
                    if (!$this->all_cost && !empty($this->cost_centers)) {
                        $costs = CostCenter::find()
                            ->where(['in', 'id', $this->cost_centers])
                            ->andWhere((['in', 'location_id', $this->locations]))
                            ->all();
                        if ($costs) {
                            foreach ($costs as $cost) {
                                $modelCost = new UserCostCenter();
                                $modelCost->user_id = $model->id;
                                $modelCost->cost_center_id = $cost->id;
                                $modelCost->save();
                            }
                        }
                    }
                }
                if (!empty($this->locations)) {
                    foreach ($this->locations as $location) {
                        $modelLocation = new UserLocation();
                        $modelLocation->user_id = $model->id;
                        $modelLocation->location_id = $location;
                        $modelLocation->save();
                    }
                }
                return true;
            }
        }
        return false;
    }

    public function attributeLabels()
    {
        return [
            'email' => 'Email',
            'phone_number' => 'Телефон',
            'first_name' => 'Ім\'я',
            'last_name' => 'Прізвище',
            'position' => 'Посада',
            'role_name' => 'Роль',
            'password' => 'Пароль',
            'repeatPassword' => 'Підтвердження пароля',
            'cost_centers' => 'Кост-центр',
            'locations' => 'Локації'
        ];
    }

    public function validateUniq()
    {
        $query = User::find()
            ->where(['phone_number' => $this->phone_number]);

        if (!is_null($this->id)) {
            $query->andWhere(['!=', 'id', $this->id]);
        }

        $user = $query->all();
        if ($user) {
            $this->addError('phone_number', 'Цей телефон вже зайнятий');
        }
    }
}
