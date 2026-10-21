<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 08.06.2019
 * Time: 15:35
 */

namespace api\schema;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use common\models\User;
use yii\helpers\ArrayHelper;

class UserType extends BasicType
{
    public function __construct($pagination = false)
    {
        $config = [
            'fields' => function () {
                return [
                    'id' => Type::int(),
                    'first_name' => Type::string(),
                    'last_name' => Type::string(),
                    'email' => Type::string(),
                    'status' => Type::int(),
                    'currency_id' => Type::int(),
                    'company_id' => Type::int(),
                    'password_difficult' => Type::int(),
                    'phone_number' => Type::string(),
                    'username' => Type::string(),
                    'reason' => Type::string(),
                    'position' => Type::string(),
                    'all_location' => Type::boolean(),
                    'all_cost' => Type::boolean(),
                    'locations' => [
                        'type' => Type::listOf(Types::userLocation()),
                        'resolve' => function (User $user) {
                            return $user->locations;
                        },
                    ],
                    'company_name' => [
                        'type' => Type::string(),
                        'resolve' => function (User $model) {
                            return ArrayHelper::getValue($model, 'company.short_name', '');
                        }
                    ],
                    'company_status' => [
                        'type' => Type::int(),
                        'resolve' => function (User $model) {
                            return ArrayHelper::getValue($model, 'company.status', 0);
                        }
                    ],
                    'currency' => [
                        'type' => Types::currency(),
                        'resolve' => function (User $user) {
                            return $user->currency;
                        },
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
                    'costCenters' => [
                        'type' => Type::listOf(Types::userCostCenters()),
                        'resolve' => function (User $model) {
                            return $model->costCenters;
                        }
                    ],
                    'role_name' => [
                        'type' => Type::string(),
                        'resolve' => function (User $model) {
                            return ArrayHelper::getValue($model, 'role.itemName.name', '');
                        }
                    ],
                    'role_description' => [
                        'type' => Type::string(),
                        'resolve' => function (User $model) {
                            return ArrayHelper::getValue($model, 'role.itemName.description', '');
                        }
                    ],
                    'manager' => Type::int(),
                    'all_company' => Type::boolean(),
                    'userCompany' => [
                        'type' => Type::listOf(Types::userCompany()),
                        'resolve' => function (User $model) {
                            return $model->userCompany;
                        }
                    ],
                    'userCompanyList' => [
                        'type' => Type::listOf(Types::company()),
                        'resolve' => function (User $model) {
                            return $model->userCompanyList;
                        }
                    ],
                    'history' => Types::history(),
                    'is_sending_mail' => Type::boolean(),
                ];
            }
        ];
        parent::__construct($config, $pagination);
    }
}
