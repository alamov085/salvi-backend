<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 28.05.2019
 * Time: 14:28
 */

namespace api\components;

use Baha2Odeh\RecaptchaV3\RecaptchaV3;
use yii\base\InvalidConfigException;
use yii\di\Instance;
use yii\validators\Validator;

/**
 * Class RecaptchaV3Validator
 * @package api\components
 */
class RecaptchaV3Validator extends Validator
{
    /**
     * @var bool
     */
    public $skipOnEmpty = false;


    /**
     * Recaptcha component
     * @var string|array|RecaptchaV3
     */
    public $component = 'recaptchaV3';


    /**
     * the minimum score for this request (0.0 - 1.0)
     * @var null|int
     */
    public $acceptance_score = null;

    /**
     * @var RecaptchaV3
     */
    private $_component = null;

    /**
     * @inheritdoc
     * @throws InvalidConfigException
     */
    public function init()
    {
        parent::init();
        $component = Instance::ensure($this->component, RecaptchaV3::class);
        if ($component == null) {
            throw new InvalidConfigException('component is required.');
        }
        $this->_component = $component;

        if ($this->message === null) {
            $this->message = 'The verification code is incorrect.';
        }
    }

    /**
     * @inheritdoc
     */
    protected function validateValue($value)
    {
        $result = $this->_component->validateValue($value);
        if ($result === false) {
            return [$this->message, []];
        }
        if ($this->acceptance_score !== null && $result < $this->acceptance_score) {
            return [$this->message, []];
        }
        return null;
    }
}
