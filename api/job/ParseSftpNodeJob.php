<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 29.04.2020
 * Time: 12:28
 */

namespace api\job;

use common\helpers\xml\ProductParse;
use SimpleXMLElement;
use yii\base\BaseObject;
use yii\queue\JobInterface;
use yii\queue\Queue;

/**
 * Class ParseSftpNodeJob
 * @package api\job
 */
class ParseSftpNodeJob extends BaseObject implements JobInterface
{
    public $xml;

    /**
     * @inheritDoc
     */
    public function execute($queue)
    {
        $element = new SimpleXMLElement($this->xml);
        $temp = new ProductParse($element);
        $temp->readNode();
    }
}
