<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 27.06.2019
 * Time: 12:50
 */

namespace api\schema;

use common\models\AuthItem;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

/**
 * Class OrderProductStatusType
 * @package api\schema
 */
class OrderProductStatusType extends BasicType
{
    public function __construct($pagination = false)
    {
        $config = [
            'description' => 'Статусы',
            'fields' => function () {
                return [
                    'id' => Type::int(),
                    'icon' => Type::string(),
                    'text' => Type::string(),
                    'style' => Type::string(),
                ];
            }
        ];
        parent::__construct($config, $pagination);
    }
}
