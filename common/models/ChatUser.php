<?php

namespace common\models;

use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "chat_user".
 *
 * @property int $id
 * @property int $chat_id
 * @property int $user_id
 * @property ActiveQuery|User $user
 * @property ChatGuestUser|ActiveQuery $guest
 * @property int $is_guest
 */
class ChatUser extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'chat_user';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['chat_id', 'user_id', 'is_guest'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'chat_id' => 'Chat ID',
            'user_id' => 'User ID',
            'is_guest' => 'Is Guest',
        ];
    }

    /**
     * @return ActiveQuery | User
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id'])->andWhere(['is_guest' => 0]);
    }

    /**
     * @return ActiveQuery | ChatGuestUser
     */
    public function getGuest()
    {
        return $this->hasOne(ChatGuestUser::class, ['id' => 'user_id'])->andWhere(['is_guest' => 1]);
    }
}
