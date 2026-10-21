<?php

namespace common\models;

use Exception;
use Yii;

/**
 * This is the model class for table "company_supplier".
 *
 * @property int $id
 * @property int $company_id
 * @property int $supplier_id
 */
class CompanySupplier extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'company_supplier';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['company_id', 'supplier_id'], 'integer'],
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
            'supplier_id' => 'Supplier ID',
        ];
    }

    /**
     * @param $id
     * @param $data
     */
    public static function addCompanies($id, $data)
    {
        $insert = [];
        if (!empty($data)) {
            foreach ($data as $value) {
                $insert[] = [
                    'supplier_id' => $id,
                    'company_id' => $value
                ];
            }
        }
        if (!empty($insert)) {
            try {
                Yii::$app->db->createCommand()->batchInsert(
                    self::tableName(),
                    ['supplier_id', 'company_id'],
                    $insert
                )->execute();
            } catch (Exception $e) {
                Yii::error($e);
            }
        }
    }
}
