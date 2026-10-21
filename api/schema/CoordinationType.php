<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 03.07.2019
 * Time: 18:06
 */

namespace api\schema;

use common\models\Coordination;
use GraphQL\Type\Definition\Type;

/**
 * Class CoordinationType
 * @package api\schema
 */
class CoordinationType extends BasicType
{
    public function __construct($pagination = false)
    {
        $config = [
            'fields' => function () {
                return [
                    'id' => Type::int(),
                    'company_id' => Type::int(),
                    'name' => Type::string(),
                    'sum' => Type::string(),
                    'type' => Type::int(),
                    'additional_type' => Type::int(),
                    'status' => Type::int(),
                    'statusName' => Type::string(),
                    'all_roles' => Type::boolean(),
                    'roles' => [
                        'type' => Type::listOf(Types::coordinationRoles()),
                        'resolve' => function (Coordination $model) {
                            return $model->roles;
                        },
                    ],
                    'users' => [
                        'type' => Type::listOf(Types::coordinationUsers()),
                        'resolve' => function (Coordination $model) {
                            return $model->users;
                        }
                    ],
                    'created_at' => [
                        'type' => Type::string(),
                        'description' => 'Дата создания',
                        'args' => [
                            'format' => Type::string(),
                        ],
                        'resolve' => function (Coordination $model, $args) {
                            if (isset($args['format'])) {
                                return date($args['format'], strtotime($model->created_at));
                            }
                            return date('Y-m-d H:i:s', $model->created_at);
                        },
                    ],
                    'updated_at' => [
                        'type' => Type::string(),
                        'description' => 'Дата обновления',
                        'args' => [
                            'format' => Type::string(),
                        ],
                        'resolve' => function (Coordination $model, $args) {
                            if (isset($args['format'])) {
                                return date($args['format'], strtotime($model->updated_at));
                            }
                            return date('Y-m-d H:i:s', $model->updated_at);
                        },
                    ],
                    'history' => Types::history(),
                    'enable_notification' => Type::boolean(),
                ];
            }
        ];
        parent::__construct($config, $pagination);
    }
}
