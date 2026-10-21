<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 06.02.2020
 * Time: 18:42
 */

namespace api\schema\queries;

use api\schema\Types;
use common\models\ProductUktzed;
use GraphQL\Type\Definition\Type;

/**
 * Class UktzedQuery
 * @package api\schema\queries
 */
class UktzedQuery extends BasicQuery
{

    public function type()
    {
        return Type::listOf(Types::uktzed());
    }

    public function args()
    {
        return ['search' => Type::string()];
    }

    public function resolve()
    {
        return function ($root, $args) {
            $query = ProductUktzed::find();
            if (isset($args['search'])) {
                $query->where(['like', 'name', $args['search']]);
            }
            return $query->all();
        };
    }
}
