<?php
namespace common\models;

use yii\db\ActiveRecord;

class ProductFilters extends ActiveRecord
{
    public static function tableName()
    {
        return 'product_filters';
    }

    public function rules()
    {
        return [

            [['product_id', 'property_id', 'value_id'], 'required'],

            [['product_id', 'property_id', 'value_id'], 'integer'],

        ];
    }

    public function attributeLabels()
    {
        return [

            'id' => 'ID',

            'product_id' => 'Товар',

            'property_id' => 'Свойство',

            'value_id' => 'Значение',
        ];
    }

    public static function primaryKey()
    {
        return ['product_id', 'property_id', 'value_id'];
    }

    public function getProduct()
    {
        return $this->hasOne(Product::class, ['id' => 'product_id']);
    }

    public function getProperty()
    {
        return $this->hasOne(ProductProperty::class, ['id' => 'property_id']);
    }

    public function getValue()
    {
        return $this->hasOne(ProductPropertyValue::class, ['id' => 'value_id']);
    }
}