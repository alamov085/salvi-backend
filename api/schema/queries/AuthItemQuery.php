<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 29.06.2019
 * Time: 14:39
 */

namespace api\schema\queries;

use api\schema\Types;
use common\models\AuthItem;
use common\models\User;
use GraphQL\Type\Definition\Type;
use Yii;

/**
 * Class AuthItemQuery
 * @package api\schema\queries
 */
class AuthItemQuery extends BasicQuery
{
    public function type()
    {
        return Type::listOf(Types::authItem());
    }

    public function args()
    {
        return [
            'type' => Type::int(),
            'status' => Type::int(),
            'search' => Type::string(),
            'manager' => Type::boolean(),
            'list' => Type::boolean()
        ];
    }

    public function resolve()
    {
        return function ($root, $args) {
            /** @var  $user User */
            $user = Yii::$app->user->getIdentity();
            $query = AuthItem::find()
                ->joinWith(['parentGroup'])
                ->where(['!=', 'auth_item.name', 'admin'])
                ->andWhere(['!=', 'auth_item.name', 'api']);
            if (isset($args['type'])) {
                $query->andWhere(['type' => $args['type']]);
            }
            if (isset($args['status'])) {
                $query->andWhere(['status' => $args['status']]);
            }
            if (!empty($args['search'])) {
                $query->andWhere([
                    'or',
                    ['like', 'auth_item.description', $args['search']],
                    ['like', 'auth_item.name', $args['search']],
                ]);
            }
            if (isset($args['manager']) && $args['manager']) {
                $query->andWhere(['company_id' => 0]);
            } else {
                $query->andWhere(['company_id' => $user->company_id]);
                if (!isset($args['list'])) {
                    $query->orWhere(['in', 'auth_item.name', ['defaultUser', 'mainUser']]);
                }
            }
            return $query->all();
        };
    }
}
