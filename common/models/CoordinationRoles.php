<?php

namespace common\models;

use Exception;
use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "coordination_roles".
 *
 * @property int $id
 * @property int $coordination_id
 * @property AuthItem|ActiveQuery $authItem
 * @property string $role
 */
class CoordinationRoles extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'coordination_roles';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['coordination_id'], 'integer'],
            [['role'], 'string', 'max' => 255],
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
            'role' => 'Role',
        ];
    }

    public static function addRoles($id, $data)
    {
        $insert = [];
        if (!empty($data)) {
            foreach ($data as $value) {
                $insert[] = [
                    'coordination_id' => $id,
                    'role' => $value
                ];
            }
        }
        if (!empty($insert)) {
            try {
                Yii::$app->db->createCommand()->batchInsert(
                    self::tableName(),
                    ['coordination_id', 'role'],
                    $insert
                )->execute();
            } catch (Exception $e) {
                Yii::error($e);
            }
        }
    }

    /**
     * @return ActiveQuery | AuthItem
     */
    public function getAuthItem()
    {
        return $this->hasOne(AuthItem::class, ['name' => 'role']);
    }
}
