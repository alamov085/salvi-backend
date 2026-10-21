<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 03.07.2019
 * Time: 18:18
 */

namespace api\schema\queries;

use api\schema\Types;
use common\models\Coordination;
use GraphQL\Type\Definition\Type;
use Yii;

/**
 * Class CoordinationQuery
 * @package api\schema\queries
 */
class CoordinationQuery extends BasicQuery
{

    public function type()
    {
        return Type::listOf(Types::coordination());
    }

    public function args()
    {
        return ['search' => Type::string()];
    }

    public function resolve()
    {
        return function ($root, $args) {
            if (Yii::$app->user->can('viewCoordination')) {
                $query = Coordination::find()->joinWith(['users', 'roles', 'roles.authItem', 'history']);
                /** @var  $user User */
                $user = Yii::$app->user->getIdentity();
                $query->where(['in', 'coordination.company_id', $user->company_id]);
                if (!empty($args['search'])) {
                    $query->andWhere(['like', 'coordination.name', $args['search']]);
                }
                $query->orderBy('coordination.order ASC');
                return $query->all();
            } else {
                return self::accessDeniedPagination();
            }
        };
    }
}
