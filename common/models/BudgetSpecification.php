<?php

namespace common\models;

use Exception;
use Yii;

/**
 * This is the model class for table "budget_specification".
 *
 * @property int $id
 * @property int $budget_id
 * @property int $specification_id
 */
class BudgetSpecification extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'budget_specification';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['budget_id', 'specification_id'], 'integer'],
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
            'specification_id' => 'Specification ID',
        ];
    }

    public static function addSpecification($budgetId, $data)
    {
        $insert = [];
        if (!empty($data)) {
            foreach ($data as $value) {
                $insert[] = [
                    'budget_id' => $budgetId,
                    'specification_id' => $value
                ];
            }
        }
        if (!empty($insert)) {
            try {
                Yii::$app->db->createCommand()->batchInsert(
                    self::tableName(),
                    ['budget_id', 'specification_id'],
                    $insert
                )->execute();
            } catch (Exception $e) {
                Yii::error($e);
            }
        }
    }
}
