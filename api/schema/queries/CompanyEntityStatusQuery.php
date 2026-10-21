<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 05.07.2019
 * Time: 12:55
 */

namespace api\schema\queries;

use api\schema\Types;
use common\models\CompanyEntity;
use GraphQL\Type\Definition\Type;

/**
 * Class CompanyEntityStatusQuery
 * @package api\schema\queries
 */
class CompanyEntityStatusQuery extends BasicQuery
{

    public function type()
    {
        return Type::listOf(Types::companyEntityStatus());
    }

    public function args()
    {
        return [];
    }

    public function resolve()
    {
        return function ($root, $args) {
            return CompanyEntity::statuses();
        };
    }
}
