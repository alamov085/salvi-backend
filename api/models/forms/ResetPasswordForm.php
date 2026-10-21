<?php

namespace api\models\forms;

use common\helpers\UserSecurity;
use yii\base\Exception;
use yii\base\InvalidArgumentException;
use yii\base\Model;
use common\models\User;

/**
 * Password reset form
 */
class ResetPasswordForm extends Model
{
    public $password;
    public $repeatPassword;

    /**
     * @var User
     */
    private $_user;
    private $_passwordDifficult;


    /**
     * Creates a form model given a token.
     *
     * @param string $token
     * @param array $config name-value pairs that will be used to initialize the object properties
     * @throws InvalidArgumentException if token is empty or not valid
     */
    public function __construct($token, $config = [])
    {
        if (empty($token) || !is_string($token)) {
            throw new InvalidArgumentException('Посилання для відновлення паролю не вірне.');
        }
        $this->_user = User::findByPasswordResetToken($token);
        if (!$this->_user) {
            throw new InvalidArgumentException('Посилання для відновлення пароля більше не активне.');
        }
        parent::__construct($config);
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['repeatPassword', 'required'],
            ['password', 'string', 'min' => 8],
            ['password', 'passwordValidate'],

            ['repeatPassword', 'compare', 'compareAttribute' => 'password',
                'message' => 'Паролі повинні бути однакові'],
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
                'repeatPassword',
                'Пароль повинен містити як мінімум 8 символів, 1 велику літеру і 1 цифру'
            );
            return false;
        }
    }

    /**
     * Resets password.
     *
     * @return bool if password was reset.
     * @throws Exception
     */
    public function resetPassword()
    {
        $user = $this->_user;
        $user->setPassword($this->password);
        $user->removePasswordResetToken();
        $result = $user->save(false);
        if ($result) {
            return $user;
        }
        return false;
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'password' => 'Пароль',
            'repeatPassword' => 'Повторіть пароль'
        ];
    }
}
