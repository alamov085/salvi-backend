<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 22.05.2019
 * Time: 10:45
 */

namespace api\models\forms;

use api\components\RecaptchaV3Validator;
use common\models\Company;
use common\models\User;
use Yii;
use yii\base\Model;

/**
 * Class LoginForm
 * @package api\models\forms
 *
 * @property null|User $user
 */
class LoginForm extends Model
{
    public $username;
    public $password;
    public $recaptchaToken;
    public $rememberMe = true;

    private $_user = false;


    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['username', 'password'], 'required'],
            ['username', 'email'],
            ['rememberMe', 'boolean'],
            ['password', 'validatePassword'],
            [['recaptchaToken'], RecaptchaV3Validator::class, 'acceptance_score' => null],
        ];
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validatePassword($attribute, $params)
    {
        $user = $this->getUser();
        if (!$this->hasErrors()) {
            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, 'Невірний email або пароль.');
            }
            if ($user && $user->manager === 0 && $user->company && $user->company->status !== Company::STATUS_ACTIVE) {
                $this->addError($attribute, 'Ваше підприємство неактивне.');
            }
            if ($user && $user->status == User::STATUS_NON_APPROVED_EMAIL) {
                $this->addError($attribute, 'Ваш аккаунт неактивний.');
            }
        }
    }

    /**
     * Logs in a user using the provided username and password.
     * @return boolean whether the user is logged in successfully
     */
    public function login()
    {
        if ($this->validate()) {
            return Yii::$app->user->login($this->getUser(), $this->rememberMe ? 3600 * 24 * 30 : 0);
        } else {
            return false;
        }
    }

    /**
     * Finds user by [[username]]
     *
     * @return User|null
     */
    public function getUser()
    {
        if ($this->_user === false) {
            $this->_user = User::findByUsername($this->username);
        }

        return $this->_user;
    }

    /**
     * @param $token
     * @return User|null
     */
    public static function findIdentityByAccessToken($token)
    {
        return User::findOne(['auth_key' => $token]);
    }
}
