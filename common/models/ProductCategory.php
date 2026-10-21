<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "product_category".
 *
 * @property int $id
 * @property int $category_id
 * @property int $product_id
 * @property int $is_main
 * @property string $category_uuid
 * @property string $product_uuid
 */
class ProductCategory extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'product_category';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['category_id', 'product_id', 'is_main'], 'integer'],
            [['category_uuid', 'product_uuid'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'category_id' => 'Category ID',
            'product_id' => 'Product ID',
            'category_uuid' => 'Category Uuid',
            'product_uuid' => 'Product Uuid',
        ];
    }
}
