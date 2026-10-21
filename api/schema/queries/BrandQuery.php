<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 06.02.2020
 * Time: 18:36
 */

namespace api\schema\queries;

use api\schema\Types;
use common\models\ProductBrand;
use GraphQL\Type\Definition\Type;

/**
 * Class BrandQuery
 * @package api\models\forms
 */
class BrandQuery extends BasicQuery
{

    public function type()
    {
        return Type::listOf(Types::brand());
    }

    public function args()
    {
        return [
            'search' => Type::string(),
            'vendor_uuid' => Type::string(),
            'vendor_id' => Type::int()
        ];
    }

    public function resolve()
    {
        return function ($root, $args) {
            $query = ProductBrand::find();
            if (isset($args['search'])) {
                $query->where(['like', 'product_brand.name', $args['search']]);
            }
            if (isset($args['vendor_uuid'])) {
                $query->andWhere(['product_brand.vendor_uuid' => $args['vendor_uuid']]);
            }
            if (isset($args['vendor_id'])) {
                $query->joinWith(['vendor']);
                $query->andWhere(['vendor.id' => $args['vendor_id']]);
            }
            $query->orderBy('name ASC');
            return $query->all();
        };
    }
}
