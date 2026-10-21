<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 25.07.2019
 * Time: 11:30
 */

namespace api\schema\queries;

use api\schema\PaginationType;
use api\schema\Types;
use common\models\Cart;
use common\models\CartProduct;
use common\models\User;
use GraphQL\Type\Definition\Type;
use yii\helpers\ArrayHelper;

/**
 * Class CartProductQuery
 * @package api\schema\queries
 */
class CartProductQuery extends BasicQuery
{
	
	public function type()
	{
		return Types::cartProductPagination();
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
		return function ($root, $args)
		{
			/** @var  $user User */
			$user = \Yii::$app->user->getIdentity();
			$cartQuery = Cart::find()->select(array_keys((new Cart())->attributeLabels()));
			if ($args['order_id'])
			{
				$cartQuery->where(['order_id' => $args['order_id']]);
				$cartQuery->andWhere(['company_id' => $user->company_id]);
			}
			else
			{
				$cartQuery->where(['order_id' => 0]);
				$cartQuery->andWhere(['company_id' => $user->company_id]);
				$cartQuery->andWhere(['user_id' => $user->id]);
			}
			
			$cart = $cartQuery->one();
			
			if (!$args['order_id'] && !$cart)
			{
				$cart = new Cart([
					'company_id' => $user->company_id,
					'order_id' => 0,
					'user_id' => $user->id,
				]);
				$cart->save();
			}
			
			if ($cart)
			{
				$query = CartProduct::find()->with([
					'product',
					'product.unit',
				]);
				$query->where(['cart_product.cart_id' => $cart->id]);
				
				$arguments = PaginationType::arguments($args, array_keys(CartProduct::getTableSchema()->columns));
				$query->groupBy('cart_product.id');
				
				$query->offset($arguments['perPage'] * $arguments['page'])->limit($arguments['perPage'])
					->orderBy($arguments['orderBy'] . ' ' . $arguments['order']);
				
				$list = $query->all();
				
				return [
					'model' => $list,
					'total' => (!is_null($list)) ? count($list) : 0,
				];
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
