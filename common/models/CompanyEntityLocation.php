<?php

namespace common\models;

use Exception;
use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "company_entity_location".
 *
 * @property int $id
 * @property int $company_id
 * @property ActiveQuery|CompanyEntity $company
 * @property int $location_id
 */
class CompanyEntityLocation extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'company_entity_location';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'company_id', 'location_id'], 'integer'],
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
            'location_id' => 'Location ID',
        ];
    }

    /**
     * @return ActiveQuery | CompanyEntity
     */
    public function getCompany()
    {
        return $this->hasOne(CompanyEntity::class, ['id' => 'company_id']);
    }

    public static function addLocations($id, $data)
    {
        $insert = [];
        if (!empty($data)) {
            foreach ($data as $value) {
                $insert[] = [
                    'location_id' => $id,
                    'company_id' => $value
                ];
            }
        }
        if (!empty($insert)) {
            try {
                Yii::$app->db->createCommand()->batchInsert(
                    self::tableName(),
                    ['location_id', 'company_id'],
                    $insert
                )->execute();
            } catch (Exception $e) {
                Yii::error($e);
            }
        }
    }
}
