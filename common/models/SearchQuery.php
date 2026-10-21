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
 */
class SearchQuery extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'search_queries';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['query_string', 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'          => 'ID',
            'query_string'  => 'Query string',
            'created_at'     => 'Created at',
        ];
    }
}
