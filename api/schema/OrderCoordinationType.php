<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 18.07.2019
 * Time: 15:32
 */

namespace api\schema;

use common\models\OrderCoordination;
use common\models\User;
use GraphQL\Type\Definition\Type;
use yii\helpers\ArrayHelper;

/**
 * Class OrderCoordinationType
 * @package api\schema
 */
class OrderCoordinationType extends BasicType
{
    public function __construct($pagination = false)
    {
        $config = [
            'fields' => function () {
                return [
                    'id' => Type::int(),
                    'coordination_id' => Type::int(),
                    'order_id' => Type::int(),
                    'company_id' => Type::int(),
                    'name' => Type::string(),
                    'sum' => Type::float(),
                    'type' => Type::int(),
                    'status' => Type::int(),
                    'order' => Type::int(),
                    'all_roles' => Type::boolean(),
                    'created_at' => Type::int(),
                    'updated_at' => Type::int(),
                    'next_step' => Type::boolean(),
                    'canApprove' => Type::boolean(),
                    'status_cancel' => Type::boolean(),
                    'user' => [
                        'type'    => Types::user(),
                        'resolve' => function (OrderCoordination $model) {
                            return $model->user;
                        }
                    ],
                ];
            }
        ];
        parent::__construct($config, $pagination);
    }
}
