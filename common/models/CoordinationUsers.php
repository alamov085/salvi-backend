<?php

namespace common\models;

use Exception;
use Yii;

/**
 * This is the model class for table "coordination_users".
 *
 * @property int $id
 * @property int $coordination_id
 * @property int $user_id
 */
class CoordinationUsers extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'coordination_users';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['coordination_id', 'user_id'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'coordination_id' => 'Coordination ID',
            'user_id' => 'User ID',
        ];
    }

    public static function addUsers($id, $data)
    {
        $insert = [];
        if (!empty($data)) {
            foreach ($data as $value) {
                $insert[] = [
                    'coordination_id' => $id,
                    'user_id' => $value
                ];
            }
        }
        if (!empty($insert)) {
            try {
                Yii::$app->db->createCommand()->batchInsert(
                    self::tableName(),
                    ['coordination_id', 'user_id'],
                    $insert
                )->execute();
            } catch (Exception $e) {
                Yii::error($e);
            }
        }
    }
}
