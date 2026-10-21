<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 17.07.2019
 * Time: 10:25
 */

namespace api\schema;

use common\models\Order;
use common\models\OrderCoordination;
use GraphQL\Type\Definition\Type;
use yii\helpers\ArrayHelper;

/**
 * Class OrderType
 * @package api\schema
 */
class OrderType extends BasicType
{
    public function __construct($pagination = false)
    {
        $config = [
            'fields' => function () {
                return [
                    'id'                            => Type::int(),
                    'order_id'                      => [
                        self::FIELD_TYPE    => Type::int(),
                        self::FIELD_RESOLVE => function (Order $model) {
                            return $model->id;
                        }
                    ],
                    'company_id'                    => Type::int(),
                    'delivery_date'                 => Type::int(),
                    'company_entity_id'             => Type::int(),
                    'company_entity'                => [
                        'type'    => Types::companyEntity(),
                        'resolve' => function (Order $model) {
                            return $model->companyEntity;
                        }
                    ],
                    'location_id'                   => Type::int(),
                    'location_name'                 => [
                        'type'    => Type::string(),
                        'resolve' => function (Order $model) {
                            return ArrayHelper::getValue($model, 'location.name', '');
                        }
                    ],
                    'cost_center_id'                => Type::int(),
                    'cost_center_name'              => [
                        'type'    => Type::string(),
                        'resolve' => function (Order $model) {
                            return ArrayHelper::getValue($model, 'costCenter.name', '');
                        }
                    ],
                    'contract_id'                   => Type::int(),
	                'contract'                        => [
		                'type'    => Types::contract(),
		                'resolve' => function (Order $model) {
			                return $model->contract;
		                }
	                ],
                    'budget_id'                     => Type::int(),
                    'budget'                        => [
                        'type'    => Types::budget(),
                        'resolve' => function (Order $model) {
                            return $model->budget;
                        }
                    ],
                    'comment'                       => Type::string(),
                    'created_by_user'               => Type::int(),
                    'created_by'                    => [
                        self::FIELD_TYPE    => Types::user(),
                        self::FIELD_RESOLVE => function (Order $model) {
                            return $model->createdBy;
                        }
                    ],
                    'created_at'                    => Type::int(),
                    'updated_at'                    => Type::int(),
                    'status'                        => Type::int(),
                    'total_sum'                     => Type::string(),
                    'coordination'                  => Type::boolean(),
                    'empty_string'                  => Type::boolean(),
                    'history'                       => Types::history(),
                    'modal'                         => Type::boolean(),
                    'cart_id'                       => [
                        self::FIELD_TYPE    => Type::int(),
                        self::FIELD_RESOLVE => function (Order $model) {
                            if ($model->cart) {
                                return $model->cart->id;
                            }
                            return 0;
                        }
                    ],
                    'total'                         => [
                        self::FIELD_TYPE    => Type::string(),
                        self::FIELD_RESOLVE => function (Order $model) {
                            if ($model->cart) {
                                return $model->cart->total;
                            }
                            return 0;
                        }
                    ],
                    'can_approve_next_coordination' => [
                        self::FIELD_TYPE    => Type::boolean(),
                        self::FIELD_RESOLVE => function (Order $order) {
                            $coordination = OrderCoordination::find()
                                ->where(['next_step' => 1])
                                ->andWhere(['order_id' => $order->id])
                                ->one();
                            if ($coordination) {
                                return $coordination->canApprove;
                            }
                            return false;
                        }
                    ],
                    'ro_number'                     => [
                        self::FIELD_TYPE    => Type::string(),
                        self::FIELD_RESOLVE => function (Order $order) {
                            if (is_null($order->ro_number)) {
                                return '';
                            }
                            return $order->ro_number;
                        }
                    ],
	                'ttn'                           => Type::string(),
	                'one_day_shipment'              => Type::boolean()
                ];
            }
        ];
        parent::__construct($config, $pagination);
    }
}