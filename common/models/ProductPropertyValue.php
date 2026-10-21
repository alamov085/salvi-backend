<?php
namespace common\models;

use yii\db\ActiveRecord;

class ProductPropertyValue extends ActiveRecord
{
    public static function tableName()
    {
        return 'product_filter_value';
    }

    public function rules()
    {
        return [

            [['property_id', 'name', 'sort','slug'], 'required'],

            [['property_id', 'sort'], 'integer'],

            [['name', 'slug'], 'string'],

        ];
    }

    public function attributeLabels()
    {
        return [

            'id' => 'ID',

            'property_id' => 'Свойтсво',

            'name' => 'Наименоваине товара',

            'sort' => 'Сортировка',

            'slug' => 'code',
        ];
    }

    public function getProperty()
    {
        return $this->hasOne(ProductProperty::class, ['id' => 'property_id']);
    }

    public function getProductFilters()
    {
        return $this->hasMany(ProductFilter::class, ['value_id' => 'id']);
    }
}