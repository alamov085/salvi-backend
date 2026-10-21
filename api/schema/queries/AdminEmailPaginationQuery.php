<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 03.02.2020
 * Time: 10:09
 */

namespace api\schema\queries;

use api\schema\PaginationType;
use api\schema\Types;
use common\models\AdminEmail;
use GraphQL\Type\Definition\Type;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * Class AdminEmailPaginationQuery
 * @package api\schema\queries
 */
class AdminEmailPaginationQuery extends BasicQuery
{

    public function type()
    {
        return Types::adminEmailPagination();
    }

    public function args()
    {
        return ArrayHelper::merge(PaginationType::argsDescription(), [
            'id' => [
                'type' => Type::int(),
            ]
        ]);
    }

    public function resolve()
    {
        return function ($root, $args) {
            $query = AdminEmail::find();
            if (Yii::$app->user->can('managementEmail')) {
                $query->andWhere(['>', 'admin_email.id', 0]);
            } else {
                $query->andWhere(['admin_email.id' => 0]);
            }
            $arguments = PaginationType::arguments(
                $args,
                array_keys(AdminEmail::getTableSchema()->columns)
            );
            $query->groupBy('admin_email.id');
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
