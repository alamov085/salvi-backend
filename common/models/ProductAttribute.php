<?php

namespace common\models;

use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "product_attribute".
 *
 * @property int $id
 * @property int $attribute_id
 * @property int $value_id
 * @property AttributeValue|ActiveQuery $value
 * @property Attribute|ActiveQuery $attr
 * @property string $product_uuid
 */
class ProductAttribute extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'product_attribute';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['attribute_id', 'value_id'], 'integer'],
            [['product_uuid'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'attribute_id' => 'Attribute ID',
            'value_id' => 'Value ID',
            'product_uuid' => 'Product Uuid',
        ];
    }

    /**
     * @return ActiveQuery | AttributeValue
     */
    public function getValue()
    {
        return $this->hasOne(AttributeValue::class, ['id' => 'value_id']);
    }

    /**
     * @return ActiveQuery | Attribute
     */
    public function getAttr()
    {
        return $this->hasOne(Attribute::class, ['id' => 'attribute_id']);
    }
}
