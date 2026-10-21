<?php

namespace common\models;

use Exception;
use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "specification_location".
 *
 * @property int $id
 * @property int $specification_id
 * @property ActiveQuery|Locations $currentLocation
 * @property int $location_id
 */
class SpecificationLocation extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'specification_location';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['specification_id', 'location_id'], 'integer'],
        ];
    }

    /**
     * @return ActiveQuery | Locations
     */
    public function getCurrentLocation()
    {
        return $this->hasOne(Locations::class, ['id' => 'location_id']);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'specification_id' => 'Specification ID',
            'location_id' => 'Location ID',
        ];
    }

    public static function addLocations($id, $data)
    {
        $insert = [];
        if (!empty($data)) {
            foreach ($data as $value) {
                $insert[] = [
                    'specification_id' => $id,
                    'location_id' => $value
                ];
            }
        }
        if (!empty($insert)) {
            try {
                Yii::$app->db->createCommand()->batchInsert(
                    self::tableName(),
                    ['specification_id', 'location_id'],
                    $insert
                )->execute();
            } catch (Exception $e) {
                Yii::error($e);
            }
        }
    }
}
