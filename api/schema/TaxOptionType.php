<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 29.07.2019
 * Time: 10:25
 */

namespace api\schema;

use GraphQL\Type\Definition\Type;

/**
 * Class TaxOptionType
 * @package api\schema
 */
class TaxOptionType extends BasicType
{
    public function __construct($pagination = false)
    {
        $config = [
            'description' => 'Статус платника податків',
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