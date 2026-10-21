<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 20.07.2019
 * Time: 9:22
 */

namespace api\schema\queries;

use api\schema\Types;
use common\models\Order;
use common\models\OrderFile;
use common\models\User;
use GraphQL\Type\Definition\Type;
use Yii;

/**
 * Class OrderFileQuery
 * @package api\schema\queries
 */
class OrderFileQuery extends BasicQuery
{

    public function type()
    {
        return Type::listOf(Types::orderFile());
    }

    public function args()
    {
        return ['order_id' => Type::nonNull(Type::int())];
    }

    public function resolve()
    {
        return function ($root, $args) {
            /** @var  $user User */
            $user = Yii::$app->user->getIdentity();
            $order = Order::find()
                ->where(['id' => $args['order_id']])
                ->andWhere(['company_id' => $user->company_id])
                ->one();
            if ($order) {
                $query = OrderFile::find()
                    ->where(['order_id' => $args['order_id']]);
                return $query->all();
            }
            return self::accessDeniedPagination();
        };
    }
}
