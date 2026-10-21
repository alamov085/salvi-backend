<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 03.07.2019
 * Time: 18:12
 */

namespace api\schema;

use GraphQL\Type\Definition\Type;

/**
 * Class CoordinationUsersType
 * @package api\schema
 */
class CoordinationUsersType extends BasicType
{
    public function __construct($pagination = false)
    {
        $config = [
            'fields' => function () {
                return [
                    'id' => Type::int(),
                    'coordination_id' => Type::int(),
                    'user_id' => Type::int()
                ];
            }
        ];
        parent::__construct($config, $pagination);
    }
}
