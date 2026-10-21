<?php

namespace common\models;

use Ramsey\Uuid\Uuid;
use Yii;

/**
 * This is the model class for table "unit".
 *
 * @property int $id
 * @property string $uuid
 * @property string $name
 */
class Unit extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'unit';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'string', 'max' => 255],
            ['uuid', 'safe']
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

//    public function save($runValidation = true, $attributeNames = null)
//    {
//        if ($this->isNewRecord) {
//            $this->uuid = Uuid::uuid1()->toString();
//        }
//        return parent::save($runValidation, $attributeNames);
//    }
}
