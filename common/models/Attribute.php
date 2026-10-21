<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "attribute".
 *
 * @property int $id
 * @property string $name
 * @property string $category_uuid
 * @property string $attribute
 * @property mixed $values
 * @property integer $category_id
 */
class Attribute extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'attribute';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'category_uuid'], 'string', 'max' => 255],
            ['category_id', 'integer']
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
            'category_uuid' => 'Category Uuid',
        ];
    }

    public function getValues()
    {
        return $this->hasMany(AttributeValue::class, ['attribute_id' => 'id']);
    }
}
