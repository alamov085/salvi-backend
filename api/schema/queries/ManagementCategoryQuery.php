<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 06.02.2020
 * Time: 18:29
 */

namespace api\schema\queries;

use api\schema\Types;
use common\models\Category;
use GraphQL\Type\Definition\Type;

/**
 * Class ManagementCategoryQuery
 * @package api\schema\queries
 */
class ManagementCategoryQuery extends BasicQuery
{
    public function type()
    {
        return Type::listOf(Types::category());
    }

    public function args()
    {
        return ['search' => Type::string()];
    }

    public function resolve()
    {
        return function ($root, $args) {
            $query = Category::find()
                ->where(['has_child' => 0]);
            if (isset($args['search'])) {
                $query->andWhere(['like', 'name', $args['search']]);
            }
            return $query->all();
        };
    }
}
