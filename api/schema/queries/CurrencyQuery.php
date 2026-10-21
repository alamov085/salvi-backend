<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 29.06.2019
 * Time: 14:39
 */

namespace api\schema\queries;

use api\schema\Types;
use common\models\Currency;
use GraphQL\Type\Definition\Type;

/**
 * Class CurrencyQuery
 * @package api\schema\queries
 */
class CurrencyQuery extends BasicQuery
{
    public function type()
    {
        return Type::listOf(Types::currency());
    }

    public function args()
    {
        return ['id' => Type::int()];
    }

    public function resolve()
    {
        return function ($root, $args) {
            $query = Currency::find();
            if (!empty($args)) {
                $query->where($args);
            }
            return $query->all();
        };
    }
}
