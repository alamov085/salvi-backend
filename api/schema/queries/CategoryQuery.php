<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 23.07.2019
 * Time: 14:22
 */

namespace api\schema\queries;

use api\schema\Types;
use common\models\CatalogLimitation;
use common\models\Category;
use common\models\User;
use GraphQL\Type\Definition\Type;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * Class CategoryQuery
 * @package api\schema\queries
 */
class CategoryQuery extends BasicQuery
{

    public function type()
    {
        return Type::listOf(Types::category());
    }

    public function args()
    {
        return [
            'parent_uuid' => Type::string(),
            'limitation' => Type::nonNull(Type::boolean())
        ];
    }

    public function resolve()
    {
        return function ($root, $args) {
            /** @var  $user User */
            $user = Yii::$app->user->getIdentity();
            if ($args['limitation']) {
                $categories = CatalogLimitation::find()->where(['company_id' => $user->company_id])->all();
            }
            $query = Category::find()
                ->joinWith(['limitation'])
                ->where(['parent_uuid' => ArrayHelper::getValue($args, 'parent_uuid', '')]);
            if ($args['limitation']) {
                $query->andWhere(['in', 'uuid', ArrayHelper::getColumn($categories, 'category_uuid')]);
            }
            return $query->all();
        };
    }
}
