<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\db\Exception;

/**
 * This is the model class for table "budget_cost_center".
 *
 * @property int $id
 * @property int $budget_id
 * @property int $cost_center_id
 */
class BudgetCostCenter extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'budget_cost_center';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['budget_id', 'cost_center_id'], 'integer'],
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
            'cost_center_id' => 'Cost Center ID',
        ];
    }

    /**
     * @param $budgetId
     * @param $data
     */
    public static function addCost($budgetId, $data)
    {
        $insert = [];
        if (!empty($data)) {
            foreach ($data as $value) {
                $insert[] = [
                    'budget_id' => $budgetId,
                    'cost_center_id' => $value
                ];
            }
        }
        if (!empty($insert)) {
            try {
                Yii::$app->db->createCommand()->batchInsert(
                    self::tableName(),
                    ['budget_id', 'cost_center_id'],
                    $insert
                )->execute();
            } catch (\Exception $e) {
                Yii::error($e);
            }
        }
    }
}
