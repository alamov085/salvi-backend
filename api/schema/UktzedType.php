<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 06.02.2020
 * Time: 18:40
 */

namespace api\schema;

use GraphQL\Type\Definition\Type;

/**
 * Class UktzedType
 * @package api\schema
 */
class UktzedType extends BasicType
{
    public function __construct($pagination = false)
    {
        $config = [
            'description' => 'УКТЗЕД',
            'fields' => function () {
                return [
                    'id' => Type::int(),
                    'uuid' => Type::string(),
                    'full_name' => Type::string(),
                    'short_name' => Type::string(),
                    'type' => Type::int()
                ];
            }
        ];
        parent::__construct($config, $pagination);
    }
}
