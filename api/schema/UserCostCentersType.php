<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 01.07.2019
 * Time: 14:42
 */

namespace api\schema;

use GraphQL\Type\Definition\Type;

/**
 * Class UserCostCentersType
 * @package api\schema
 */
class UserCostCentersType extends BasicType
{
    public function __construct($pagination = false)
    {
        $config = [
            'fields' => function () {
                return [
                    'id' => Type::int(),
                    'user_id' => Type::int(),
                    'cost_center_id' => Type::int()
                ];
            }
        ];
        parent::__construct($config, $pagination);
    }
}
