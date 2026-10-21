<?php

namespace api\helpers\UniSender;

use Exception;
use Yii;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

/**
 * This is the model class for table "unisender_sended".
 *
 * @property integer $id
 * @property string $email
 * @property string $subject
 * @property string $message
 * @property string $id_message
 * @property string $code
 * @property string $errors
 * @property string $apiHost
 * @property string $created_at
 *
 */
class UnisenderSended extends ActiveRecord
{

    public $replyto;
    public $encoding = 'UTF8';
    public $file;

    const CODE_ERROR = 'error';
    const CODE_SUCCESS = 'success';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'unisender_sended';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_at'], 'safe'],
            ['email', 'string'],
            [['code', 'errors'], 'string', 'max' => 255],
            [['id_message'], 'string', 'max' => 64],
            [['message', 'subject'], 'string']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'email' => 'Email',
            'subject' => 'Тема',
            'message' => 'Сообщение',
            'id_message' => 'Идентификатор сообщения',
            'code' => 'Статус',
            'errors' => 'Ошибки',
            'created_at' => 'Время создания',
        ];
    }

    /**
     * @return bool
     */
    public function send()
    {
        if ($this->code != self::CODE_SUCCESS) {
            return $this->sendEmail();
        }
        return true;
    }

    /**
     * @return bool
     */
    private function sendEmail()
    {
        $email = $this->email;
        if ($email == null) {
            //Емейла нет
            $this->code = self::CODE_ERROR;
            $this->errors = 'У пользователя не установлен email';
            $this->save();
            return true;
        }
//        $unisender = UniSenderFactory::factory();
        $arg = [
            "api_key" => UniSenderFactory::getApiKey(),
            "username" => UniSenderFactory::getSenderLogin(),
            "message" =>
                [
                    "body" => ["html" => $this->message],
                    "subject" => $this->subject,
                    "from_email" => UniSenderFactory::getSenderEmail(),
                    "from_name" => UniSenderFactory::getSenderName(),
                    "recipients" => [["email" => $email]]
                ]
        ];
        if ($this->file) {
            $arg['message']['attachments'][] = [
                "type" => "text/plain",
                "name" => "registration_".date('d_m_Y_H_i_s').".txt",
                "content" => $this->file
            ];
        }
        $result = $this->sendQuery("transactional/api/v1/email/send.json", $arg);
        if (isset($result['job_id'])) {
            if (isset($result['job_id']) && $result['status'] == 'success') {
                $this->id_message = $result['job_id'];
                $this->code = self::CODE_SUCCESS;
                if (isset($result['failed_emails'])) {
                    $this->errors = json_encode($result['failed_emails']);
                }
            } else {
                $errors = ArrayHelper::getColumn($result, 'message');
                $errorsText = (count($errors) > 0) ? implode(', ', $errors) : '';
                $this->code = self::CODE_ERROR;
                $this->errors = $errorsText;
                if (isset($result['failed_emails'])) {
                    $this->errors .= ' ' . json_encode($result['failed_emails']);
                }
            }
        } else {
            $this->code = self::CODE_ERROR;
            $this->errors = $result['message'];
        }
        $this->save();
        return true;
    }

    public function sendQuery($methodName, array $params = [])
    {
        $this->convertParamsEncoding($params);
        $body = json_encode($params);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10 ?: 10);
        $retryCount = 0;
        do {
            curl_setopt($ch, CURLOPT_URL, $this->getApiHost() . $methodName);
            $result = curl_exec($ch);
            $retryCount++;
        } while ($result === false && $retryCount < 0);
        curl_close($ch);
        return $result !== false ? json_decode($result, true) : null;
    }

    protected function getApiHost()
    {
        return "https://eu1.unione.io/ru/";
    }

    protected function convertParamsEncoding(&$params)
    {
        if ($this->encoding != 'UTF8') {
            if (function_exists('iconv')) {
                array_walk_recursive($params, [$this, 'iconv']);
            } else if (function_exists('mb_convert_encoding')) {
                array_walk_recursive($params, [$this, 'mb_convert_encoding']);
            }
        }
    }

    protected function iconv(&$value, $key)
    {
        $value = iconv($this->encoding, 'UTF-8//IGNORE', $value);
    }

    /**
     * @param string $value
     * @param string $key
     */
    protected function mb_convert_encoding(&$value, $key)
    {
        $value = mb_convert_encoding($value, 'UTF-8', $this->encoding);
    }

    /**
     * @param $view
     * @param $params
     * @return string
     */
    public static function renderView($view, $params)
    {
        $layoutPath = "@api/mail/";
        if (strpos($view, $layoutPath) === false) {
            $view = $layoutPath . $view;
        }
        $view .= '.php';
        return Yii::$app->view->renderFile($view, $params);
    }

    /**
     * @param $email
     * @param $subject
     * @param $html
     * @param bool $file
     * @return bool
     */
    public static function createAndSend($email, $subject, $html, $file = false)
    {
        $object = self::create($email, $subject, $html, $file);
        try {
            return $object->send();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * @param $email
     * @param $subject
     * @param $html
     * @return UnisenderSended
     */
    public static function create($email, $subject, $html, $file = false)
    {
        $object = new self();
        $object->email = $email;
        $object->subject = $subject;
        $object->message = $html;
        $object->created_at = date('U');
        $object->file = $file;
        return $object;
    }
}
