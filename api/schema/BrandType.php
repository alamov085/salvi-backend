<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 06.02.2020
 * Time: 18:35
 */

namespace api\schema;

use GraphQL\Type\Definition\Type;

/**
 * Class BrandType
 * @package api\schema
 */
class BrandType extends BasicType
{
    public function __construct($pagination = false)
    {
        $config = [
            'description' => 'Бренд',
            'fields' => function () {
                return [
                    'id' => Type::int(),
                    'uuid' => Type::string(),
                    'name' => Type::string(),
                    'vendor_uuid' => Type::string(),
                    'vendor' => Types::vendor()
                ];
            }
        ];
        parent::__construct($config, $pagination);
    }
}
