<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 29.06.2019
 * Time: 15:15
 */

namespace api\schema\queries;

use api\schema\PaginationType;
use api\schema\Types;
use common\models\AuthItem;
use GraphQL\Type\Definition\Type;
use yii\helpers\ArrayHelper;

/**
 * Class AuthItemListQuery
 * @package api\schema\queries
 */
class AuthItemListQuery extends BasicQuery
{

    public function type()
    {
        return Types::authItemPagination();
    }

    public function args()
    {
        return ArrayHelper::merge(PaginationType::argsDescription(), [
            'type' => [
                'type' => Type::int(),
                'description' => 'Тип значения, 1 - это роль, 2 - это правило доступа'
            ]
        ]);
    }

    public function resolve()
    {
        return function ($root, $args) {
            $query = AuthItem::find()->joinWith(['parentGroup']);
            $arguments = PaginationType::arguments($args);
            if (isset($arguments['where'])) {
                foreach ($arguments['where'] as $where) {
                    $query->andWhere($where);
                }
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
