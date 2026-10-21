<?php

namespace common\models;

use Exception;
use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "budget_location".
 *
 * @property int $id
 * @property int $budget_id
 * @property int $location_id
 */
class BudgetLocation extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'budget_location';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['budget_id', 'location_id'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'budget_id' => 'Budget ID',
            'location_id' => 'Location ID',
        ];
    }

    /**
     * @param $budgetId
     * @param $data
     */
    public static function addLocations($budgetId, $data)
    {
        $insert = [];
        if (!empty($data)) {
            foreach ($data as $value) {
                $insert[] = [
                    'budget_id' => $budgetId,
                    'location_id' => $value
                ];
            }
        }
        if (!empty($insert)) {
            try {
                Yii::$app->db->createCommand()->batchInsert(
                    self::tableName(),
                    ['budget_id', 'location_id'],
                    $insert
                )->execute();
            } catch (Exception $e) {
                Yii::error($e);
            }
        }
    }

    public function getLocation()
    {
        return $this->hasOne(Locations::class, ['id' => 'location_id']);
    }
}
