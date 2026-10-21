<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 29.06.2019
 * Time: 15:13
 */

namespace api\schema\queries;

use api\schema\Types;
use common\models\Company;
use GraphQL\Type\Definition\Type;

/**
 * Class CompanyStatusQuery
 * @package api\schema\queries
 */
class CompanyStatusQuery extends BasicQuery
{
    public function type()
    {
        return Type::listOf(Types::companyStatus());
    }

    public function args()
    {
        return [];
    }

    public function resolve()
    {
        return function ($root, $args) {
            return Company::statuses();
        };
    }
}
