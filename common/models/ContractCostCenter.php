<?php

namespace common\models;

use Exception;
use Yii;

/**
 * This is the model class for table "contract_cost_center".
 *
 * @property int $id
 * @property int $contract_id
 * @property int $cost_center_id
 */
class ContractCostCenter extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'contract_cost_center';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['contract_id', 'cost_center_id'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'contract_id' => 'Contract ID',
            'cost_center_id' => 'Cost Center ID',
        ];
    }

    /**
     * @param $id
     * @param $data
     */
    public static function addCost($id, $data)
    {
        $insert = [];
        if (!empty($data)) {
            foreach ($data as $value) {
                $insert[] = [
                    'contract_id' => $id,
                    'cost_center_id' => $value
                ];
            }
        }
        if (!empty($insert)) {
            try {
                Yii::$app->db->createCommand()->batchInsert(
                    self::tableName(),
                    ['contract_id', 'cost_center_id'],
                    $insert
                )->execute();
            } catch (Exception $e) {
                Yii::error($e);
            }
        }
    }
}
