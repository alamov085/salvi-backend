<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 08.06.2019
 * Time: 15:35
 */

namespace api\schema;

use common\models\CostCenter;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

class CostCenterType extends BasicType
{
    public function __construct($pagination = false)
    {
        $config = [
            'fields' => function () {
                return [
                    'id' => Type::int(),
                    'name' => Type::string(),
                    'location_id' => Type::int(),
                    'location' => [
                        'type' => Types::location(),
                        'resolve' => function (CostCenter $model) {
                            return $model->location;
                        },
                    ],
                    'created_at' => [
                        'type' => Type::string(),
                        'description' => 'Дата создания кост-центра',
                        'args' => [
                            'format' => Type::string(),
                        ],
                        'resolve' => function (CostCenter $model, $args) {
                            if (isset($args['format'])) {
                                return date($args['format'], strtotime($model->created_at));
                            }
                            return date('Y-m-d H:i:s', $model->created_at);
                        },
                    ],
                    'updated_at' => [
                        'type' => Type::string(),
                        'description' => 'Дата обновления кост-центра',
                        'args' => [
                            'format' => Type::string(),
                        ],
                        'resolve' => function (CostCenter $model, $args) {
                            if (isset($args['format'])) {
                                return date($args['format'], strtotime($model->updated_at));
                            }
                            return date('Y-m-d H:i:s', $model->updated_at);
                        },
                    ],
                    'uuid_client' => Type::string(),
                    'uuid_contract' => Type::string(),
                    'history' => Types::history()
                ];
            }
        ];
        parent::__construct($config, $pagination);
    }
}
