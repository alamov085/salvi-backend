<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 06.02.2020
 * Time: 17:48
 */

namespace api\schema;

use GraphQL\Type\Definition\Type;

/**
 * Class AttributeType
 * @package api\schema
 */
class AttributeType extends BasicType
{
    public function __construct($pagination = false)
    {
        $config = [
            'fields' => function () {
                return [
                    'id' => Type::int(),
                    'name' => Type::string(),
                    'values' => Type::listOf(Types::attributeValue())
                ];
            }
        ];
        parent::__construct($config, $pagination);
    }
}
