<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 23.12.2019
 * Time: 15:55
 */

namespace api\schema;

use GraphQL\Type\Definition\Type;

/**
 * Class CartProductHistoryType
 * @package api\schema
 */
class CartProductHistoryType extends BasicType
{
    public function __construct($pagination = false)
    {
        $config = [
            'fields' => function () {
                return [
                    'id' => Type::int(),
                    'cart_id' => Type::int(),
                    'user_id' => Type::int(),
                    'username' => Type::string(),
                    'message' => Type::string(),
                    'created_at' => Type::int(),
                ];
            }
        ];
        parent::__construct($config, $pagination);
    }
}
