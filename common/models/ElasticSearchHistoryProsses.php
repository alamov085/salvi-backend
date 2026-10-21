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
 * @property string $query
 * @property int $user_create
 * @property int $user_update
 * @property int $created_at
 * @property int $updated_at
 */
class ElasticSearchHistoryProsses extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'elastic_search_history_prosses';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'query', 'type','user_create', 'user_update', 'created_at', 'updated_at'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'query' => 'query',
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
