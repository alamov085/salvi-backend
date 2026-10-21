<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 17.07.2019
 * Time: 10:25
 */

namespace api\schema;

use common\models\Order;
use GraphQL\Type\Definition\Type;
use yii\helpers\ArrayHelper;

/**
 * Class OrderDashboardType
 * @package api\schema
 */
class OrderDashboardType extends BasicType
{
    public function __construct($pagination = false)
    {
        $config = [
            'fields' => function () {
                return [
                    'id' => Type::int(),
                    'company_id' => Type::int(),
                    'company' => [
                        self::FIELD_TYPE => Types::company(),
                        self::FIELD_RESOLVE => function (Order $model) {
                            return $model->company;
                        }
                    ],
                    'delivery_date' => Type::int(),
                    'company_entity_id' => Type::int(),
                    'company_entity' => [
                        self::FIELD_TYPE => Types::companyEntity(),
                        self::FIELD_RESOLVE => function (Order $model) {
                            return $model->companyEntity;
                        }
                    ],
                    'location_id' => Type::int(),
                    'location_name' => [
                        self::FIELD_TYPE => Type::string(),
                        self::FIELD_RESOLVE => function (Order $model) {
                            return ArrayHelper::getValue($model, 'location.name', '');
                        }
                    ],
                    'cost_center_id' => Type::int(),
                    'cost_center_name' => [
                        self::FIELD_TYPE => Type::string(),
                        self::FIELD_RESOLVE => function (Order $model) {
                            return ArrayHelper::getValue($model, 'costCenter.name', '');
                        }
                    ],
                    'contract_id' => Type::int(),
                    'budget_id' => Type::int(),
                    'budget' => [
                        self::FIELD_TYPE => Types::budget(),
                        self::FIELD_RESOLVE => function (Order $model) {
                            return $model->budget;
                        }
                    ],
                    'comment' => Type::string(),
                    'created_by_user' => Type::int(),
                    'created_at' => Type::int(),
                    'updated_at' => Type::int(),
                    'status' => Type::int(),
                    'total_sum' => Type::float(),
                    'coordination' => Type::boolean(),
                    'empty_string' => Type::boolean(),
                    'history' => Types::history(),
                    'modal' => Type::boolean()
                ];
            }
        ];
        parent::__construct($config, $pagination);
    }
}
