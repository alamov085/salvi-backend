<?php

namespace api\models\forms;

use common\models\Company;
use common\models\User;
use yii\base\InvalidArgumentException;
use yii\base\Model;

class VerifyEmailForm extends Model
{
    /**
     * @var string
     */
    public $token;

    /**
     * @var User
     */
    private $_user;


    /**
     * Creates a form model with given token.
     *
     * @param string $token
     * @param array $config name-value pairs that will be used to initialize the object properties
     * @throws InvalidArgumentException if token is empty or not valid
     */
    public function __construct($token, array $config = [])
    {
        if (empty($token) || !is_string($token)) {
            throw new InvalidArgumentException('Ссылка для подтверждения аккаунта не верная.');
        }
        $this->_user = User::findByVerificationToken($token);
        if (!$this->_user) {
            throw new InvalidArgumentException('Ссылка для подтверждения аккаунта более не активная.');
        }
        parent::__construct($config);
    }

    /**
     * Verify email
     *
     * @return User|null the saved model or null if saving fails
     */
    public function verifyEmail()
    {
        $user = $this->_user;
        $user->status = User::STATUS_ACTIVE;
        $user->generateEmailVerificationToken();
        $company = Company::find()->where(['id' => $user->company_id])->one();
        if ($company != false) {
            $company->activateCompany();
            $company->save();
        }
        return $user->save(false) ? $user : null;
    }
}
