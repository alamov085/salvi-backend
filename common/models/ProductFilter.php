<?php
namespace common\models;

use yii\db\ActiveRecord;

class ProductFilter extends ActiveRecord
{
    public static function tableName()
    {
        return 'product_filter';
    }

    public function rules()
    {
        return [

            [['product_id', 'filter_name', 'filter_value','filter_code','filter_value_code'], 'required'],

            [['product_id'], 'integer'],

            [['filter_name', 'filter_value','filter_code','filter_value_code'], 'string', 'max' => 255],

        ];
    }

    public function attributeLabels()
    {
        return [

            'id' => 'ID',

            'product_id' => 'Товар',

            'filter_name' => 'Название фильтра',

            'filter_code' => 'Код фильтра',

            'filter_value_code' => 'Код значения фильтра',

            'filter_value' => 'Значение фильтра',

        ];
    }

    public function getProduct()
    {
        return $this->hasOne(
            Product::class,
            ['id' => 'product_id']
        );
    }
}