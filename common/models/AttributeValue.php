<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "attribute_value".
 *
 * @property int $id
 * @property string $name
 * @property int $attribute_id
 * @property string $uuid
 */
class AttributeValue extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'attribute_value';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['attribute_id'], 'integer'],
            [['name', 'uuid'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'attribute_id' => 'Attribute ID',
        ];
    }
}
