<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 19.09.2019
 * Time: 11:38
 */

namespace api\job;

use api\helpers\UserEmailHelper;
use yii\base\BaseObject;
use yii\queue\JobInterface;

/**
 * Class SendEmail
 * @package api\job
 */
class SendEmail extends BaseObject implements JobInterface
{
    /** @var $view string */
    public $view;
    /** @var $data array */
    public $data;
    /** @var $subject string */
    public $subject;
    /** @var bool|string $file */
    public $file = false;
    /** @var bool  */
    public $firstTime = true;

    public function execute($queue)
    {
        return UserEmailHelper::sendMsg($this->view, $this->data, $this->subject, $this->file, $this->firstTime);
    }
}
