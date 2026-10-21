<?php

namespace common\models;

use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "product".
 *
 * @property int $id
 * @property string $uuid
 * @property string $sku
 * @property string $uktzed
 * @property string $buy_price
 * @property string $name
 * @property string $description
 * @property string $unit_uuid
 * @property ActiveQuery|Unit $unit
 * @property ActiveQuery|Category $categories
 * @property string $price
 * @property string $vat
 * @property int $unit_id
 * @property ActiveQuery|ProductPhoto $image
 * @property ActiveQuery|SpecificationProduct $specificationProduct
 * @property string $priceCatalog
 * @property ActiveQuery|SpecificationProduct $specificationsProduct
 * @property int $vendor_id
 * @property ActiveQuery|ProductUktzed $productUktzed
 * @property int $deleted
 * @property ActiveQuery|ProductBrand $productBrand
 * @property Vendor|ActiveQuery $vendor
 * @property ActiveQuery|ProductAttribute $productAttribute
 * @property int $brand_id
 * @property integer $is_buffer
 * @property ActiveQuery|ProductPhoto $images
 * @property ActiveQuery|Category $categoriesNew
 * @property float|int $vatPercent
 * @property bool $isSpecificationProduct
 * @property integer $buffer_status
 * @property integer $updated_at
 * @property integer $sort;
 * @property integer $synonym;
 * @property integer $multiplicity;
 * @property integer $strict_multiplicity;
 */
class Product extends ActiveRecord
{
    const BUFFER_EDIT = 0;
    const BUFFER_WAITING = 1;
    const BUFFER_APPROVED = 2;

    const STATUS_ACTIVE = 0;
    const STATUS_NOT_AVAILABLE = 1;
    const STATUS_HIDDEN = 2;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'product';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['buy_price'], 'number'],
            [['description', 'synonym'], 'string'],
            [['unit_id', 'sort'], 'integer'],
            [['uuid', 'sku', 'uktzed', 'name', 'unit_uuid'], 'string', 'max' => 255],
            [['vendor_id', 'vat', 'deleted', 'brand_id'], 'safe'],
            [['is_buffer', 'buffer_status', 'updated_at'], 'integer']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'          => 'ID',
            'uuid'        => 'Uuid',
            'sku'         => 'Sku',
            'uktzed'      => 'Uktzed',
            'buy_price'   => 'Buy Price',
            'name'        => 'Name',
            'description' => 'Description',
            'unit_uuid'   => 'Unit Uuid',
            'unit_id'     => 'Unit ID',
            'deleted'     => 'Is deleted',
        ];
    }

    /**
     * @return ActiveQuery | Unit
     */
    public function getUnit()
    {
        return $this->hasOne(Unit::class, ['id' => 'unit_id']);
    }

    /**
     * @return ActiveQuery | Category
     * @throws InvalidConfigException
     */
    public function getCategories()
    {
        return $this->hasMany(Category::class, ['id' => 'category_id'])
            ->viaTable(ProductCategory::tableName(), ['product_id' => 'id'], function (ActiveQuery $query) {
                $query->andOnCondition([
                    ProductCategory::tableName() . '.is_main' => 1
                ]);
            });
    }
    

    /**
     * @return ActiveQuery | Category
     * @throws InvalidConfigException
     */
    public function getCategoriesNew()
    {
        return $this->hasMany(Category::class, ['id' => 'category_id'])
            ->viaTable(ProductCategory::tableName(), ['product_id' => 'id']);
    }

    /**
     * Бренд товара
     */
    public function getBrand()
    {
        return $this->hasOne(
            Brand::class,
            ['id' => 'brand_id']
        );
    }

    /**
     * Все фильтры товара если в одной таблице
     */
    public function getFilters()
    {
        return $this->hasMany(
            ProductFilter::class,
            ['product_id' => 'id']
        );
    }

    /**
     * Все фильтры товара если в 3 таблицах
     */
    public function getProductFilters()
    {
        return $this->hasMany(ProductFilters::class, ['product_id' => 'id']);
    }

    public function getFilterProperties()
    {
        return $this->hasMany(ProductProperty::class, ['id' => 'property_id'])
            ->via('productFilters');
    }

    public function getFilterValues()
    {
        return $this->hasMany(ProductPropertyValue::class, ['id' => 'value_id'])
            ->via('productFilters');
    }

    /**
     * @return string
     * @throws Throwable
     */
    public function getPrice()
    {
        /** @var  $user User */
        $user = Yii::$app->user->getIdentity();
        $categoryId = ArrayHelper::getValue($this->categories, '0.uuid');
        $price = CatalogLimitation::find()
            ->where(['category_uuid' => $categoryId])
            ->andWhere(['company_id' => $user->company_id])
            ->one();
        if ($price) {
            $priceWithMarkup = ((100 + $price->markup) / 100) * $this->buy_price;
            return number_format($priceWithMarkup, 2, '.', '');
        }
        $category = Category::find()
            ->where(['uuid' => $categoryId])
            ->andWhere(['not', ['markup' => null]])
            ->one();
        if ($category) {
            $priceWithMarkup = ((100 + $category->markup) / 100) * $this->buy_price;
            return number_format($priceWithMarkup, 2, '.', '');
        }
        $basicMarkup = Config::getValue(Config::MARKUP);
        if ($basicMarkup) {
            $priceWithMarkup = ((100 + $basicMarkup) / 100) * $this->buy_price;
            return number_format($priceWithMarkup, 2, '.', '');
        }
        return $this->buy_price;
    }

    public function getPriceCatalog($specifications = [])
    {
        /** @var  $user User */
        $user = Yii::$app->user->getIdentity();
        $categoryId = ArrayHelper::getValue($this->categories, '0.uuid');
        $cacheKey = 'price_company_' . $user->company_id . '_id_' . $categoryId;
        $cacheDuration = 1;
        $cache = Yii::$app->cache;
        if ($cache->exists($cacheKey)) {
            $price = $cache->get($cacheKey);
        } else {
            $price = CatalogLimitation::find()
                ->where(['category_uuid' => $categoryId])
                ->andWhere(['company_id' => $user->company_id])
                ->one();
            $cache->set($cacheKey, $price, $cacheDuration);
        }
        if ($price) {
            $priceWithMarkup = ((100 + $price->markup) / 100) * $this->buy_price;
            $resultPrice = number_format($priceWithMarkup, 2, '.', '');
        } else {
            $cacheKeyCategory = 'category_markup_null_' . $categoryId;
            if ($cache->exists($cacheKeyCategory)) {
                $category = $cache->get($cacheKeyCategory);
            } else {
                $category = Category::find()
                    ->where(['uuid' => $categoryId])
                    ->andWhere(['not', ['markup' => null]])
                    ->one();
                $cache->set($cacheKeyCategory, $category, $cacheDuration);
            }
            if ($category) {
                $priceWithMarkup = ((100 + $category->markup) / 100) * $this->buy_price;
                $resultPrice = number_format($priceWithMarkup, 2, '.', '');
            } else {
                $basicMarkup = Config::getValue(Config::MARKUP);
                if ($basicMarkup) {
                    $priceWithMarkup = ((100 + $basicMarkup) / 100) * $this->buy_price;
                    $resultPrice = number_format($priceWithMarkup, 2, '.', '');
                } else {
                    $resultPrice = $this->buy_price;
                }
            }
        }
        

        if ($this->specificationsProduct)
        {
	        $specificationsListNeed = [];
	        
        	if(!empty($specifications))
	        {
		        $specificationsIds = ArrayHelper::getColumn($specifications, 'id');
		        
		        foreach ($this->specificationsProduct as $item)
		        {
		        	if(in_array($item->specification_id,$specificationsIds))
			        {
				        $specificationsListNeed[] = $item;
			        }
		        }
	        }
	        else
	        {
		        $specificationsListNeed = $this->specificationsProduct;
	        }
	        
	        $status = ArrayHelper::getColumn($specificationsListNeed, 'status');
	
	        $key = array_search(1, $status);
	
	        if (false !== $key)
	        {
		        $prodPrice = ($specificationsListNeed[$key]->price > 0) ? $specificationsListNeed[$key]->price : $resultPrice;
		
		        $resultPrice = ($specificationsListNeed[$key]->use_markup) ? round($this->buy_price * ((100 + $specificationsListNeed[$key]->markup) / 100),2) : $prodPrice;
	        }
        }
        return $resultPrice;
    }

    /**
     * @return ActiveQuery | ProductPhoto
     */
    public function getImage()
    {
        return $this->hasOne(ProductPhoto::class, ['product_id' => 'id']);
    }

    /**
     * @return ActiveQuery | SpecificationProduct
     */
    public function getSpecificationProduct()
    {
        return $this->hasOne(SpecificationProduct::class, ['product_id' => 'id']);
    }

    /**
     * @return ActiveQuery | SpecificationProduct
     */
    public function getSpecificationsProduct()
    {
        return $this->hasMany(SpecificationProduct::class, ['product_id' => 'id']);
    }

    /**
     * @return ActiveQuery | ProductUktzed
     */
    public function getProductUktzed()
    {
        return $this->hasOne(ProductUktzed::class, ['id' => 'uktzed']);
    }

    /**
     * @return ActiveQuery | ProductBrand
     */
    public function getProductBrand()
    {
        return $this->hasOne(ProductBrand::class, ['id' => 'brand_id']);
    }

    /**
     * @return ActiveQuery | Vendor
     */
    public function getVendor()
    {
        return $this->hasOne(Vendor::class, ['id' => 'vendor_id']);
    }

    /**
     * @return ActiveQuery | ProductAttribute
     */
    public function getProductAttribute()
    {
        return $this->hasMany(ProductAttribute::class, ['product_uuid' => 'uuid']);
    }

    /**
     * @return ActiveQuery | ProductPhoto
     */
    public function getImages()
    {
        return $this->hasMany(ProductPhoto::class, ['product_id' => 'id']);
    }

    /** Ставки НДС
     * @return array
     */
    public static function vat()
    {
        return [
            '20%',
            '7%',
            'Без ПДВ',
            'Не ПДВ'
        ];
    }

    /**
     * @return float|int
     */
    public function getVatPercent()
    {
        $cof = 0;
        $percent = explode('%', $this->vat);
        $percent = ArrayHelper::getValue($percent, 0, 'number');
        if (is_numeric($percent) && $percent > 0) {
            $cof = $percent / 100;
        }
        return $cof;
    }

    public function getIsSpecificationProduct()
    {
        if ($this->specificationsProduct) {
            $status = ArrayHelper::getColumn($this->specificationsProduct, 'status');
            $key = array_search(1, $status);
            if (false !== $key) {
                return true;
            }
        }
        return false;
    }
}
