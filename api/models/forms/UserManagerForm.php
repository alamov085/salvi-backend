<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 03.07.2019
 * Time: 10:39
 */

namespace api\models\forms;

use api\helpers\UserEmailHelper;
use common\helpers\UserSecurity;
use common\models\User;
use common\models\UserCompany;
use common\models\UserCostCenter;
use common\models\UserLocation;
use Yii;
use yii\base\Model;

/**
 * Class UserManagerForm
 * @package api\models\forms
 */
class UserManagerForm extends Model
{
    public $id;
    public $email;
    public $phone_number;
    public $first_name;
    public $last_name;
    public $role_name;
    public $password;
    public $repeatPassword;
    public $all_company;
    public $companies;
    public $password_difficult;


    public function rules()
    {
        return [
            [['first_name', 'last_name', 'role_name'], 'required'],
            [['password', 'repeatPassword'], 'required', 'on' => 'create'],

            ['phone_number', 'match', 'pattern' => '/^\d{9}$/', 'message' => 'Поле має містити 9 цифр'],

            ['email', 'required', 'on' => 'create'],
            ['email', 'trim'],
            ['email', 'email'],
            ['email', 'string', 'max' => 255],
            ['email', 'unique', 'targetClass' => '\common\models\User', 'message' => 'Цей email вже зайнятий',
                'on' => 'create'],
            [['role_name', 'id', 'companies'], 'safe'],

            ['password', 'passwordValidate'],

            ['repeatPassword', 'compare', 'compareAttribute' => 'password',
                'message' => 'Пароли должны быть одинаковые'],
            [['all_company'], 'boolean']
        ];
    }

    /**
     * @return bool
     */
    public function passwordValidate()
    {
        $this->password_difficult = UserSecurity::getPassLevel($this->password);
        if ($this->password_difficult) {
            return true;
        } else {
            $this->addError(
                'password',
                'Пароль повинен містити як мінімум 8 символів, 1 велику літеру і 1 цифру'
            );
            return false;
        }
    }

    public function create()
    {
        $model = new User();
        $model->first_name = $this->first_name;
        $model->last_name = $this->last_name;
        $model->email = $this->email;
        $model->username = $this->email;
        $model->phone_number = $this->phone_number;
        $model->setPassword($this->password);
        $model->status = User::STATUS_NON_APPROVED_EMAIL;
        $model->all_company = ($this->all_company) ? $this->all_company : 0;
        $model->manager = true;
        $model->old_role = $this->role_name;
        $model->generateAuthKey();
        $model->company_id = 0;
        if ($model->save()) {
            if (!$this->all_company) {
                UserCompany::addCompanies($model->id, $this->companies);
            }
            if (!empty($this->role_name)) {
                $manager = Yii::$app->authManager;
                $role_name = $manager->getRole($this->role_name);
                $manager->assign($role_name, $model->id);
            }
            UserEmailHelper::createUser($model, $this->password);
            return true;
        }
        return false;
    }

    public function update()
    {
        $model = User::find()->where(['id' => $this->id])->one();
        if ($model != false) {
            $model->first_name = $this->first_name;
            $model->last_name = $this->last_name;
            $model->email = $this->email;
            $model->username = $this->email;
            $model->phone_number = $this->phone_number;
            $model->company_id = 0;
            $model->old_role = $this->role_name;
            if (!empty($this->password)) {
                $model->setPassword($this->password);
                $model->generateAuthKey();
            }
            $model->all_company = $this->all_company;
            if ($model->save()) {
                UserCompany::deleteAll(['user_id' => $model->id]);
                if (!$this->all_company) {
                    UserCompany::addCompanies($model->id, $this->companies);
                }
                if (!empty($this->role_name)) {
                    $manager = Yii::$app->authManager;
                    $manager->revokeAll($model->id);
                    $role = $manager->getRole($this->role_name);
                    $manager->assign($role, $model->id);
                }
                return true;
            }
        }
        return false;
    }

    public function attributeLabels()
    {
        return [
            'first_name' => "Ім'я",
            'last_name' => "Прізвище",
            'password' => "Пароль",
            'repeatPassword' => "Підтвердження пароля",
            'email' => "Email",
            'phone_number' => "Телефон",
            'role_name' => 'Роль'
        ];
    }
}
