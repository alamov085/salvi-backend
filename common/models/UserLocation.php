<?php

namespace common\models;

use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "user_location".
 *
 * @property int $id
 * @property int $user_id
 * @property ActiveQuery|CompanyEntityLocation $companyEntityLocation
 * @property int $location_id
 */
class UserLocation extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_location';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'location_id'], 'integer'],
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
            'location_id' => 'Location ID',
        ];
    }

    /**
     * @return ActiveQuery | CompanyEntityLocation
     */
    public function getCompanyEntityLocation()
    {
        return $this->hasMany(CompanyEntityLocation::class, ['location_id' => 'location_id']);
    }
}
