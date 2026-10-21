<?php

namespace common\models;

use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "order_product_history".
 *
 * @property int $id
 * @property int $order_id
 * @property int $order_product_id
 * @property string $from_uuid
 * @property string $to_uuid
 * @property int $user_id
 * @property Product|ActiveQuery $oldProduct
 * @property int $created_at
 */
class OrderProductHistory extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'order_product_history';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['order_id', 'order_product_id', 'user_id', 'created_at'], 'integer'],
            [['from_uuid', 'to_uuid'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'order_id' => 'Order ID',
            'order_product_id' => 'Order Product ID',
            'from_uuid' => 'From Uuid',
            'to_uuid' => 'To Uuid',
            'user_id' => 'User ID',
            'created_at' => 'Created At',
        ];
    }

    /**
     * @return ActiveQuery | Product
     */
    public function getOldProduct()
    {
        return $this->hasOne(Product::class, ['uuid' => 'from_uuid']);
    }
}
