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
 * Class UserQuery
 * @package api\schema\queries
 */
class UserQuery extends BasicQuery
{

    public function type()
    {
        return Types::userPagination();
    }

    public function args()
    {
        return ArrayHelper::merge(PaginationType::argsDescription(), [
            'id' => [
                'type' => Type::int()
            ],
        ]);
    }

    public function resolve()
    {
        return function ($root, $args) {
            /** @var  $user User */
            $user = Yii::$app->user->getIdentity();

            $query = User::find()->joinWith([
                'locations',
                'costCenters',
                'role',
                'companyEntity',
                'role.itemName',
                'currency',
                'company',
                'userCompany',
                'userCompanyList as u_company',
                'history'
            ]);
            $arguments = PaginationType::arguments(
                $args,
                array_keys(User::getTableSchema()->columns)
            );
//            if ($user->mainCompanyId) {
            $query->where(['user.company_id' => $user->company_id]);
//            }
            $query->andWhere(['manager' => 0]);
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
