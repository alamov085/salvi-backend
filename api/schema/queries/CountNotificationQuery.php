<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 27.08.2019
 * Time: 10:56
 */

namespace api\schema\queries;

use api\schema\Types;
use common\models\Chat;
use common\models\ChatMessageRead;
use common\models\ChatUser;
use common\models\Notification;
use GraphQL\Type\Definition\Type;
use Yii;
use yii\db\Query;
use yii\helpers\ArrayHelper;

/**
 * Class CountNotificationQuery
 * @package api\schema\queries
 */
class CountNotificationQuery extends BasicQuery
{

    public function type()
    {
        return Types::countNotification();
    }

    public function args()
    {
        return [];
    }

    public function resolve()
    {
        return function ($root, $args) {
            $chat = ChatMessageRead::find()
                ->where(['is_read' => 0])
                ->andWhere(['user_id' => Yii::$app->user->getId()])
                ->count();
            $notification = Notification::find()
                ->where(['notification.user_id' => Yii::$app->user->getId()])
                ->andWhere(['notification.is_read' => 0])
                ->count();
            $result = $chat + $notification;
            return [
                'total' => $result,
                'chat' => $chat,
                'notification' => $notification
            ];
        };
    }
}
