<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 06.02.2020
 * Time: 18:33
 */

namespace api\schema\queries;

use api\schema\Types;
use common\models\Vendor;
use GraphQL\Type\Definition\Type;

/**
 * Class VendorQuery
 * @package api\schema\queries
 */
class VendorQuery extends BasicQuery
{

    public function type()
    {
        return Type::listOf(Types::vendor());
    }

    public function args()
    {
        return ['search' => Type::string()];
    }

    public function resolve()
    {
        return function ($root, $args) {
            $query = Vendor::find();
            if (isset($args['search'])) {
                $query->where(['like', 'name', $args['search']]);
            }
            $query->orderBy('name ASC');
            return $query->all();
        };
    }
}
