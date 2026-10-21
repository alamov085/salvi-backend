<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "user_cost_center".
 *
 * @property int $id
 * @property int $user_id
 * @property int $cost_center_id
 */
class UserCostCenter extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_cost_center';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'cost_center_id'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'cost_center_id' => 'Cost Center ID',
        ];
    }
}
