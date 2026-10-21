<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 01.07.2019
 * Time: 18:04
 */

namespace api\schema;

use common\helpers\NumberHelper;
use common\models\Budget;
use GraphQL\Type\Definition\Type;
use yii\helpers\ArrayHelper;

/**
 * Class BudgetType
 * @package api\schema
 */
class BudgetType extends BasicType
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
                    'availableSum' => Type::string(),
                    'availableSumString' => [
                        'type' => Type::string(),
                        'resolve' => function (Budget $model) {
                            return NumberHelper::numberToString($model->availableSum);
                        }
                    ],
                    'usedSum' => Type::string(),
                    'usedSumString' => [
                        'type' => Type::string(),
                        'resolve' => function (Budget $model) {
                            return NumberHelper::numberToString($model->usedSum);
                        }
                    ],
                    'allSum' => [
                        'type' => Type::string(),
                        'resolve' => function (Budget $model) {
                            return (float)$model->usedSum + (float)$model->availableSum;
                        }
                    ],
                    'date_start' => Type::int(),
                    'date_end' => Type::int(),
                    'budget_restriction' => Type::boolean(),
                    'all_cost' => Type::boolean(),
                    'all_location' => Type::boolean(),
                    'user_id_responsible' => Type::int(),
                    'user_responsible' => [
                        'type' => Types::user(),
                        'resolve' => function (Budget $model) {
                            return $model->responsibleUser;
                        }
                    ],
                    'status' => Type::int(),
                    'created_at' => Type::int(),
                    'updated_at' => Type::int(),
                    'locations' => [
                        'type' => Type::listOf(Types::budgetLocation()),
                        'resolve' => function (Budget $model) {
                            return $model->locations;
                        }
                    ],
                    'locationData' => [
                        'type' => Type::listOf(Types::location()),
                        'resolve' => function (Budget $model) {
                            return $model->locationsData;
                        }
                    ],
                    'cost_centers' => [
                        'type' => Type::listOf(Types::budgetCostCenters()),
                        'resolve' => function (Budget $model) {
                            return $model->costCenters;
                        }
                    ],
                    'costCenterData' => [
                        'type' => Type::listOf(Types::costCenter()),
                        'resolve' => function (Budget $model) {
                            return $model->listOfCostCenters();
                        }
                    ],
                    'history' => Types::history(),
                    'specifications' => [
                        self::FIELD_TYPE => Type::listOf(Type::int()),
                        self::FIELD_RESOLVE => function (Budget $model) {
                            if ($model->specifications) {
                                return ArrayHelper::getColumn($model->specifications, 'specification_id');
                            }
                            return [];
                        }
                    ]
                ];
            }
        ];
        parent::__construct($config, $pagination);
    }
}
