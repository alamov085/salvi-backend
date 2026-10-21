<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 08.06.2019
 * Time: 15:35
 */

namespace backend\schema;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use common\models\User;

class UserType extends ObjectType
{
    public function __construct()
    {
        $config = [
            'fields' => function () {
                return [
                    'first_name' => [
                        'type' => Type::string(),
                    ],
                    'last_name' => [
                        'type' => Type::string(),
                    ],
                    'email' => [
                        'type' => Type::string(),
                    ],
                    'created_at' => [
                        'type' => Type::string(),
                        'description' => 'Date when user was created',
                        'args' => [
                            'format' => Type::string(),
                        ],
                        'resolve' => function (User $user, $args) {
                            if (isset($args['format'])) {
                                return date($args['format'], strtotime($user->created_at));
                            }
                            return date('Y-m-d H:i:s', $user->created_at);
                        },
                    ],
                    'status' => [
                        'type' => Type::int(),
                    ],
                    'currency_id' => [
                        'type' => Type::int(),
                    ],
                    'currency' => [
                        'type' => Types::currency(),
                        'resolve' => function (User $user) {
                            return $user->currency;
                        },
                    ],
                ];
            }
        ];
        parent::__construct($config);
    }
}