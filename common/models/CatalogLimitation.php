<?php

namespace common\models;

use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "catalog_limitation".
 *
 * @property int $id
 * @property int $company_id
 * @property int $category_id
 * @property string $category_uuid
 * @property string $markup
 * @property int $for_show
 * @property int $total_product
 * @property ActiveQuery|Category $category
 * @property int $specification
 * @property int $location_id
 */
class CatalogLimitation extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'catalog_limitation';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['company_id', 'category_id', 'for_show', 'total_product', 'specification', 'location_id'], 'integer'],
            [['markup'], 'number'],
            [['category_uuid'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'company_id' => 'Company ID',
            'category_id' => 'Category ID',
            'category_uuid' => 'Category Uuid',
            'markup' => 'Markup',
        ];
    }

    /**
     * @return ActiveQuery | Category
     */
    public function getCategory()
    {
        return $this->hasOne(Category::class, ['uuid' => $this->category_uuid]);
    }
}
