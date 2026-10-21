<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 08.06.2019
 * Time: 17:16
 */

namespace api\schema;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

/**
 * Class ValidationErrorsListType
 * @package api\schema
 */
class ValidationErrorsListType extends ObjectType
{
    /**
     * ValidationErrorsListType constructor.
     */
    public function __construct()
    {
        $config = [
            'fields' => function () {
                return [
                    'errors' => Type::listOf(Types::validationError()),
                ];
            },
        ];
        parent::__construct($config);
    }
}
