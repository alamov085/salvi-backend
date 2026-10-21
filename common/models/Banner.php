<?php

namespace common\models;

use common\models\query\BudgetAQ;
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "budget".
 *
 * @property int $id
 * @property int $active
 * @property string $name
 * @property string $img_path
 * @property int $url
 * @property int $text
 * @property int $time_swap
 * @property int $user_create
 * @property int $user_update
 * @property int $created_at
 * @property int $updated_at
 */
class Banner extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'banners';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'active', 'name', 'img_path', 'url', 'text', 'time_swap', 'user_create', 'user_update', 'created_at', 'updated_at','sort'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'active' => 'Active',
            'name' => 'Name',
            'img_path' => 'img Path',
            'url' => 'url',
            'text' => 'text',
            'time_swap' => 'time Swap',
            'user_create' => 'user Create',
            'user_update' => 'user Update',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @param bool $runValidation
     * @param null $attributeNames
     * @return bool
     */
    public function save($runValidation = true, $attributeNames = null)
    {
        if ($this->isNewRecord) {
            $this->created_at = date('U');
        }
        $this->updated_at = date('U');
        return parent::save($runValidation, $attributeNames);
    }

}
