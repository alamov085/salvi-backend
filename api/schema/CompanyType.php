<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 27.06.2019
 * Time: 12:50
 */

namespace api\schema;

use common\models\AuthItem;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

/**
 * Class CompanyType
 * @package api\schema
 */
class CompanyType extends BasicType
{
    public function __construct($pagination = false)
    {
        $config = [
            'fields' => function () {
                return [
                    'id'                 => Type::int(),
                    'type'               => [
                        'type'        => Type::int(),
                        'description' => '1 - фоп, 2 - юр.лицо'
                    ],
                    'short_name'         => Type::string(),
                    'full_name'          => Type::string(),
                    'address'            => Type::string(),
                    'edrpou'             => Type::string(),
                    'inn'                => Type::string(),
                    'status'             => Type::int(),
                    'show_catalog'       => Type::boolean(),
                    'empty_string'       => Type::boolean(),
                    'budgets'            => Type::boolean(),
                    'non_budget_order'   => Type::boolean(),
                    'coordination'       => Type::boolean(),
                    'catalog_limitation' => Type::boolean(),
                    'new_category'       => Type::boolean(),
                    'currency_id'        => Type::int(),
                    'specification'      => Type::string(),
                    'history'            => Types::history(),
                    'po_number_required' => Type::boolean(),
                    'manager_email'      => Type::string(),
                ];
            }
        ];
        parent::__construct($config, $pagination);
    }
}
