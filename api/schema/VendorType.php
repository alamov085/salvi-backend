<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 06.02.2020
 * Time: 18:32
 */

namespace api\schema;

use GraphQL\Type\Definition\Type;

/**
 * Class VendorType
 * @package api\schema
 */
class VendorType extends BasicType
{
    public function __construct($pagination = false)
    {
        $config = [
            'description' => 'Поставщик',
            'fields' => function () {
                return [
                    'id' => Type::int(),
                    'uuid' => Type::string(),
                    'name' => Type::string(),
                    'brands' => Type::listOf(Types::brand())
                ];
            }
        ];
        parent::__construct($config, $pagination);
    }
}
