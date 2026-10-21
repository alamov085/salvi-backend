<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 05.03.2020
 * Time: 9:20
 */

namespace api\schema\queries;

use common\models\Product;
use GraphQL\Type\Definition\Type;

/**
 * Class VatQuery
 * @package api\schema\queries
 */
class VatQuery extends BasicQuery
{

    public function type()
    {
        return Type::listOf(Type::string());
    }

    public function args()
    {
        return [];
    }

    public function resolve()
    {
        return function () {
            return Product::vat();
        };
    }
}
