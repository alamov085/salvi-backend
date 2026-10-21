<?php

namespace common\models;

use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "supplier".
 *
 * @property int $id
 * @property string $name
 * @property string $edrpou
 * @property string $inn
 * @property string $description
 * @property int $status
 * @property int $created_at
 * @property ActiveQuery|CompanySupplier $companySupplier
 * @property History|ActiveQuery $history
 * @property int $updated_at
 */
class Supplier extends ActiveRecord
{
    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'supplier';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['description'], 'string'],
            [['status', 'created_at', 'updated_at'], 'integer'],
            [['name', 'edrpou', 'inn'], 'string', 'max' => 255],
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
            'edrpou' => 'Edrpou',
            'inn' => 'Inn',
            'description' => 'Description',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => 'yii\behaviors\TimestampBehavior',
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at', 'updated_at'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
                ],
            ],
        ];
    }

    /**
     * @return ActiveQuery | CompanySupplier
     */
    public function getCompanySupplier()
    {
        return $this->hasMany(CompanySupplier::class, ['supplier_id' => 'id']);
    }

    public function afterSave($insert, $changedAttributes)
    {
        if ($insert) {
            History::saveHistory(History::SUPPLIER, $this->id);
        }
        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * @return ActiveQuery | History
     */
    public function getHistory()
    {
        return $this->hasOne(History::class, ['entity_id' => 'id'])
            ->andOnCondition(['entity' => History::SUPPLIER]);
    }
}
