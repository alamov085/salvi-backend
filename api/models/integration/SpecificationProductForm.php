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
class SpecificationProductForm extends Model
{
    public $specId;
    public $uuid;
    public $qty;
    public $price;

    public $productId;


    public function rules()
    {
        return [
            [['specId', 'uuid', 'qty', 'price'], 'required'],
//            ['price', 'number'],
//            [['uuid', 'specName'],'string'],
            [['specId', 'uuid', 'qty', 'price'], 'safe'],
        ];
    }


    public function save()
    {
        $db = \Yii::$app->db;
        $transaction = $db->beginTransaction();

        try {
            // получаем товар
            $productItem = Product::find()->where(['uuid' => $this->uuid])
                ->andWhere(['deleted' => 0])
                ->andWhere(['is_buffer' => 0])
                ->one();

            if(!$productItem) {
                $transaction->rollBack();
                return false;
//                throw new \Exception("Товар с UUID {$this->uuid} не найден!");
            }

            // получаем спецификацию по id компании и названию спецификации
            $specification = Specification::find()->where([
                'id' => $this->specId,
            ])->one();

            if(!$specification) {
                die(var_dump($specification));
                $transaction->rollBack();
                return false;
            }

            // ищем товар спецификации
            $specificationProduct = SpecificationProduct::find()->where([
                'product_uuid' => $productItem->uuid,
                'specification_id' => $specification->id,
            ])->one();

            if(!$specificationProduct) {
                $specificationProduct = new SpecificationProduct();
            }

            $attributes = [
                'product_id' => $productItem->id,
                'product_uuid' => $productItem->uuid,
                'specification_id' => $specification->id,
                'price' => NumberHelper::convert((float)$this->price),
                'markup' => 0,
                'manual_markup' => 1,
                'use_markup' => 0,
                'quantity' => (int)$this->qty,
                'status' => 1
            ];

            $specificationProduct->attributes = $attributes;

            if($specificationProduct->save()) {
                $this->productId = $specificationProduct->product_id;

                $transaction->commit();
                return true;
            } else {
                throw new \Exception("Товар с UUID {$this->uuid} не добавлен в спецификацию!");
            }

        } catch (\Exception $exception) {
            die(var_dump($exception->getMessage()));

            $transaction->rollBack();
            return false;
        }




    }


}
