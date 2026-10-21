<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 17.07.2019
 * Time: 13:58
 */

namespace api\schema\queries;

use api\schema\Types;
use common\models\OrderComment;
use GraphQL\Type\Definition\Type;

/**
 * Class OrderCommentQuery
 * @package api\schema\queries
 */
class OrderCommentQuery extends BasicQuery
{

    public function type()
    {
        return Type::listOf(Types::orderComment());
    }

    public function args()
    {
        return ['order_id' => Type::int()];
    }

    public function resolve()
    {
        return function ($root, $args) {
            $query = OrderComment::find()->joinWith(['user']);
            $query->andWhere(['order_comment.order_id' => $args['order_id']]);
            return $query->all();
        };
    }
}
