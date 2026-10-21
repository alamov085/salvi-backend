<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 08.10.2019
 * Time: 12:18
 */

namespace common\helpers;

use api\job\EndCronJob;
use api\job\ParseSftpNodeJob;
use SimpleXMLElement;
use XMLReader;
use Yii;

/**
 * Class XmlParse
 * @package common\helpers
 */
class XmlParse
{
    public $xml;
    public $node;
    public $class;
    public $updatedId = [];

    public function __construct(string $file, string $node, $class)
    {
        $this->xml = new XMLReader();
        $this->xml->open($file, null, LIBXML_COMPACT | LIBXML_PARSEHUGE);
        $this->node = $node;
        $this->class = $class;
    }

    public function __destruct()
    {
        $this->xml->close();
    }

    public function read()
    {
        while ($this->xml->read() && $this->xml->name != $this->node) {
            ;
        }
        $k = 1;
        while ($this->xml->name == $this->node) {
            $k++;
            try {
//                if (memory_get_usage(true) > 1048576 * 200) {
//                    Yii::$app->queueFile->push(new ParseSftpNodeJob([
//                        'xml' => $this->xml->readOuterXML()
//                    ]));
//                } else {
                    $element = new SimpleXMLElement($this->xml->readOuterXML());
                    $temp = new $this->class($element);
                    $temp->readNode();
//                }
            } catch (\Exception $e) {
                \Yii::error($e);
                print_r($e->getMessage());
                echo "\n";
            }
            $this->xml->next($this->node);
            unset($element);
            unset($temp);
        }
    }

    public function update()
    {
        call_user_func([$this->class, 'update'], $this->updatedId);
        return true;
    }
}
