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
 * Class SpecificationLocationType
 * @package api\schema
 */
class SpecificationLocationType extends BasicType
{
    public function __construct($pagination = false)
    {
        $config = [
            'fields' => function () {
                return [
                    'id' => Type::int(),
                    'location_id' => Type::int(),
                    'specification_id' => Type::int(),
                ];
            }
        ];
        parent::__construct($config, $pagination);
    }
}