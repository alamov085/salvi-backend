<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 05.12.2019
 * Time: 18:33
 */

namespace api\schema;

use common\models\OrderProductHistory;
use GraphQL\Type\Definition\Type;

/**
 * Class OrderProductHistoryType
 * @package api\schema
 */
class OrderProductHistoryType extends BasicType
{
    public function __construct($pagination = false)
    {
        $config = [
            'fields' => function () {
                return [
                    'id' => Type::int(),
                    'order_id' => Type::int(),
                    'order_product_id' => Type::int(),
                    'from_uuid' => Type::string(),
                    'to_uuid' => Type::string(),
                    'user_id' => Type::int(),
                    'created_at' => Type::int(),
                    'product_name' => [
                        self::FIELD_TYPE => Type::string(),
                        self::FIELD_RESOLVE => function (OrderProductHistory $model) {
                            if ($model->oldProduct) {
                                return $model->oldProduct->name;
                            }
                            return '';
                        }
                    ]
                ];
            }
        ];
        parent::__construct($config, $pagination);
    }
}
