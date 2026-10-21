<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 08.06.2019
 * Time: 15:35
 */

namespace api\schema;

use common\models\Locations;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use common\models\User;

class LocationType extends BasicType
{
    public function __construct($pagination = false)
    {
        $config = [
            'fields' => function () {
                return [
                    'id' => Type::int(),
                    'company_id' => Type::int(),
                    'name' => Type::string(),
                    'zip_code' => Type::string(),
                    'city' => Type::string(),
                    'street' => Type::string(),
                    'building' => Type::string(),
                    'office' => Type::string(),
                    'floor' => Type::string(),
                    'phone_one' => Type::string(),
                    'phone_two' => Type::string(),
                    'comment' => Type::string(),
                    'isActive' => Type::int(),
                    'allCompanies' => Type::boolean(),
                    'activeDate' => Type::string(),
                    'budgetLocationOnly' => Type::int(),
                    'created_at' => [
                        'type' => Type::string(),
                        'args' => [
                            'format' => Type::string(),
                        ],
                        'resolve' => function (Locations $model, $args) {
                            if (isset($args['format'])) {
                                return date($args['format'], strtotime($model->created_at));
                            }
                            return date('Y-m-d', $model->created_at);
                        },
                    ],
                    'updated_at' => [
                        'type' => Type::string(),
                        'args' => [
                            'format' => Type::string(),
                        ],
                        'resolve' => function (Locations $model, $args) {
                            if (isset($args['format'])) {
                                return date($args['format'], strtotime($model->updated_at));
                            }
                            return date('Y-m-d', $model->updated_at);
                        },
                    ],
                    'companyEntities' => [
                        'type' => Type::listOf(Types::companyEntityLocation()),
                        'resolve' => function (Locations $model, $args) {
                            return $model->companyEntities;
                        }
                    ],
                    'costCenters' => [
                        'type' => Type::listOf(Types::costCenter()),
                        'resolve' => function (Locations $model, $args) {
                            return $model->costCenters;
                        }
                    ],
                    'history' => Types::history()
                ];
            }
        ];
        parent::__construct($config, $pagination);
    }
}
