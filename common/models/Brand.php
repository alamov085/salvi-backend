<?php

namespace common\models;

use yii\db\ActiveRecord;

class Brand extends ActiveRecord
{
    public static function tableName()
    {
        return 'brand';
    }

    public function rules()
    {
        return [

            [['uuid', 'name','code'], 'required'],

            [['uuid'], 'unique'],

            [['vendor_uuid','code'], 'string'],

            [['created_at', 'updated_at'], 'integer'],

            [['name'], 'string', 'max' => 255],

        ];
    }

    public function attributeLabels()
    {
        return [

            'id' => 'ID',

            'uuid' => 'UUID',

            'name' => 'Название',

            'code' => 'Код',

            'vendor_uuid' => 'UUID производителя',

        ];
    }

    public function getProducts()
    {
        return $this->hasMany(
            Product::class,
            ['brand_id' => 'id']
        );
    }

    public function getVendor()
    {
        return $this->hasOne(Vendor::class, ['uuid' => 'vendor_uuid']);
    }
}