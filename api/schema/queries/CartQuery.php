<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 23.12.2019
 * Time: 16:14
 */

namespace api\schema\queries;

use api\schema\Types;
use common\models\Cart;
use common\models\Locations;
use common\models\Order;
use common\models\User;
use GraphQL\Type\Definition\Type;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * Class CartQuery
 * @package api\schema\queries
 */
class CartQuery extends BasicQuery
{
	
	public function type()
	{
		return Type::listOf(Types::cart());
	}
	
	public function args()
	{
		return [
			'order_id' => Type::int(),
		];
	}
	
	public function resolve()
	{
		return function ($root, $args)
		{
			/** @var  $user User */
			$user = \Yii::$app->user->getIdentity();
			
			if (isset($args['order_id']))
			{
				$query = Cart::find()->joinWith(['order', 'order.costCenter', 'order.location'])->where(['cart.company_id' => $user->company_id]);
				
				if ($args['order_id'])
				{
					$query->andWhere(['cart.order_id' => $args['order_id']]);
				}
				else
				{
					$query->andWhere(['cart.order_id' => 0])->andWhere(['cart.user_id' => $user->id]);
				}
			}
			else
			{
				$query = Order::find();
				
				$query->where(['order.company_id' => $user->company_id]);
				
				if (Yii::$app->user->can('viewOrder'))
				{
					if (Yii::$app->user->can('viewAndCreateAllOrder'))
					{
						$query->andWhere(['>', 'order.id', 0]);
					}
					elseif (Yii::$app->user->can('viewAndCreateLocationOrder'))
					{
						if ($user->all_location)
						{
							$companyLocation = Locations::find()->where(['company_id' => $user->company_id])->all();
							
							$locations = ArrayHelper::getColumn($companyLocation, 'id');
						}
						else
						{
							$locations = ArrayHelper::getColumn($user->locations, 'location_id');
						}
						$query->andWhere(['in', 'order.location_id', $locations]);
					}
					elseif (Yii::$app->user->can('viewAndCreateCostOrder'))
					{
						$query->andWhere([
							'in',
							'order.cost_center_id',
							ArrayHelper::getValue($user, 'allCost', []),
						]);
					}
					else
					{
						$query->andWhere(['order.created_by_user' => $user->id]);
					}
				}
				else
				{
					$query->andWhere(['order.id' => 0]);
				}
				
				
				$ids = $query->asArray()->all();
				
				$query = Cart::find()->joinWith(['order', 'order.costCenter', 'order.location'])
					->where(['cart.company_id' => $user->company_id])->andWhere(['order.status' => Order::STATUS_OPEN])
					->andWhere(['in', 'order_id', ArrayHelper::getColumn($ids, 'id')]);
			}
			
			$query->orderBy('cart.order_id DESC');
			
			return $query->all();
		};
	}
}
