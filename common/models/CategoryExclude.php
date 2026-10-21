<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "category_exclude".
 *
 * @property int $id
 * @property int $company_id
 * @property string $category_uuid
 * @property int $category_id
 */
class CategoryExclude extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'category_exclude';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['company_id', 'category_id'], 'integer'],
            [['category_uuid'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'company_id' => 'Company ID',
            'category_uuid' => 'Category Uuid',
            'category_id' => 'Category ID',
        ];
    }
}
