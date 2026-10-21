<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 29.06.2019
 * Time: 15:09
 */

namespace api\schema\queries;

use api\schema\PaginationType;
use api\schema\Types;
use common\models\Company;
use common\models\User;
use GraphQL\Type\Definition\Type;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * Class CompanyQuery
 * @package api\schema\queries
 */
class CompanyQuery extends BasicQuery
{
    public function type()
    {
        return Types::companyPagination();
    }

    public function args()
    {
        return ArrayHelper::merge(PaginationType::argsDescription(), [
            'id' => [
                'type' => Type::int(),
            ],
            'status' => [
                'type' => Type::int()
            ]
        ]);
    }

    public function resolve()
    {
        return function ($root, $args) {
            /** @var  $user User */
            $user = Yii::$app->user->getIdentity();
            $query = Company::find()->joinWith(['history']);
            $arguments = PaginationType::arguments(
                $args,
                array_keys(Company::getTableSchema()->columns)
            );
            if ($user->manager) {
                $query->where(['in', 'company.id', $user->managerCompanies]);
            } else {
                if (!Yii::$app->user->can('viewCompanyList')) {
                    $query->where(['company.id' => $user->company_id]);
                }
            }
            if (isset($args['status']) && !empty($args['status'])) {
                $query->andWhere(['company.status' => $args['status']]);
            }
            if (!empty($args['search'])) {
                $query->andWhere([
                    'or',
                    ['like', 'company.short_name', $args['search']],
                    ['like', 'company.full_name', $args['search']]
                ]);
            }
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
