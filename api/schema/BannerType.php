<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 06.02.2020
 * Time: 18:35
 */

namespace api\schema;

use api\schema\BasicType;
use api\schema\Types;
use GraphQL\Type\Definition\Type;

/**
 * Class BannerType
 * @package api\schema
 */
class BannerType extends BasicType
{
    public function __construct($pagination = false)
    {
        $config = [
            'description' => 'Банер',
            'fields' => function () {
                return [
                    'id' => Type::int(),
                    'active' => Type::boolean(),
                    'sort' => Type::int(),
                    'name' => Type::string(),
                    'img_path' => Type::string(),
                    'url' => Type::string(),
                    'text' => Type::string(),
                    'time_swap' => Type::int(),
                    'user_create' => Type::int(),
                    'user_update' => Type::int(),
                ];
            }
        ];
        parent::__construct($config, $pagination);
    }
}
