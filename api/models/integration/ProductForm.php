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
use common\models\Product;
use common\models\ProductCategory;
use common\models\ProductFilter;
use common\models\ProductPhoto;
use Ramsey\Uuid\Uuid;
use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yii\helpers\Inflector;
use common\models\ProductProperty;
use common\models\ProductFilters;
use common\models\ProductPropertyValue;

/**
 * Class ProductForm
 * @package api\models\integration
 */
class ProductForm extends Model
{
    public $uuid;
    public $uktzed;
    public $props;
    public $sku;
    public $name;
    public $category;
    public $brand;
    public $vendor;
    public $description;
    public $unit;
    public $image;
    public $buy_price;
    public $vat;
    public $deleted;
    public $sort;
    public $sinonim;
    public $leftovers;
    public $multiplicity;
    public $leftovers_salvi;
    public $leftovers_supplier;
    public $supplier_delivery_postpone;
    public $strict_multiplicity;
    public $additional_assortment;
    public $is_temperature_conditions;
    public $discount_expire_date;
    public $discount_price;
    public $season_goods;
    public $popular;

    public $listHideUuidCategory = [
        '5a5ff689-c9d6-11eb-a3f1-00155d008238',
        '70a5cd83-c9d6-11eb-a3f1-00155d008238',
        '19813c23-297a-11ea-a23b-00155dd08a09',
        '1c23da7b-3211-11ea-a23b-00155dd08a09',
        '99eff5d6-3c50-11ea-a23c-00155dd08a09',
        '302d512e-386b-11ea-a23c-00155dd08a09',
        '362e1d96-386b-11ea-a23c-00155dd08a09',
        '1589d3ef-dd5d-11eb-a3f1-00155d008238',
    ];

    public function rules()
    {
        return [
            [['uuid', 'name', 'category', 'unit', 'buy_price', 'deleted'], 'required'],
            ['buy_price', 'number'],
            ['multiplicity', 'number'],
            [['vat','strict_multiplicity','additional_assortment','is_temperature_conditions','discount_expire_date'], 'string'],
            [['deleted'], 'integer'],
            [['props', 'sku', 'description', 'image', 'uktzed', 'brand', 'vendor', 'sort', 'sinonim','discount_price','season_goods','popular'], 'safe'],
            [['unit'], 'modelValidate', 'skipOnEmpty' => false, 'skipOnError' => false]
        ];
    }

    public function modelValidate($attribute)
    {
        $modelName = 'api\\models\\integration\\' . Inflector::camelize($attribute . '_form');
        $model = new $modelName();
        if ($model->load($this->$attribute, '') && !$model->validate()) {
            $this->addErrors([$attribute => $model->errors]);
            return false;
        }
    }

    public function save()
    {
    	$statuses = [Product::STATUS_ACTIVE, Product::STATUS_NOT_AVAILABLE, Product::STATUS_HIDDEN];
        $db = \Yii::$app->db;
        $transaction = $db->beginTransaction();
        $uktzedForm = new UktzedForm($this->uktzed);
        $uktzed = $uktzedForm->save();
        $brandForm = new BrandForm($this->brand);
        $brand = $brandForm->save();
        $vendorForm = new VendorForm($this->vendor);
        $vendor = $vendorForm->save();
        $unitForm = new UnitForm($this->unit);
        $unit = $unitForm->save();
        if ($unit) {
            $product = Product::find()
                ->where(['uuid' => $this->uuid])
                ->one();
            if (!$product) {
                $product = new Product([
                    'uuid' => $this->uuid
                ]);
            }
            $product->name = $this->name;
            $product->buy_price = NumberHelper::convert($this->buy_price);
//            $percent = explode('%', $this->vat);
//            $percent = ArrayHelper::getValue($percent, 0, 'number');
//            if (is_numeric($percent) && $percent > 0) {
//                $percent = (100 + $percent) / 100;
//                $product->buy_price = NumberHelper::convert($this->buy_price * $percent);
//            }

	        if($this->discount_price)
            {
                $product->discount_price = NumberHelper::convert($this->buy_price);
                $product->buy_price = NumberHelper::convert($this->discount_price);
            }
            else
            {
                $product->discount_price = 0;
            }

            $product->sku = $this->sku;
            $product->vat = $this->vat;
            $product->uktzed = ($uktzed) ? $uktzed->uuid : null;
            $product->description = $this->description;
            $product->unit_uuid = $unit->uuid;
            $product->unit_id = $unit->id;
            $product->vendor_id = ($vendor) ? $vendor->id : null;
            $product->brand_id = ($brand) ? $brand->id : null;
            $product->deleted = (in_array($this->deleted, $statuses)) ? $this->deleted : Product::STATUS_NOT_AVAILABLE;
            $product->sort = $this->sort;
            $product->synonym = $this->sinonim;
            $product->multiplicity = $this->multiplicity;
            $product->strict_multiplicity = ($this->strict_multiplicity == 'true') ? 1 : 0;
            $product->is_temperature_conditions = ($this->is_temperature_conditions == 'true') ? 1 : 0;
            $product->is_season_goods = ($this->season_goods == 'true') ? 1 : 0;
            $product->is_popular = ($this->popular == 'true') ? 1 : 0;
            $product->discount_expire_date = ($this->discount_expire_date) ? strtotime($this->discount_expire_date) : 0;
            $product->additional_assortment = ($this->additional_assortment == 'true') ? 1 : 0;


            if ($product->save())
            {
                ProductFilters::deleteAll([
                    'product_id' => $product->id
                ]);

//                    Yii::$app->cache->delete('product_filter_properties');
//                    Yii::$app->cache->delete('product_filter_values');
//die();
                $properties = $this->getPropertyCache();
                $values = $this->getValueCache();

                foreach ($this->props as $prop)
                {
                    if (empty($prop['attr']) || empty($prop['value']))
                    {
                        continue;
                    }

                    $propertyName = trim($prop['attr']);
                    $propertySlug = Inflector::slug($propertyName);

                    $valueName = trim($prop['value']);
                    $valueSlug = Inflector::slug(str_replace(',', '-', $valueName));

                    $property = $properties[$propertySlug] ?? null;


                    if ($property === null)
                    {
                        $property = new ProductProperty();
                        $property->name = $propertyName;
                        $property->slug = $propertySlug;
                        $property->sort = count($properties);

                        $property->save();

                        Yii::$app->cache->delete('product_filter_properties');

                        $properties = $this->getPropertyCache();

                        $property = $properties[$propertySlug];
                    }

                    $value = $values[$property['id']][$valueSlug] ?? null;

                    if ($value === null)
                    {
                        $sort = 0;

                        if(isset($values[$property['id']]) && isset($values[$property['id']][$valueSlug]))
                        {
                            $sort = $values[$property['id']][$valueSlug]['sort'] + 1;
                        }
                        else
                        {
                            foreach ($values as $keyValue => $valuesList)
                            {
                                foreach ($valuesList as $value)
                                {
                                    ++$sort;
                                }
                            }

                            ++$sort;
                        }


                        $value = new ProductPropertyValue();
                        $value->property_id = $property['id'];
                        $value->name = $valueName;
                        $value->slug = $valueSlug;
                        $value->sort = $sort;
                        $value->save();

                        Yii::$app->cache->delete('product_filter_values');

                        $values = $this->getValueCache();

                        $value = $values[$property['id']][$valueSlug];
                    }

                    try
                    {
                        $filter = new ProductFilters();
                        $filter->product_id = $product->id;
                        $filter->property_id = $property['id'];
                        $filter->value_id = $value['id'];

                        $filter->save(false);
                    }
                    catch (\Exception $e)
                    {
//                        echo '<pre>';
//                        print_r($e->getMessage());
//                        echo '</pre>';
                    }
                }


                if ($brand && $brand->uuid != '00000000-0000-0000-0000-000000000000')
                {
                    $properties = $properties ?? $this->getPropertyCache();
                    $values = $values ?? $this->getValueCache();

                    $property = $properties['brand'] ?? null;


                    if ($property === null)
                    {
                        $property = new ProductProperty();
                        $property->name = 'Бренд';
                        $property->slug = 'brand';
                        $property->sort = count($properties);
                        $property->save(false);


                        Yii::$app->cache->delete('product_filter_properties');

                        $properties = $this->getPropertyCache();

                        $property = $properties['brand'];
                    }

                    $existingValue = ProductPropertyValue::find()
                        ->where(['property_id' => $property['id'], 'name' => $brand->name])
                        ->asArray()
                        ->one();

                    if ($existingValue !== null)
                    {
                        $value = $existingValue;
                    }
                    else
                    {
                        $valueSlug = $brand->code;
                        $suffix = 2;

                        while (isset($values[$property['id']][$valueSlug]))
                        {
                            $valueSlug = $brand->code . '-' . $suffix;
                            $suffix++;
                        }

                        $sort = 0;

                        foreach ($values as $valuesList)
                        {
                            $sort += count($valuesList);
                        }

                        ++$sort;

                        $value = new ProductPropertyValue();
                        $value->property_id = $property['id'];
                        $value->name = $brand->name;
                        $value->slug = $valueSlug;
                        $value->sort = $sort;
                        $value->save(false);

                        Yii::$app->cache->delete('product_filter_values');

                        $values = $this->getValueCache();

                        $value = $values[$property['id']][$valueSlug];
                    }

                    $filter = new ProductFilters();
                    $filter->product_id = $product->id;
                    $filter->property_id = $property['id'];
                    $filter->value_id = $value['id'];
                    $filter->save(false);
                }

                ProductCategory::deleteAll(['product_uuid' => $product->uuid]);
                $this->saveCategory($product, trim($this->category), 1);
                if ($this->image) {
                    Yii::$app->queue->push(new UploadPhoto([
                        'product' => $product,
                        'images'  => $this->image
                    ]));
                }
                $transaction->commit();
                return true;
            }
            $transaction->rollBack();
            return false;
        }
        $transaction->rollBack();
        return false;
    }

    private function getPropertyCache(): array
    {
        $cache = Yii::$app->cache->get('product_filter_properties');

        if ($cache !== false)
        {
            return $cache;
        }

        $cache = ProductProperty::find()
            ->select(['id','slug','sort'])
            ->asArray()
            ->all();

        $result = [];

        foreach ($cache as $row)
        {
            $result[$row['slug']] = $row;
        }

        Yii::$app->cache->set('product_filter_properties', $result, 3600);

        return $result;
    }

    private function getValueCache(): array
    {
        $cache = Yii::$app->cache->get('product_filter_values');

        if ($cache !== false)
        {
            return $cache;
        }

        $rows = ProductPropertyValue::find()
            ->select(['id','property_id','slug','sort'])
            ->asArray()
            ->all();

        $cache = [];

        foreach ($rows as $row)
        {
            $cache[$row['property_id']][$row['slug']] = $row;
        }

        Yii::$app->cache->set('product_filter_values', $cache, 3600);

        return $cache;
    }

    private function transliterate($st) {
        $st = strtr($st, array(
            'а' => 'a', 'б' => 'b', 'в' => 'v',
            'г' => 'g', 'д' => 'd', 'е' => 'e',
            'ё' => 'e', 'ж' => 'zh', 'з' => 'z',
            'и' => 'i', 'й' => 'y', 'к' => 'k',
            'л' => 'l', 'м' => 'm', 'н' => 'n',
            'о' => 'o', 'п' => 'p', 'р' => 'r',
            'с' => 's', 'т' => 't', 'у' => 'u',
            'ф' => 'f', 'х' => 'h', 'ц' => 'c',
            'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sch',
            'ь' => '\'', 'ы' => 'y', 'ъ' => '\'',
            'э' => 'e', 'ю' => 'yu', 'я' => 'ya',
            '%' => '',

            'А' => 'A', 'Б' => 'B', 'В' => 'V',
            'Г' => 'G', 'Д' => 'D', 'Е' => 'E',
            'Ё' => 'E', 'Ж' => 'Zh', 'З' => 'Z',
            'И' => 'I', 'Й' => 'Y', 'К' => 'K',
            'Л' => 'L', 'М' => 'M', 'Н' => 'N',
            'О' => 'O', 'П' => 'P', 'Р' => 'R',
            'С' => 'S', 'Т' => 'T', 'У' => 'U',
            'Ф' => 'F', 'Х' => 'H', 'Ц' => 'C',
            'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Sch',
            'Ь' => '\'', 'Ы' => 'Y', 'Ъ' => '\'',
            'Э' => 'E', 'Ю' => 'Yu', 'Я' => 'Ya',
        ));
        return $st;
    }

    private function saveCategory(Product $product, $uuid, $is_main = 0)
    {
        $mainCategory = Category::find()
            ->where(['uuid' => $uuid])
            ->one();
        if ($mainCategory) {
            $model = new ProductCategory([
                'category_uuid' => $mainCategory->uuid,
                'category_id'   => $mainCategory->id,
                'is_main'       => $is_main,
                'product_uuid'  => $product->uuid,
                'product_id'    => $product->id
            ]);
            $model->save();

            $mainCategory->delete = 0;

            if(in_array($mainCategory->uuid,$this->listHideUuidCategory))
            {
                $mainCategory->delete = 1;
            }

            $mainCategory->save();
            if ($mainCategory->parent_id) {
                $this->saveCategory($product, $mainCategory->parent_uuid, 0);
            }
        }
    }
}
