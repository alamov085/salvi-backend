<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 06.12.2019
 * Time: 15:58
 */

namespace api\job;

use api\helpers\ParseXlsx;
use api\models\forms\ImportForm;
use common\models\User;
use yii\base\BaseObject;
use yii\queue\JobInterface;

/**
 * Class ParseXlsxJob
 * @package api\job
 */
class ParseXlsxJob extends BaseObject implements JobInterface
{
    /** @var $id int */
    public $id;
    /** @var $user User */
    public $user;
    /** @var $name string */
    public $name;

    public function execute($queue)
    {
        switch ($this->id) {
            case ImportForm::FILE_LOCATION:
                return ParseXlsx::readLocation($this->user, $this->name);
                break;
            case ImportForm::FILE_COST:
                return ParseXlsx::readCost($this->user, $this->name);
                break;
            case ImportForm::FILE_BUDGET:
                return ParseXlsx::readBudget($this->user, $this->name);
                break;
            case ImportForm::FILE_USER:
                return ParseXlsx::readUser($this->user, $this->name);
                break;
            case ImportForm::FILE_SPEC:
                return ParseXlsx::readSpec($this->user, $this->name);
                break;
            case ImportForm::FILE_PRICE:
                return ParseXlsx::updatePrice($this->name);
                break;
            case ImportForm::FILE_CATALOG:
                return ParseXlsx::updateCatalog($this->name);
                break;
            case ImportForm::FILE_IMAGE:
                return ParseXlsx::updateImage($this->name);
                break;
            default:
                break;
        }
        return true;
    }
}
