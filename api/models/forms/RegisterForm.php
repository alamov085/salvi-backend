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
use common\models\AdminEmail;
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
class RegisterForm extends Model
{
    public $type;
    public $email;
    public $shortName;
    public $fullName;
    public $address;
    public $edrpou;
    public $inn;
    public $phone;
    public $rules;
    public $recaptchaToken;
    public $firstName;
    public $lastName;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['type', 'phone', 'rules'], 'required'],

            ['phone', 'match', 'pattern' => '/^\d{9}$/', 'message' => 'Поле має містити 9 цифр'],

            ['email', 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            ['email', 'string', 'max' => 255],
            ['email', 'unique', 'targetClass' => '\common\models\User', 'message' => 'Цей email вже зайнятий'],
            ['phone', 'unique', 'targetClass'     => '\common\models\User',
                                'targetAttribute' => 'phone_number', 'message' => 'Цей телефон вже зайнятий'],

            ['type', 'in', 'range' => array_keys(Company::getTypes())],
            [['firstName', 'lastName'], 'required', 'on' => 'fop'],
            [['shortName'], 'string', 'on' => 'ur'],

            ['rules', 'required', 'requiredValue' => true,
                                  'message'       => 'Ви повинні погодитися з правилами'],
            [['recaptchaToken'], RecaptchaV3Validator::class, 'acceptance_score' => null],
        ];
    }

    public function register()
    {
        switch ($this->type) {
            case 1:
                $result = [
                    'Тип'               => 'ФОП',
                    'Email'             => $this->email,
                    'Прізвище'          => $this->lastName,
                    'Ім\'я'             => $this->firstName,
                    'Мобільний телефон' => '+380' . $this->phone
                ];
                break;
            case 2:
                $result = [
                    'Тип'               => 'Юр. лицо',
                    'Email'             => $this->email,
                    'Назва компанії'    => $this->shortName,
                    'Мобільний телефон' => '+380' . $this->phone,
                ];
                break;
            default:
                $result = false;
                break;
        }
        if ($result) {
            $text = '';
            $text2 = '';
            foreach ($result as $k => $value) {
                $text .= $k . ": " . $value . "\n";
                $text2 .= $k . ": " . $value . "<br>";
            }
            $txt = base64_encode($text);
            $emails = AdminEmail::find()->where(['status' => AdminEmail::STATUS_ACTIVE])->all();
            if ($emails) {
                foreach ($emails as $email) {
                    UserEmailHelper::defaultMessage(
                        [
                            'view' => 'registrationAdmin',
                            'user' => ['email' => $email->email],
                            'text' => $text2
                        ],
                        $txt
                    );
                }
            } else {
                UserEmailHelper::defaultMessage(
                    [
                        'view' => 'registrationAdmin',
                        'user' => ['email' => Yii::$app->params['defaultEmail']],
                        'text' => $text2
                    ],
                    $txt
                );
            }
            return true;
        }
        return false;
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function registerOld()
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
            $user->setPassword(Yii::$app->security->generateRandomString(8));
            $user->generateAuthKey();
            $user->generateEmailVerificationToken();
            $user->status = User::STATUS_INACTIVE;
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
            'type'               => $this->type,
            'short_name'         => $this->lastName . ' ' . $this->firstName,
            'full_name'          => "",
            'address'            => '',
            'edrpou'             => '',
            'inn'                => "",
            'status'             => Company::STATUS_WAITING,
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
            'type'               => $this->type,
            'short_name'         => $this->shortName,
            'full_name'          => "",
            'address'            => "",
            'edrpou'             => "",
            'inn'                => "",
            'status'             => Company::STATUS_WAITING,
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
            'company_id'  => $company->id,
            'short_name'  => $company->short_name,
            'full_name'   => "",
            'address'     => "",
            'bank_number' => '',
            'bank_name'   => '',
            'mfo'         => '',
            'edrpou'      => "",
            'inn'         => "",
            'tax_payer'   => 0,
            'status'      => CompanyEntity::STATUS_DISMISSED,
            'from_date'   => null,
            'is_main'     => 1
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
            'lastName'  => 'Прізвище',
            'email'     => 'Email',
            'inn'       => 'ІПН',
            'phone'     => 'Мобільний телефон',
            'shortName' => 'Назва компанії',
            'fullName'  => 'Повна назва',
            'address'   => 'Юридична адреса',
            'edrpou'    => 'Код ЄДРПОУ'
        ];
    }
}
