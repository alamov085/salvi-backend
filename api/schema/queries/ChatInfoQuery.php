<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 27.08.2019
 * Time: 12:25
 */

namespace api\schema\queries;

use api\schema\Types;
use common\models\Chat;
use GraphQL\Type\Definition\Type;

/**
 * Class ChatInfoQuery
 * @package api\schema\queries
 */
class ChatInfoQuery extends BasicQuery
{

    public function type()
    {
        return Types::chatInfo();
    }

    public function args()
    {
        return ['id' => Type::nonNull(Type::int())];
    }

    public function resolve()
    {
        return function ($root, $args) {
            $model = Chat::find()->where(['chat.id' => $args['id']])->joinWith(['user', 'guestUser'])->one();
            return $model;
        };
    }
}
