<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 08.06.2019
 * Time: 15:25
 */

namespace backend\schema;

use common\models\Currency;
use common\models\User;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

/**
 * Class QueryType
 * @package backend\schema
 */
class QueryType extends ObjectType
{
    /**
     * QueryType constructor.
     */
    public function __construct()
    {
        $config = [
            'fields' => function () {
                return [
                    'user' => [
                        'type' => Types::user(),
                        'args' => [
                            'id' => Type::nonNull(Type::int()),
                        ],
                        'resolve' => function ($root, $args) {
                            return User::find()->where(['id' => $args['id']])->one();
                        },
                    ],
                    'currency' => [
                        'type' => Type::listOf(Types::currency()),
                        'args' => [
                            'id' => Type::int(),
                        ],
                        'resolve' => function ($root, $args) {
                            $query = Currency::find();
                            if (!empty($args)) {
                                $query->where($args);
                            }
                            return $query->all();
                        },
                    ],
                ];
            }
        ];
        parent::__construct($config);
    }
}
