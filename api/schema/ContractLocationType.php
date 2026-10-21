<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 02.08.2019
 * Time: 11:46
 */

namespace api\schema;

use GraphQL\Type\Definition\Type;

/**
 * Class ContractLocationType
 * @package api\schema
 */
class ContractLocationType extends BasicType
{
    public function __construct($pagination = false)
    {
        $config = [
            'fields' => function () {
                return [
                    'id' => Type::int(),
                    'contract_id' => Type::int(),
                    'location_id' => Type::int()
                ];
            }
        ];
        parent::__construct($config, $pagination);
    }
}
