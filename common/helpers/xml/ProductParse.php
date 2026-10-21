<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 08.10.2019
 * Time: 12:30
 */

namespace common\helpers\xml;

use common\models\Attribute;
use common\models\AttributeValue;
use common\models\Category;
use common\models\Product;
use common\models\ProductAttribute;
use common\models\ProductCategory;
use common\models\ProductPhoto;
use common\models\Unit;
use common\models\Vendor;
use SimpleXMLElement;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;

/**
 * Class ProductParse
 * @package common\helpers\xml
 */
class ProductParse
{
    public $xml;
    /** @var $product Product | boolean */
    public $product;
    public $categories;
    public $photo;
    public $params;
    /** @var Category | boolean */
    public $mainCategory;

    /**
     * ProductParse constructor.
     * @param SimpleXMLElement $el
     */
    public function __construct(SimpleXMLElement $el)
    {
        $this->xml = $el;
    }

    public function readNode()
    {
        if (!empty($this->xml)) {
            $temp = (array)$this->xml;
            $productUuid = ArrayHelper::getValue($temp, '@attributes.uid');
            $productName = ArrayHelper::getValue($temp, '@attributes.name');
            $this->product = Product::find()
                ->where(['uuid' => $productUuid])
                ->one();
            if (!$this->product) {
                $this->product = new Product();
                $this->product->uuid = $productUuid;
            }
            $this->product->name = $productName;
            foreach ($this->xml as $node) {
                $name = $node->getName();
//                print_r($name);
//                echo "\n";
                switch ($name) {
                    case 'groups':
                        $this->groups($node);
                        break;
                    case 'Art':
                        $this->art($node);
                        break;
                    case 'unit':
                        $this->unit($node);
                        break;
                    case 'description':
                        $this->description($node);
                        break;
                    case 'vat':
                        $this->vat($node);
                        break;
                    case 'price':
                        $this->price($node);
                        break;
                    case 'vendor':
                        $this->vendor($node);
                        break;
                    case 'param':
                        $this->param($node);
                        break;
                    case 'picture':
                        $this->picture($node);
                        break;
                    case 'sinonim':
                        $this->synonym($node);
                        break;
                    case 'sort':
                        $this->sort($node);
                        break;
                    default:
                        break;
                }
            }
            $percent = explode('%', $this->product->vat);
            $percent = ArrayHelper::getValue($percent, 0, 'number');
            if (is_numeric($percent) && $percent > 0) {
                $percent = (100 + $percent) / 100;
                $this->product->buy_price = $this->product->buy_price * $percent;
            }
            $this->product->delete = 0;
            $this->product->updated_at = date('U');
            if ($this->product->save()) {
                print_r([$this->product->uuid => $this->product->buy_price]);
                echo "\n";
                $this->savePicture();
                $this->saveParams();
                $this->saveCategory();
                return $this->product->id;
            }
        }
    }

    private function groups(SimpleXMLElement $node)
    {
        $this->categories = [];
        $data = (array)$node;
        if (!empty($data)) {
            $reverse = array_reverse($data['group']);
            $count = count($reverse);
            foreach ($reverse as $k => $category) {
                $category = (array)$category;
                $group = (array)$reverse;
                $categoryModel = Category::find()
                    ->where(['uuid' => ArrayHelper::getValue($category, '@attributes.uid')])
                    ->one();
                if (!$categoryModel) {
                    $categoryModel = new Category();
                }
                $parentCategory = false;
                if (isset($group[$k - 1])) {
                    $key = (array)$group[$k - 1];
                    $parentCategory = Category::find()
                        ->where(['uuid' => ArrayHelper::getValue($key, '@attributes.uid')])
                        ->one();
                    $parentCategory->delete = 0;
                    $parentCategory->save();
                }
                $categoryModel->setAttributes([
                    'uuid'        => ArrayHelper::getValue($category, '@attributes.uid'),
                    'name'        => ArrayHelper::getValue($category, '@attributes.name'),
                    'is_view'     => ($count - 1 == $k) ? 1 : 0,
                    'has_child'   => ($count - 1 == $k) ? 0 : 1,
                    'parent_uuid' => ($parentCategory) ? $parentCategory->uuid : "",
                    'parent_id'   => ($parentCategory) ? $parentCategory->id : null,
                    'delete'      => 0
                ]);
                $categoryModel->save();
                if ($count - 1 == $k) {
                    $this->mainCategory = $categoryModel;
                }
                $this->categories[] = [
                    'category_uuid' => ArrayHelper::getValue($category, '@attributes.uid'),
                    'category_id'   => $categoryModel->id,
                    'is_main'       => ($count - 1 == $k) ? 1 : 0
                ];
            }
        }
    }

    /**
     * @throws \yii\db\Exception
     */
    private function saveCategory()
    {
        if (!empty($this->categories)) {
            ProductCategory::deleteAll(['product_uuid' => $this->product->uuid]);
            foreach ($this->categories as $k => $value) {
                $this->categories[$k]['product_uuid'] = $this->product->uuid;
                $this->categories[$k]['product_id'] = $this->product->id;
            }
            Yii::$app->db->createCommand()->batchInsert(
                ProductCategory::tableName(),
                ['category_uuid', 'category_id', 'is_main', 'product_uuid', 'product_id'],
                $this->categories
            )->execute();
        }
    }

    /**
     * @param SimpleXMLElement $node
     */
    private function art(SimpleXMLElement $node)
    {
        $node = (array)$node;
        if (!empty($node)) {
            $this->product->sku = ArrayHelper::getValue($node, 0);
        }
    }

    /**
     * @param SimpleXMLElement $node
     */
    private function unit(SimpleXMLElement $node)
    {
        $unit = (array)$node;
        $unitUuid = ArrayHelper::getValue($unit, '@attributes.uid');
        $unitName = ArrayHelper::getValue($unit, '@attributes.name');
        $unitModel = Unit::find()
            ->where(['uuid' => $unitUuid])
            ->one();
        if (!$unitModel) {
            $unitModel = new Unit(['uuid' => $unitUuid, 'name' => $unitName]);
            $unitModel->save();
        }
        $this->product->unit_uuid = $unitUuid;
        if ($unitModel) {
            $this->product->unit_id = $unitModel->id;
        }
    }

    /**
     * @param SimpleXMLElement $node
     */
    private function description(SimpleXMLElement $node)
    {
        $node = (array)$node;
        if (!empty($node)) {
            $this->product->description = ArrayHelper::getValue($node, 0);
        } else {
            $this->product->description = '';
        }
    }

    /**
     * @param SimpleXMLElement $node
     */
    private function vat(SimpleXMLElement $node)
    {
        $node = (array)$node;
        if (!empty($node)) {
            $this->product->vat = ArrayHelper::getValue($node, 0);
        }
    }

    /**
     * @param SimpleXMLElement $node
     */
    private function synonym(SimpleXMLElement $node)
    {
        $node = (array)$node;
        if (!empty($node)) {
            $this->product->synonym = ArrayHelper::getValue($node, 0);
        }
    }

    /**
     * @param SimpleXMLElement $node
     */
    private function sort(SimpleXMLElement $node)
    {
        $node = (array)$node;
        if (!empty($node)) {
            $this->product->sort = ArrayHelper::getValue($node, 0);
        }
    }

    /**
     * @param SimpleXMLElement $node
     */
    private function price(SimpleXMLElement $node)
    {
        $node = (array)$node;
//        print_r($node);
//        echo "\n";
        if (!empty($node)) {
            $this->product->buy_price = ArrayHelper::getValue($node, 0);
        }
    }

    /**
     * @param SimpleXMLElement $node
     */
    private function vendor(SimpleXMLElement $node)
    {
        $vendor = (array)$node;
        if (!empty($vendor)) {
            $vendorUuid = ArrayHelper::getValue($vendor, '@attributes.uid');
            $vendorName = ArrayHelper::getValue($vendor, '@attributes.name');
            $vendorModel = Vendor::find()
                ->where(['uuid' => $vendorUuid])
                ->one();
            if (!$vendorModel) {
                $vendorModel = new Vendor(['uuid' => $vendorUuid, 'name' => $vendorName]);
                $vendorModel->save();
            }
            $this->product->vendor_id = $vendorModel->id;
        }
    }

    /**
     * @param SimpleXMLElement $node
     */
    private function param(SimpleXMLElement $node)
    {
        $node = (array)$node;
        $name = ArrayHelper::getValue($node, '@attributes.name');
        $value = ArrayHelper::getValue($node, 0);
        $this->params[] = [
            'name'  => $name,
            'value' => $value
        ];
    }

    /**
     * @param SimpleXMLElement $node
     */
    private function picture(SimpleXMLElement $node)
    {
        $node = (array)$node;
        if (!empty($node)) {
            $binary = ArrayHelper::getValue($node, '@attributes.binary');
            $image_base64 = base64_decode($binary);
            //$name = uniqid() . '_' . time() . '.png';
            $name = $this->product->uuid . '.png';
            $path = Yii::getAlias('@api') . '/web/product/';
            FileHelper::createDirectory($path);
            $file = $path . $name;
            file_put_contents($file, $image_base64);
            $this->photo = $name;
        }
    }

    private function savePicture()
    {
        if (!empty($this->photo)) {
            $photos = ProductPhoto::find()->where(['product_uuid' => $this->product->uuid])->one();
            if (!$photos) {
                $model = new ProductPhoto([
                    'name'         => $this->photo,
                    'product_uuid' => $this->product->uuid,
                    'product_id'   => $this->product->id
                ]);
                $model->save();
            }
        }
    }

    private function saveParams()
    {
        if (!empty($this->params)) {
            ProductAttribute::deleteAll(['product_uuid' => $this->product->uuid]);
            foreach ($this->params as $param) {
                $attr = Attribute::find()
                    ->where(['name' => $param['name']])
                    ->andWhere(['category_uuid' => $this->mainCategory->uuid])
                    ->one();
                if (!$attr) {
                    $attr = new Attribute([
                        'name'          => $param['name'],
                        'category_uuid' => $this->mainCategory->uuid
                    ]);
                    $attr->save();
                }
                $value = AttributeValue::find()
                    ->where(['name' => $param['value']])
                    ->andWhere(['attribute_id' => $attr->id])
                    ->one();
                if (!$value) {
                    $value = new AttributeValue([
                        'name'         => $param['value'],
                        'attribute_id' => $attr->id
                    ]);
                    $value->save();
                }
                $model = new ProductAttribute([
                    'attribute_id' => $attr->id,
                    'product_uuid' => $this->product->uuid,
                    'value_id'     => $value->id
                ]);
                $model->save();
            }
        }
    }

    public static function update($ids)
    {
        $products = Product::find()->select(['id', 'uuid'])
            ->where(['not in', 'id', $ids])
            ->asArray()
            ->all();
        if ($products) {
            Product::updateAll(['delete' => 1], ['in', 'id', ArrayHelper::getColumn($products, 'id')]);
//            ProductCategory::deleteAll(['in', 'product_uuid', ArrayHelper::getColumn($products, 'uuid')]);
//            print_r(ArrayHelper::getColumn($products, 'uuid'));
        }
    }
}
