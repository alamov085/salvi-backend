<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 30.06.2019
 * Time: 21:20
 */

namespace api\schema;

use GraphQL\Type\Definition\Type;

/**
 * Class CompanyEntityLocationType
 * @package api\schema
 */
class CompanyEntityLocationType extends BasicType
{
    public function __construct($pagination = false)
    {
        $config = [
            'fields' => function () {
                return [
                    'id' => Type::int(),
                    'company_id' => Type::int(),
                    'location_id' => Type::int()
                ];
            }
        ];
        parent::__construct($config, $pagination);
    }
}
