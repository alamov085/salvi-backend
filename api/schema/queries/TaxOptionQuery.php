<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 29.07.2019
 * Time: 10:26
 */

namespace api\schema\queries;

use api\schema\Types;
use common\models\CompanyEntity;
use GraphQL\Type\Definition\Type;

/**
 * Class TaxOptionQuery
 * @package api\schema\queries
 */
class TaxOptionQuery extends BasicQuery
{

    public function type()
    {
        return Type::listOf(Types::taxOption());
    }

    public function args()
    {
        return [];
    }

    public function resolve()
    {
        return function () {
            return CompanyEntity::taxOption();
        };
    }
}
