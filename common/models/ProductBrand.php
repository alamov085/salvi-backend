<?php

namespace common\models;

use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "product_brand".
 *
 * @property int $id
 * @property string $uuid
 * @property string $name
 * @property Vendor|ActiveQuery $vendor
 * @property string $vendor_uuid
 */

/**
 * @deprecated
 */
class ProductBrand extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'product_brand';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['uuid', 'name', 'vendor_uuid'], 'required'],
            [['uuid', 'name', 'vendor_uuid'], 'string', 'max' => 255],
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
            'vendor_uuid' => 'Vendor uuid',
        ];
    }

    /**
     * @return ActiveQuery | Vendor
     */
    public function getVendor()
    {
        return $this->hasOne(Vendor::class, ['uuid' => 'vendor_uuid']);
    }
}
