<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 29.06.2019
 * Time: 19:37
 */

namespace api\schema\queries;

use api\schema\PaginationType;
use api\schema\Types;
use common\models\CostCenter;
use common\models\User;
use GraphQL\Type\Definition\Type;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * Class ManagerQuery
 * @package api\schema\queries
 */
class ManagerQuery extends BasicQuery
{

    public function type()
    {
        return Types::userPagination();
    }

    public function args()
    {
        return ArrayHelper::merge(PaginationType::argsDescription(), [
            'id' => Type::int()
        ]);
    }

    public function resolve()
    {
        return function ($root, $args) {
            /** @var  $user User */
            $user = Yii::$app->user->getIdentity();

            $query = User::find()->joinWith(['role', 'userCompany']);
            $arguments = PaginationType::arguments(
                $args,
                array_keys(CostCenter::getTableSchema()->columns)
            );
            $query->where(['manager' => 1]);
            $query->andWhere(['>', 'user.id', '3']);
            if ($user->company_id) {
                $query->andWhere(['user_company.company_id' => $user->company_id]);
            }
            $query->orWhere([
                'and',
                ['all_company' => 1],
                ['manager' => 1],
                ['>', 'user.id', '3']
            ]);
            if (isset($arguments['where'])) {
                foreach ($arguments['where'] as $where) {
                    $query->andWhere($where);
                }
            }
            if (!empty($args['search'])) {
                $query->andWhere([
                    'or',
                    ['like', 'user.first_name', $args['search']],
                    ['like', 'user.last_name', $args['search']],
                    ['like', 'user.email', $args['search']]
                ]);
            }
            $query->groupBy('user.id');
            $total = $query->count();

            $query->offset($arguments['perPage'] * $arguments['page'])
                ->limit($arguments['perPage'])
                ->orderBy($arguments['orderBy'] . ' ' . $arguments['order']);
            return [
                'model' => $query->all(),
                'total' => $total,
            ];
        };
    }
}
