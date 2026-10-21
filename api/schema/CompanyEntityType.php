<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 27.06.2019
 * Time: 12:50
 */

namespace api\schema;

use common\models\AuthItem;
use common\models\CompanyEntity;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

/**
 * Class CompanyEntityType
 * @package api\schema
 */
class CompanyEntityType extends BasicType
{
    public function __construct($pagination = false)
    {
        $config = [
            'fields' => function () {
                return [
                    'id' => Type::int(),
                    'company_id' => Type::id(),
                    'company' => [
                        'type' => Types::company(),
                        'resolve' => function (CompanyEntity $model) {
                            return $model->company;
                        }
                    ],
                    'short_name' => Type::string(),
                    'full_name' => Type::string(),
                    'address' => Type::string(),
                    'bank_number' => Type::string(),
                    'bank_name' => Type::string(),
                    'mfo' => Type::string(),
                    'edrpou' => Type::string(),
                    'inn' => Type::string(),
                    'tax_payer' => Type::int(),
                    'status' => Type::int(),
                    'status_check' => Type::int(),
                    'is_main' => Type::boolean(),
                    'from_date' => Type::int(),
                    'manager_email' => Type::string(),
                    'created_at' => [
                        'type' => Type::string(),
                        'args' => [
                            'format' => Type::string(),
                        ],
                        'resolve' => function (CompanyEntity $model, $args) {
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
                        'resolve' => function (CompanyEntity $model, $args) {
                            if (isset($args['format'])) {
                                return date($args['format'], strtotime($model->updated_at));
                            }
                            return date('Y-m-d', $model->updated_at);
                        },
                    ],
                    'companyEntitiesLocation' =>[
                        'type'=> Types::companyEntityLocation(),
                        'resolve' => function (CompanyEntity $model, $args) {
                            return $model->companyEntityLocation;
                        },
                    ],
                    'history' => Types::history(),
                ];
            }
        ];
        parent::__construct($config, $pagination);
    }
}
