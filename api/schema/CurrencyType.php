<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 08.06.2019
 * Time: 15:43
 */

namespace api\schema;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

/**
 * Class CurrencyType
 * @package api\schema
 */
class CurrencyType extends BasicType
{
    /**
     * CurrencyType constructor.
     */
    public function __construct($pagination = false)
    {
        $config = [
            'fields' => function () {
                return [
                    'id' => [
                        'type' => Type::int(),
                    ],
                    'code' => [
                        'type' => Type::string(),
                    ],
                    'name' => [
                        'type' => Type::string(),
                    ]
                ];
            }
        ];
        parent::__construct($config, $pagination);
    }
}
