<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 29.06.2019
 * Time: 19:32
 */

namespace api\schema\queries;

use api\schema\Types;
use common\models\AuthItemParent;
use GraphQL\Type\Definition\Type;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * Class AuthItemParentQuery
 * @package api\schema\queries
 */
class AuthItemParentQuery extends BasicQuery
{

    public function type()
    {
        return Type::listOf(Types::authItemParent());
    }

    public function args()
    {
        return ['id' => Type::int()];
    }

    public function resolve()
    {
        return function ($root, $args) {
            $permission = Yii::$app->authManager->getPermissionsByUser(Yii::$app->user->getId());
            $permission = ArrayHelper::getColumn($permission, 'name');
            $query = AuthItemParent::find()->joinWith(['children']);
            $query->andWhere(['in', 'auth_item.name', $permission]);
            $query->orderBy('auth_item_parent.id ASC');
            return $query->all();
        };
    }
}
