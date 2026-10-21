<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 08.06.2019
 * Time: 15:47
 */

namespace api\schema;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use common\models\User;

/**
 * Class MutationType
 * @package api\schema
 */
class MutationType extends ObjectType
{
    /**
     * MutationType constructor.
     */
    public function __construct()
    {
        $config = [
            'fields' => function () {
                return [
                    'user' => [
                        'type' => Types::userMutation(),
                        'args' => [
                            'id' => Type::nonNull(Type::int()),
                        ],
                        'resolve' => function ($root, $args) {
                            return User::find()->where($args)->one();
                        },
                    ]
                ];
            }
        ];
        parent::__construct($config);
    }
}