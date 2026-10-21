<?php
namespace common\models;

use Throwable;
use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Exception;


class OrderProductChangeHistory extends ActiveRecord
{
	/**
	 * {@inheritdoc}
	 */
	public static function tableName()
	{
		return 'order_product_change_history';
	}

	public function getProducts()
	{
		return $this->hasMany(Product::class, ['id' => 'product_id']);
	}
}
