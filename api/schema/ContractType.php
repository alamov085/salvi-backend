<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 12.07.2019
 * Time: 17:44
 */

namespace api\schema;

use common\models\Contract;
use common\models\Coordination;
use GraphQL\Type\Definition\Type;
use yii\helpers\ArrayHelper;

/**
 * Class ContractType
 * @package api\schema
 */
class ContractType extends BasicType
{
    public function __construct($pagination = false)
    {
        $config = [
            'fields' => function () {
                return [
                    'id' => Type::int(),
                    'company_id' => Type::int(),
                    'name' => Type::string(),
                    'number' => Type::string(),
                    'supplier_id' => Type::int(),
                    'status' => Type::int(),
                    'supplier_name' => [
                        'type' => Type::string(),
                        'resolve' => function (Contract $model, $args) {
                            return ArrayHelper::getValue($model, 'supplier.name', '');
                        },

                    ],
                    'supplier' => Types::supplier(),
                    'company_entity_id' => Type::int(),
                    'company_entity_name' => [
                        'type' => Type::string(),
                        'resolve' => function (Contract $model, $args) {
                            return ArrayHelper::getValue($model, 'companyEntity.short_name', '');
                        },

                    ],
                    'date_start' => Type::int(),
                    'date_end' => Type::int(),
                    'date_send_noty_end' => Type::int(),
                    'location_id' => Type::int(),
                    'comment' => Type::string(),
                    'created_at' => [
                        'type' => Type::string(),
                        'description' => 'Дата создания',
                        'args' => [
                            'format' => Type::string(),
                        ],
                        'resolve' => function (Contract $model, $args) {
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
                        'resolve' => function (Contract $model, $args) {
                            if (isset($args['format'])) {
                                return date($args['format'], strtotime($model->updated_at));
                            }
                            return date('Y-m-d H:i:s', $model->updated_at);
                        },
                    ],
                    'cost_centers' => [
                        'type' => Type::listOf(Types::contractCostCenter()),
                        'resolve' => function (Contract $model) {
                            return $model->costCenters;
                        }
                    ],
                    'locations' => [
                        'type' => Type::listOf(Types::contractLocation()),
                        'resolve' => function (Contract $model) {
                            return $model->locations;
                        }
                    ],
                    'all_location' => Type::boolean(),
                    'history' => Types::history()
                ];
            }
        ];
        parent::__construct($config, $pagination);
    }
}
