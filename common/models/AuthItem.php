<?php

namespace common\models;

use Yii;
use yii\base\InvalidConfigException;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\BaseInflector;
use yii\helpers\StringHelper;

/**
 * This is the model class for table "auth_item".
 *
 * @property string $name
 * @property int $type
 * @property string $description
 * @property string $rule_name
 * @property resource $data
 * @property int $created_at
 * @property int $updated_at
 * @property int $company_id
 * @property int $status
 * @property int $parent_id
 * @property int $default_role
 *
 * @property AuthAssignment[] $authAssignments
 * @property AuthRule $ruleName
 * @property AuthItemChild[] $authItemChildren
 * @property AuthItemChild[] $authItemChildren0
 * @property AuthItem[] $children
 * @property ActiveQuery|AuthItemParent $parentGroup
 * @property AuthItem[] $parents
 */
class AuthItem extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'auth_item';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'type'], 'required'],
            [['type', 'created_at', 'updated_at', 'company_id'], 'integer'],
            [['description', 'data'], 'string'],
            [['status', 'parent_id', 'default_role'], 'safe'],
            [['name', 'rule_name'], 'string', 'max' => 64],
            [['name'], 'unique'],
            [['rule_name'], 'exist', 'skipOnError' => true,
                'targetClass' => AuthRule::className(), 'targetAttribute' => ['rule_name' => 'name']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'name' => 'Name',
            'type' => 'Type',
            'description' => 'Description',
            'rule_name' => 'Rule Name',
            'data' => 'Data',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'company_id' => 'Company ID',
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getAuthAssignments()
    {
        return $this->hasMany(AuthAssignment::className(), ['item_name' => 'name']);
    }

    /**
     * @return ActiveQuery
     */
    public function getRuleName()
    {
        return $this->hasOne(AuthRule::className(), ['name' => 'rule_name']);
    }

    /**
     * @return ActiveQuery
     */
    public function getAuthItemChildren()
    {
        return $this->hasMany(AuthItemChild::className(), ['parent' => 'name']);
    }

    /**
     * @return ActiveQuery
     */
    public function getAuthItemChildren0()
    {
        return $this->hasMany(AuthItemChild::className(), ['child' => 'name']);
    }

    /**
     * @return ActiveQuery
     * @throws InvalidConfigException
     */
    public function getChildren()
    {
        return $this->hasMany(AuthItem::className(), ['name' => 'child'])
            ->viaTable('auth_item_child', ['parent' => 'name']);
    }

    /**
     * @return ActiveQuery
     * @throws InvalidConfigException
     */
    public function getParents()
    {
        return $this->hasMany(AuthItem::className(), ['name' => 'parent'])
            ->viaTable('auth_item_child', ['child' => 'name']);
    }

    /**
     * @return ActiveQuery | AuthItemParent
     */
    public function getParentGroup()
    {
        return $this->hasOne(AuthItemParent::class, ['id' => 'parent_id']);
    }

    public static function translate($name)
    {
        return BaseInflector::slug($name);
    }

    /**
     * @return ActiveQuery | AuthItemHistory
     */
    public function getHistory()
    {
        return $this->hasOne(AuthItemHistory::class, ['name' => 'name']);
    }
}
