<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 06.02.2020
 * Time: 18:48
 */

namespace api\schema\queries;

use api\schema\Types;
use common\models\ProductUktzed;
use GraphQL\Type\Definition\Type;

/**
 * Class UktzedTypesQuery
 * @package api\schema\queries
 */
class UktzedTypesQuery extends BasicQuery
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
            return ProductUktzed::types();
        };
    }
}
