<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 02.08.2019
 * Time: 16:19
 */

namespace api\schema;

use GraphQL\Type\Definition\Type;

/**
 * Class CompanySupplierType
 * @package api\schema
 */
class CompanySupplierType extends BasicType
{
    public function __construct($pagination = false)
    {
        $config = [
            'fields' => function () {
                return [
                    'id' => Type::int(),
                    'company_id' => Type::int(),
                    'supplier_id' => Type::int(),
                ];
            }
        ];
        parent::__construct($config, $pagination);
    }
}
