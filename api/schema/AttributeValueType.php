<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 06.02.2020
 * Time: 17:50
 */

namespace api\schema;

use GraphQL\Type\Definition\Type;

/**
 * Class AttributeValueType
 * @package api\schema
 */
class AttributeValueType extends BasicType
{
    public function __construct($pagination = false)
    {
        $config = [
            'fields' => function () {
                return [
                    'id' => Type::int(),
                    'name' => Type::string()
                ];
            }
        ];
        parent::__construct($config, $pagination);
    }
}
