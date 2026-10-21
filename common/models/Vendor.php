<?php

namespace common\models;

use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "vendor".
 *
 * @property int $id
 * @property string $uuid
 * @property ActiveQuery|ProductBrand $brands
 * @property string $name
 */
class Vendor extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'vendor';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['uuid', 'name'], 'required'],
            [['uuid', 'name'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'uuid' => 'Uuid',
            'name' => 'Name',
        ];
    }

    /**
     * @return ActiveQuery | ProductBrand
     */
    public function getBrands()
    {
        return $this->hasMany(ProductBrand::class, ['vendor_uuid' => 'uuid']);
    }
}
