<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 30.06.2019
 * Time: 21:20
 */

namespace api\schema;

use GraphQL\Type\Definition\Type;

/**
 * Class BudgetLocationType
 * @package api\schema
 */
class BudgetLocationType extends BasicType
{
    public function __construct($pagination = false)
    {
        $config = [
            'fields' => function () {
                return [
                    'id' => Type::int(),
                    'budget_id' => Type::int(),
                    'location_id' => Type::int()
                ];
            }
        ];
        parent::__construct($config, $pagination);
    }
}
