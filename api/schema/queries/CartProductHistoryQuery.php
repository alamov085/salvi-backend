<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 23.12.2019
 * Time: 16:08
 */

namespace api\schema\queries;

use api\schema\PaginationType;
use api\schema\Types;
use common\models\Cart;
use common\models\CartProductHistory;
use common\models\User;
use GraphQL\Type\Definition\Type;
use yii\helpers\ArrayHelper;
use yii\caching\TagDependency;
use Yii;

/**
 * Class CartProductHistoryQuery
 * @package api\schema\queries
 */
class CartProductHistoryQuery extends BasicQuery
{

    public function type()
    {
        return Types::cartProductHistoryPagination();
    }

    public function args()
    {
        return ArrayHelper::merge(PaginationType::argsDescription(), [
            'order_id' => [
                'type' => Type::nonNull(Type::int()),
            ],
        ]);
    }

    public function resolve()
    {
        return function ($root, $args) {
            /** @var  $user User */
            
     
            $user = \Yii::$app->user->getIdentity();
            $cartQuery = Cart::find()->select(array_keys((new Cart())->attributeLabels()));
            if ($args['order_id']) {
                $cartQuery->where(['order_id' => $args['order_id']]);
                $cartQuery->andWhere(['company_id' => $user->company_id]);
            } else {
                $cartQuery->where(['order_id' => 0]);
                $cartQuery->andWhere(['company_id' => $user->company_id]);
                $cartQuery->andWhere(['user_id' => $user->id]);
            }
            $cart = $cartQuery->one();
	
	     
            if ($cart) {
                $query = CartProductHistory::find()->select(array_keys((new CartProductHistory())->attributeLabels()));
                $query->where(['cart_id' => $cart->id]);
                $arguments = PaginationType::arguments(
                    $args,
                    array_keys(CartProductHistory::getTableSchema()->columns)
                );

                $CartProductHistory = Yii::$app->cache->get('CartProductHistory_' . $cart->id);

                if (!$CartProductHistory)
                {
                    $CartProductHistory['total'] = $query->count();

                    $query->offset($arguments['perPage'] * $arguments['page'])->limit($arguments['perPage'])->orderBy($arguments['orderBy'] . ' ' . $arguments['order']);

                    $CartProductHistory['model'] = $query->all();

                    Yii::$app->cache->set('CartProductHistory_' . $cart->id, $CartProductHistory, 22000,new TagDependency(['tags'=>['CartProductHistory']]));
                }

	            return $CartProductHistory;
            }
            else
            {
                return [
                    'model' => [],
                    'total' => 0,
                ];
            }
        };
    }
}
