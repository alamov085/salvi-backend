<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 23.07.2019
 * Time: 11:12
 */

namespace api\schema;

use GraphQL\Type\Definition\Type;

/**
 * Class UnitType
 * @package api\schema
 */
class UnitType extends BasicType
{
    public function __construct($pagination = false)
    {
        $config = [
            'description' => 'Единици измерения',
            'fields' => function () {
                return [
                    'id' => Type::int(),
                    'uuid' => Type::string(),
                    'name' => Type::string()
                ];
            }
        ];
        parent::__construct($config, $pagination);
    }
}
