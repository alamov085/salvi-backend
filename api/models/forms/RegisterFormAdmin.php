<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 23.05.2019
 * Time: 16:15
 */

namespace api\models\forms;

use api\components\RecaptchaV3Validator;
use api\helpers\NotificationHelper;
use api\helpers\UserEmailHelper;
use common\models\Company;
use common\models\CompanyEntity;
use common\models\User;
use Yii;
use yii\base\Exception;
use yii\base\Model;

/**
 * Class RegisterForm
 * @package api\models\forms
 */
class RegisterFormAdmin extends Model
{
    public $type;
    public $email;
    public $shortName;
    public $fullName;
    public $address;
    public $edrpou;
    public $inn;
    public $phone;
    public $firstName;
    public $lastName;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['type', 'phone', 'inn'], 'required'],

            ['phone', 'match', 'pattern' => '/^\d{9}$/', 'message' => 'Поле має містити 9 цифр'],

            ['email', 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            ['email', 'string', 'max' => 255],
            ['email', 'unique', 'targetClass' => '\common\models\User', 'message' => 'Цей email вже зайнятий'],
//            ['edrpou', 'unique', 'targetClass' => '\common\models\Company',
//                'message' => 'Цей код ЄДРПОУ вже є в базі', 'on' => 'ur'],
            ['phone', 'unique', 'targetClass' => '\common\models\User',
                'targetAttribute' => 'phone_number', 'message' => 'Цей телефон вже зайнятий'],

            ['type', 'in', 'range' => array_keys(Company::getTypes())],
            [['firstName', 'lastName'], 'required', 'on' => 'fop'],
            [['shortName', 'fullName', 'address', 'edrpou'], 'required', 'on' => 'ur'],
            [['edrpou', 'inn'], 'integer'],
            //['inn', 'match', 'pattern' => '/^\d{10}$/', 'message' => 'Поле має містити 10 цифр'],
        ];
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function register()
    {
        $transaction = Yii::$app->db->beginTransaction();
        switch ($this->type) {
            case 1:
                $result = $this->registerFop();
                break;
            case 2:
                $result = $this->registerUr();
                break;
            default:
                $result = false;
                break;
        }
        if ($result != false) {
            $company = $this->registerCompanyEntity($result);
            if ($company == false) {
                $transaction->rollBack();
                return false;
            }
            $user = new User();
            $user->username = $this->email;
            $user->email = $this->email;
            $user->company_id = $result->id;
            $user->phone_number = $this->phone;
            $user->setPassword($password = Yii::$app->security->generateRandomString(8));
            $user->generateAuthKey();
            $user->generateEmailVerificationToken();
            $user->status = User::STATUS_NON_APPROVED_EMAIL;
            $user->password_difficult = 1;
            if ($this->type == 1) {
                $user->first_name = $this->firstName;
                $user->last_name = $this->lastName;
            }
            if ($user->save()) {
                $manager = Yii::$app->authManager;
                $role = $manager->getRole('mainUser');
                $manager->assign($role, $user->id);
                $transaction->commit();
                NotificationHelper::registerCompany($result, $user);
                UserEmailHelper::registerUser($user, $password);
                return true;
            } else {
                $transaction->rollBack();
                return false;
            }
        } else {
            $transaction->rollBack();
            return false;
        }
    }

    /**
     * @return bool|Company
     */
    public function registerFop()
    {
        $model = new Company();
        $model->setAttributes([
            'type' => $this->type,
            'short_name' => $this->lastName . ' ' . $this->firstName,
            'full_name' => $this->lastName . ' ' . $this->firstName,
            'address' => '',
            'edrpou' => '',
            'inn' => $this->inn,
            'status' => Company::STATUS_ACTIVE,
            'catalog_limitation' => 1
        ]);
        return ($model->save()) ? $model : false;
    }

    /**
     * @return bool|Company
     */
    public function registerUr()
    {
        $model = new Company();
        $model->setAttributes([
            'type' => $this->type,
            'short_name' => $this->shortName,
            'full_name' => $this->fullName,
            'address' => $this->address,
            'edrpou' => $this->edrpou,
            'inn' => $this->inn,
            'status' => Company::STATUS_ACTIVE,
            'catalog_limitation' => 1
        ]);
        return ($model->save()) ? $model : false;
    }

    /**
     * @param Company $company
     * @return bool|CompanyEntity
     */
    public function registerCompanyEntity(Company $company)
    {
        $model = new CompanyEntity();
        $model->setAttributes([
            'company_id' => $company->id,
            'short_name' => $company->short_name,
            'full_name' => $company->full_name,
            'address' => $company->address,
            'bank_number' => '',
            'bank_name' => '',
            'mfo' => '',
            'edrpou' => $company->edrpou,
            'inn' => $company->inn,
            'tax_payer' => 0,
            'status' => CompanyEntity::STATUS_ACTIVE,
            'from_date' => null,
            'is_main' => 1
        ]);
        return ($model->save()) ? $model : false;
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'firstName' => 'Ім\'я',
            'lastName' => 'Прізвище',
            'email' => 'Email',
            'inn' => 'ІПН',
            'phone' => 'Мобільний телефон',
            'shortName' => 'Скорочена назва',
            'fullName' => 'Повна назва',
            'address' => 'Юридична адреса',
            'edrpou' => 'Код ЄДРПОУ'
        ];
    }
}
