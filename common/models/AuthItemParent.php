<?php

namespace common\models;

use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "auth_item_parent".
 *
 * @property int $id
 * @property string $name
 * @property AuthItem|ActiveQuery $children
 * @property string $description
 */
class AuthItemParent extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'auth_item_parent';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'description'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'description' => 'Description',
        ];
    }

    /**
     * @return ActiveQuery | AuthItem
     */
    public function getChildren()
    {
        $permission = Yii::$app->authManager->getPermissionsByUser(Yii::$app->user->getId());
        $permission = ArrayHelper::getColumn($permission, 'name');
        return $this->hasMany(AuthItem::class, ['parent_id' => 'id'])->andWhere(['in', 'auth_item.name', $permission]);
    }

    /**
     * @param $name
     * @param $description
     * @return int
     */
    public static function addGroup($name, $description)
    {
        $model = new self();
        $model->name = $name;
        $model->description = $description;
        $model->save();
        return $model->id;
    }
}
