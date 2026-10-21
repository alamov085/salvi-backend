<?php
namespace common\models;

use yii\db\ActiveRecord;

class ProductProperty extends ActiveRecord
{
    public static function tableName()
    {
        return 'product_filter_property';
    }

    public function rules()
    {
        return [

            [[ 'name', 'sort','slug'], 'required'],

            [['sort'], 'integer'],

            [['name', 'slug'], 'string'],

        ];
    }

    public function attributeLabels()
    {
        return [

            'id' => 'ID',

            'name' => 'Наименоваине товара',

            'sort' => 'Сортировка',

            'slug' => 'code',
        ];
    }

    public function getValues()
    {
        return $this->hasMany(ProductPropertyValue::class, ['property_id' => 'id']);
    }

    public function getProductFilters()
    {
        return $this->hasMany(ProductFilter::class, ['property_id' => 'id']);
    }
}