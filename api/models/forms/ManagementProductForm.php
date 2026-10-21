<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 06.02.2020
 * Time: 11:03
 */

namespace api\models\forms;

use api\helpers\IntegrationHelper;
use app\models\ProductPhoto;
use common\helpers\UploadHelper;
use common\models\Category;
use common\models\Product;
use common\models\ProductAttribute;
use common\models\ProductCategory;
use common\models\Unit;
use Exception;
use Ramsey\Uuid\Uuid;
use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\web\UploadedFile;

/**
 * Class ManagementProductForm
 * @package api\models\forms
 */
class ManagementProductForm extends Model
{
    public $id;
    public $uuid;
    public $name;
    public $buy_price;
    public $sku;
    public $unit_uuid;
    public $category_uuid;
    public $vendor_id;
    public $brand_id;
    public $uktzed;
    public $description;
    public $props;
    public $files;
    public $imagesModel;
    public $buffer;
    public $vat;

    public function rules()
    {
        return [
            [['id', 'props', 'uuid', 'imagesModel', 'sku', 'vendor_id', 'uktzed', 'brand_id'], 'safe'],
            [['name', 'buy_price', 'unit_uuid', 'category_uuid', 'vat'], 'required'],
            ['buy_price', 'number'],
            [['uuid', 'name', 'sku', 'unit_uuid', 'category_uuid', 'uktzed', 'description', 'vat'], 'string'],
            ['files', 'image', 'maxFiles' => 10, 'extensions' => 'png, jpeg, jpg'],
            ['buffer', 'boolean']
        ];
    }

    /**
     * @return bool
     * @throws \yii\base\Exception
     */
    public function create()
    {
        $unitModel = Unit::find()->where(['uuid' => $this->unit_uuid])->one();
        $model = new Product([
            'uuid'        => IntegrationHelper::generateUuid(true),
            'sku'         => $this->sku,
            'uktzed'      => $this->uktzed,
            'buy_price'   => $this->buy_price,
            'name'        => $this->name,
            'description' => $this->description,
            'unit_uuid'   => $this->unit_uuid,
            'unit_id'     => ($unitModel) ? $unitModel->id : 0,
            'vendor_id'   => $this->vendor_id,
            'brand_id'    => $this->brand_id,
            'vat'         => $this->vat,
            'is_buffer'   => 1,
        ]);
        if ($this->buffer) {
            $model->buffer_status = Product::BUFFER_WAITING;
        } else {
            $model->buffer_status = Product::BUFFER_EDIT;
        }
        $result = $model->save();
        if ($result) {
            $this->updateProps($model, false);
            $this->updateCategory($model);
            $this->updateImages($model);
        }
        return $result;
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'id'            => 'ID',
            'uuid'          => 'Код',
            'name'          => 'Назва',
            'buy_price'     => 'Ціна з ПДВ',
            'vat'           => 'Ставка ПДВ',
            'sku'           => 'Артикул',
            'unit_uuid'     => 'Одиниця вимірювання',
            'category_uuid' => 'Категорія',
            'vendor_id'     => 'Постачальник',
            'brand_id'      => 'Марка/бренд',
            'uktzed'        => 'Код УКТЗЕД',
            'description'   => 'Опис',
            'props'         => 'Додаткові атрибути',
            'files'         => 'Зображення',
        ];
    }

    public function update()
    {
        $model = Product::find()->where(['id' => $this->id])->one();
        if ($model) {
            $unitModel = Unit::find()->where(['uuid' => $this->unit_uuid])->one();
            $model->sku = $this->sku;
            $model->uktzed = $this->uktzed;
            $model->buy_price = $this->buy_price;
            $model->name = $this->name;
            $model->description = $this->description;
            $model->unit_uuid = $this->unit_uuid;
            $model->unit_id = ($unitModel) ? $unitModel->id : 0;
            $model->vendor_id = $this->vendor_id;
            $model->brand_id = $this->brand_id;
            $model->vat = $this->vat;
            if ($model->is_buffer) {
                if ($this->buffer) {
                    $model->buffer_status = Product::BUFFER_WAITING;
                } else {
                    $model->buffer_status = Product::BUFFER_EDIT;
                }
            }
            $result = $model->save();
            if ($result) {
                //TODO зделать проверку принадлежат ли аттрибуты категории
                $this->updateProps($model);
                $this->updateCategory($model);
                $this->updateImages($model);
            }
            return $result;
        }
        return false;
    }

    /** Получаем все дерево родителей категории
     * @param $uuid
     * @return array
     */
    public static function getTreeCategory($uuid)
    {
        $categories = Category::find()
            ->where(['delete' => 0])
            ->asArray()
            ->all();
        $categories = ArrayHelper::index($categories, 'uuid');
        $result = [];
        if (isset($categories[$uuid])) {
            $result[] = $categories[$uuid];
            self::getParentCategory($categories, $result, $categories[$uuid]['parent_uuid']);
        }
        return $result;
    }

    /**
     * @param $categories
     * @param $result
     * @param $uuid
     */
    public static function getParentCategory($categories, &$result, $uuid)
    {
        if (isset($categories[$uuid])) {
            $result[] = $categories[$uuid];
            self::getParentCategory($categories, $result, $categories[$uuid]['parent_uuid']);
        }
    }

    private function updateProps(Product $model, $update = true)
    {
        if ($update) {
            ProductAttribute::deleteAll(['product_uuid' => $model->uuid]);
        }
        if ($this->props) {
            $propsModel = [];
            foreach ($this->props as $prop) {
                $propsModel[] = [
                    'attribute_id' => $prop['id'],
                    'value_id'     => $prop['value_uuid'],
                    'product_uuid' => $model->uuid
                ];
            }
            if ($propsModel) {
                try {
                    Yii::$app->db->createCommand()->batchInsert(
                        ProductAttribute::tableName(),
                        ['attribute_id', 'value_id', 'product_uuid'],
                        $propsModel
                    )->execute();
                } catch (Exception $e) {
                    Yii::error($e);
                }
            }
        }
    }

    private function updateCategory(Product $model)
    {
        ProductCategory::deleteAll(['product_uuid' => $model->uuid]);
        $categories = self::getTreeCategory($this->category_uuid);
        if ($categories) {
            $categoriesInsert = [];
            foreach ($categories as $k => $category) {
                $categoriesInsert[] = [
                    'category_id'   => $category['id'],
                    'product_id'    => $model->id,
                    'category_uuid' => $category['uuid'],
                    'product_uuid'  => $model->uuid,
                    'is_main'       => ($k > 0) ? 0 : 1
                ];
            }
            if ($categoriesInsert) {
                try {
                    Yii::$app->db->createCommand()->batchInsert(
                        ProductCategory::tableName(),
                        ['category_id', 'product_id', 'category_uuid', 'product_uuid', 'is_main'],
                        $categoriesInsert
                    )->execute();
                } catch (Exception $e) {
                    Yii::error($e);
                }
            }
        }
    }

    private function updateImages(Product $model)
    {
        if ($this->imagesModel) {
            $this->imagesModel = Json::decode($this->imagesModel);
            $photos = ProductPhoto::find()
                ->where(['product_uuid' => $model->uuid])
                ->andWhere(['not in', 'id', ArrayHelper::getColumn($this->imagesModel, 'id')])
                ->all();
        } else {
            $photos = ProductPhoto::find()
                ->where(['product_uuid' => $model->uuid])
                ->all();
        }
        if ($photos) {
            foreach ($photos as $photo) {
                $photo->delete();
            }
        }
        if ($this->files) {
            foreach ($this->files as $image) {
                UploadHelper::saveProductPhoto($model, $image);
            }
        }
    }
}
