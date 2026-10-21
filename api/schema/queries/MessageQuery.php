<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 21.08.2019
 * Time: 11:27
 */

namespace api\schema\queries;

use api\schema\PaginationType;
use api\schema\Types;
use common\models\ChatMessage;
use common\models\ChatMessageRead;
use GraphQL\Type\Definition\Type;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * Class MessageQuery
 * @package api\schema\queries
 */
class MessageQuery extends BasicQuery
{

    public function type()
    {
        return Type::listOf(Types::chatMessages());
    }

    public function args()
    {
        return ArrayHelper::merge(PaginationType::argsDescription(), [
            'chat_id' => Type::nonNull(Type::int())
        ]);
    }

    public function resolve()
    {
        return function ($root, $args) {
            $query = ChatMessage::find()->where(['chat_id' => $args['chat_id']]);
            $result = $query->all();
            if (!Yii::$app->user->isGuest && $result) {
                $user = Yii::$app->user->getIdentity();
                ChatMessageRead::updateAll(
                    ['is_read' => 1],
                    [
                        'and',
                        ['in', 'message_id', ArrayHelper::getColumn($result, 'id')],
                        ['user_id' => $user->id]
                    ]
                );
            }
//            $arguments = PaginationType::arguments(
//                $args,
//                array_keys(ChatMessage::getTableSchema()->columns)
//            );
//            $total = $query->count();
//
//            $query->offset($arguments['perPage'] * $arguments['page'])
//                ->limit($arguments['perPage'])
//                ->orderBy($arguments['orderBy'] . ' ' . $arguments['order']);
//            return [
//                'model' => $query->all(),
//                'total' => $total,
//            ];
            return $result;
        };
    }
}
