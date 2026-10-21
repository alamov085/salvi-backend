<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 30.06.2019
 * Time: 21:19
 */

namespace api\schema\queries;

use api\schema\Types;
use GraphQL\Type\Definition\Type;

/**
 * Class CompanyEntityLocationQuery
 * @package api\schema\queries
 */
class CompanyEntityLocationQuery extends BasicQuery
{

    public function type()
    {
        return Type::listOf(Types::companyEntityLocation());
    }

    public function args()
    {
        return [];
    }

    public function resolve()
    {
        // TODO: Implement resolve() method.
    }
}