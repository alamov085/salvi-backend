<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 03.12.2019
 * Time: 15:08
 */

namespace api\schema;

use GraphQL\Type\Definition\Type;

/**
 * Class LocationListType
 * @package api\schema
 */
class LocationListType extends BasicType
{
    public function __construct($pagination = false)
    {
        $config = [
            'fields' => function () {
                return [
                    'id' => Type::int(),
                    'name' => Type::string(),
                ];
            }
        ];
        parent::__construct($config, $pagination);
    }
}
