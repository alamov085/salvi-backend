<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 25.07.2019
 * Time: 17:59
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
 * Class CatalogLimitationQuery
 * @package api\schema\queries
 */
class CatalogLimitationQuery extends BasicQuery
{

    public function type()
    {
        return Types::catalogLimitationPagination();
    }

    public function args()
    {
        return ArrayHelper::merge(PaginationType::argsDescription(), [
            'catalog_limitation' => Type::nonNull(Type::int())
        ]);
    }

    public function resolve()
    {
        return function ($root, $args) {
            /** @var  $user User */
            $user = Yii::$app->user->getIdentity();
            $query = Company::find()->joinWith(['categories']);
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
            if (isset($args['catalog_limitation'])) {
                $query->andWhere(['catalog_limitation' => $args['catalog_limitation']]);
            }
            if (!empty($args['search'])) {
                $query->andWhere([
                    'or',
                    ['like', 'company.full_name', $args['search']],
                    ['like', 'company.short_name', $args['search']]
                ]);
            }
            $query->groupBy('company.id');
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
