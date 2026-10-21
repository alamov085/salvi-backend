<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 21.08.2019
 * Time: 15:21
 */

namespace api\schema\queries;

use api\schema\Types;
use common\models\ChatMessageRead;
use GraphQL\Type\Definition\Type;

/**
 * Class UnreadMessageQuery
 * @package api\schema\queries
 */
class UnreadMessageQuery extends BasicQuery
{

    public function type()
    {
        return Types::unreadMessages();
    }

    public function args()
    {
        return [
            'chat_id' => Type::nonNull(Type::int()),
            'user_id' => Type::nonNull(Type::int())
        ];
    }

    public function resolve()
    {
        return function ($root, $args) {
            $model = ChatMessageRead::find()
                ->where(['chat_id' => $args['chat_id']])
                ->andWhere(['user_id' => $args['user_id']])
                ->andWhere(['is_read' => 0])
                ->count();
            return ['count' => $model];
        };
    }
}
