<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 06.02.2020
 * Time: 18:55
 */

namespace api\schema\queries;

use api\schema\Types;
use common\models\Category;
use GraphQL\Type\Definition\Type;

class CategoryTypesQuery extends BasicQuery
{
    public function type()
    {
        return Type::listOf(Types::status());
    }

    public function args()
    {
        return [];
    }

    public function resolve()
    {
        return function ($root, $args) {
            return Category::types();
        };
    }
}
