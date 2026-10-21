<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 06.02.2020
 * Time: 18:35
 */

namespace app\schema;

use api\schema\BasicType;
use api\schema\Types;
use GraphQL\Type\Definition\Type;

/**
 * Class ElasticSearchHistoryProssesType
 * @package api\schema
 */
class ElasticSearchHistoryProssesType extends BasicType
{
    public function __construct($pagination = false)
    {
        $config = [
            'description' => 'История обработанных запросов',
            'fields' => function () {
                return [
                    'id' => Type::int(),
                    'query' => Type::string(),
                    'user_create' => Type::int(),
                    'user_update' => Type::int(),
                ];
            }
        ];
        parent::__construct($config, $pagination);
    }
}
