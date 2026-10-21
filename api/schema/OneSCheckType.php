<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 26.12.2019
 * Time: 10:44
 */

namespace api\schema;

use GraphQL\Type\Definition\Type;

/**
 * Class OneSCheckType
 * @package api\schema
 */
class OneSCheckType extends BasicType
{
    public function __construct($pagination = false)
    {
        $config = [
            'fields' => function () {
                return [
                    'in_progress' => Type::boolean()
                ];
            }
        ];
        parent::__construct($config, $pagination);
    }
}
