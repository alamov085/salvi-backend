<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 05.02.2020
 * Time: 15:13
 */

namespace api\models\integration;

use api\helpers\NumberHelper;
use api\job\UploadPhoto;
use common\models\Category;
use common\models\Company;
use common\models\CompanyEntity;
use common\models\Contract;
use common\models\Product;
use common\models\ProductCategory;
use common\models\ProductPhoto;
use common\models\Specification;
use common\models\SpecificationProduct;
use Ramsey\Uuid\Uuid;
use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yii\helpers\Inflector;
use yii\helpers\VarDumper;

/**
 * Class SpecificationForm
 * @package api\models\integration
 */
class SpecificationForm extends Model
{
    public $edrpou;
    public $specName;
    public $Num;
    public $SDat;
    public $EDat;
    public $Status;
    public $specId;

    public function rules()
    {
        return [
            [['edrpou', 'specName', 'Num', 'Status'], 'required'],
//            ['price', 'number'],
//            [['uuid', 'specName'],'string'],
            [['edrpou', 'specName', 'Num', 'Status', 'SDat', 'EDat'], 'safe'],
        ];
    }


    public function save()
    {
        $db = \Yii::$app->db;
        $transaction = $db->beginTransaction();

        try {
            if(!$this->SDat) {
                $this->SDat = date('Y-m-d');
            }

            if(!$this->EDat) {
                $this->EDat = date('Y-m-d');
            }



            // получаем объект компании
            $companyItem = CompanyEntity::find()->where(['edrpou' => $this->edrpou])->one();
            // договор
            $contractItem = Contract::find()->where(['number' => $this->Num])->one();

            if(!$companyItem) {
                throw new \Exception("Компания с ЕДРПОУ {$this->edrpou} не найдена!");
            }

            if(!$contractItem) {
                throw new \Exception("Договор с номером {$this->Num} не найден!");
            }

            // получаем спецификацию по id компании и названию спецификации
            $specification = Specification::findOne([
                'company_id' => $companyItem->company_id,
                'name' => $this->specName,
                'contract_id' => $contractItem->id
            ]);

            if(!$specification) {
                $specification = new Specification();

                $specification->name = $this->specName;
                $specification->company_id = $companyItem->company_id;
                $specification->status = $this->Status;
                $specification->contract_id = $contractItem->id;
                $specification->date_start = strtotime($this->SDat.' 00:00:00');
                $specification->date_end = strtotime($this->EDat.' 23:59:59');
            } else {
                $specification->company_id = $companyItem->company_id;
                $specification->status = $this->Status;
                $specification->contract_id = $contractItem->id;
                $specification->date_start = strtotime($this->SDat.' 00:00:00');
                $specification->date_end = strtotime($this->EDat.' 23:59:59');


            }

            $specification->save();

            if(!$specification) {
                throw new \Exception("Specification not found");
            }

            $this->specId = $specification->id;

            $transaction->commit();
            return $this->specId;
        } catch (\Exception $exception) {
            $transaction->rollBack();
            return false;
        }

    }


}
