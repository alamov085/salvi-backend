<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 06.02.2020
 * Time: 17:53
 */

namespace api\schema\queries;

use api\schema\Types;
use common\models\Attribute;
use GraphQL\Type\Definition\Type;

/**
 * Class AttributeQuery
 * @package api\schema\queries
 */
class AttributeQuery extends BasicQuery
{

    public function type()
    {
        return Type::listOf(Types::attribute());
    }

    public function args()
    {
        return ['uuid' => Type::nonNull(Type::string())];
    }

    public function resolve()
    {
        return function ($root, $args) {
            return Attribute::find()
                ->joinWith(['values'])
                ->where(['attribute.category_uuid' => $args['uuid']])
                ->all();
        };
    }
}
