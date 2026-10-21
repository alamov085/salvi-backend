<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 17.07.2019
 * Time: 13:53
 */

namespace api\schema;

use common\models\OrderComment;
use GraphQL\Type\Definition\Type;

/**
 * Class OrderCommentType
 * @package api\schema
 */
class OrderCommentType extends BasicType
{
    public function __construct($pagination = false)
    {
        $config = [
            'fields' => function () {
                return [
                    'id' => Type::int(),
                    'order_id' => Type::int(),
                    'user_id' => Type::int(),
                    'text' => Type::string(),
                    'created_at' => Type::int(),
                    'user' => [
                        'type' => Types::user(),
                        'resolve' => function (OrderComment $model) {
                            return $model->user;
                        }
                    ]
                ];
            }
        ];
        parent::__construct($config, $pagination);
    }
}
