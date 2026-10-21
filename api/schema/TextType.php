<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 21.08.2019
 * Time: 11:06
 */

namespace api\schema;

use GraphQL\Type\Definition\Type;

/**
 * Class TextType
 * @package api\schema
 */
class TextType extends BasicType
{
    public function __construct($pagination = false)
    {
        $config = [
            'fields' => function () {
                return [
                    'text' => Type::string(),
                ];
            }
        ];
        parent::__construct($config, $pagination);
    }
}
