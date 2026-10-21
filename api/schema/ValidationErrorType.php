<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 08.06.2019
 * Time: 17:15
 */

namespace api\schema;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

/**
 * Class ValidationErrorType
 * @package api\schema
 */
class ValidationErrorType extends ObjectType
{
    /**
     * ValidationErrorType constructor.
     */
    public function __construct()
    {
        $config = [
            'fields' => function () {
                return [
                    'field' => Type::string(),
                    'messages' => Type::listOf(Type::string()),
                ];
            },
        ];
        parent::__construct($config);
    }
}
