<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 12.07.2019
 * Time: 17:50
 */

namespace api\schema;

use GraphQL\Type\Definition\Type;

/**
 * Class ContractCostCenterType
 * @package api\schema
 */
class ContractCostCenterType extends BasicType
{
    public function __construct($pagination = false)
    {
        $config = [
            'fields' => function () {
                return [
                    'id' => Type::int(),
                    'contract_id' => Type::int(),
                    'cost_center_id' => Type::int()
                ];
            }
        ];
        parent::__construct($config, $pagination);
    }
}
