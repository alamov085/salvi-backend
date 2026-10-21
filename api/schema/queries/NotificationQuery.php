<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 27.08.2019
 * Time: 10:56
 */

namespace api\schema\queries;

use api\schema\PaginationType;
use api\schema\Types;
use common\models\Chat;
use common\models\ChatMessageRead;
use common\models\ChatUser;
use common\models\Notification;
use GraphQL\Type\Definition\Type;
use yii\db\Query;
use yii\helpers\ArrayHelper;

/**
 * Class NotificationQuery
 * @package api\schema\queries
 */
class NotificationQuery extends BasicQuery
{

    public function type()
    {
        return Types::notificationPagination();
    }

    public function args()
    {
        return ArrayHelper::merge(PaginationType::argsDescription(), [
            'id' => Type::int(),
            'chat' => Type::boolean()
        ]);
    }

    public function resolve()
    {
        return function ($root, $args) {
            $chatArray = [];
            $notificationArray = [];
            $notificationTotal = 0;
            $messageTotal = 0;
            if (!isset($args['chat']) || (isset($args['chat']) && $args['chat'])) {
                $arguments = PaginationType::arguments(
                    $args,
                    array_keys(Chat::getTableSchema()->columns)
                );
                $usersChats = ChatUser::find()
                    ->where(['user_id' => \Yii::$app->user->getId()])
                    ->groupBy('chat_id')
                    ->all();
                if ($usersChats) {
                    $usersChats = ArrayHelper::getColumn($usersChats, 'chat_id');
                } else {
                    $usersChats = [];
                }
                $chatQuery = Chat::find()
                    ->where(['chat.status' => Chat::STATUS_OPEN])
                    ->andWhere(['in', 'chat.id', $usersChats])
                    ->joinWith(['user', 'guestUser']);
                $messageTotal = $chatQuery->count();
                $chat = $chatQuery->offset($arguments['perPage'] * $arguments['page'])
                    ->limit($arguments['perPage'])
                    ->all();
                if ($chat) {
                    foreach ($chat as $item) {
                        $is_read = ChatMessageRead::find()
                            ->where(['chat_id' => $item->id])
                            ->andWhere(['user_id' => \Yii::$app->user->getId()])
                            ->andWhere(['is_read' => 0])
                            ->count();
                        $chatArray[] = [
                            'id' => $item->id,
                            'type' => 1,
                            'user' => ($item->is_guest) ?
                                $item->guestUser->name :
                                $item->user->first_name . ' ' . $item->user->last_name,
                            'date' => $item->created_at,
                            'text' => '',
                            'is_read' => !(bool)$is_read
                        ];
                    }
                }
            }
            if (!isset($args['chat']) || (isset($args['chat']) && !$args['chat'])) {
                $arguments = PaginationType::arguments(
                    $args,
                    array_keys(Notification::getTableSchema()->columns)
                );
                $notificationQuery = Notification::find()
                    ->where(['notification.user_id' => \Yii::$app->user->getId()])
                    ->andWhere(['notification.status' => Notification::STATUS_SHOW])
                    ->joinWith(['user']);
                $notificationTotal = $notificationQuery->count();
                $notification = $notificationQuery->offset($arguments['perPage'] * $arguments['page'])
                    ->limit($arguments['perPage'])
                    ->orderBy('id DESC')
                    ->all();
                if ($notification) {
                    foreach ($notification as $item) {
                        $notificationArray[] = [
                            'id' => $item->id,
                            'type' => 0,
                            'user' => $item->user->first_name . ' ' . $item->user->last_name,
                            'date' => $item->created_at,
                            'text' => $item->message,
                            'is_read' => $item->is_read
                        ];
                    }
                }
            }
            if (!empty($chatArray) || !empty($notificationArray)) {
                $model = ArrayHelper::merge($chatArray, $notificationArray);
            } else {
                $model = [];
            }
            if (empty($model)) {
                return [
                    'model' => [],
                    'total' => 0
                ];
            }
            return [
                'model' => $model,
                'total' => ceil(($notificationTotal + $messageTotal) / $arguments['perPage']),
            ];
        };
    }
}
