<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 02.08.2019
 * Time: 16:00
 */

namespace api\schema;

use common\models\Specification;
use GraphQL\Type\Definition\Type;

/**
 * Class SpecificationType
 * @package api\schema
 */
class SpecificationType extends BasicType
{
    public function __construct($pagination = false)
    {
        $config = [
            'description' => 'Спецификация',
            'fields' => function () {
                return [
                    'id' => Type::int(),
                    'company_id' => Type::int(),
                    'name' => Type::string(),
                    'contract_id' => Type::int(),
                    'contract' => [
                        'type' => Types::contract(),
                        'resolve' => function (Specification $model) {
                            return $model->contract;
                        }
                    ],
                    'date_start' => Type::int(),
                    'date_end' => Type::int(),
                    'status' => Type::int(),
                    'status_add' => [
                        self::FIELD_TYPE => Type::int(),
                        self::FIELD_RESOLVE => function (Specification $model) {
                            if ($model->status) {
                                $date = date('d.m.Y');
                                $time = strtotime($date);
                                if ($model->date_start <= $time && $model->date_end >= $time) {
                                    return $model->status;
                                } else {
                                    return 2;
                                }
                            }
                            return $model->status;
                        }
                    ],
                    'created_at' => Type::int(),
                    'updated_at' => Type::int(),
                    'locations' => [
                        'type' => Type::listOf(Types::specificationLocation()),
                        'resolve' => function (Specification $model) {
                            return $model->locations;
                        }
                    ],
                    'company' => Types::company(),
                    'locationModel' => [
                        'type' => Type::listOf(Types::location()),
                        'resolve' => function (Specification $model) {
                            return $model->locationsModel;
                        }
                    ],
                    'history' => Types::history(),
                    'markup' => Type::float(),
                    'all_location' => Type::boolean()
                ];
            }
        ];
        parent::__construct($config, $pagination);
    }
}
