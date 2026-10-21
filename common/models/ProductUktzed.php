<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "product_uktzed".
 *
 * @property int $id
 * @property string $full_name
 * @property string $short_name
 * @property string $uuid
 * @property int $type
 */
class ProductUktzed extends ActiveRecord
{
    const TYPE_IMPORT = 0;
    const TYPE_PRODUCT = 1;
    const TYPE_SERVICE = 2;
    const TYPE_WORK = 3;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'product_uktzed';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['type'], 'integer'],
            [['full_name', 'short_name', 'uuid'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'full_name' => 'Full Name',
            'short_name' => 'Short Name',
            'uuid' => 'Uuid',
            'type' => 'Type',
        ];
    }

    public static function types()
    {
        return [
            self::TYPE_IMPORT => [
                'id' => self::TYPE_IMPORT,
                'name' => 'Импортированный товар'
            ],
            self::TYPE_PRODUCT => [
                'id' => self::TYPE_PRODUCT,
                'name' => 'Товар'
            ],
            self::TYPE_SERVICE => [
                'id' => self::TYPE_SERVICE,
                'name' => 'Услуга'
            ],
            self::TYPE_WORK => [
                'id' => self::TYPE_WORK,
                'name' => 'Работа'
            ],
        ];
    }
}
