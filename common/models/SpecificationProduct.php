<?php

namespace common\models;

use Exception;
use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "specification_product".
 *
 * @property int $id
 * @property int $product_id
 * @property string $product_uuid
 * @property int $specification_id
 * @property string $price
 * @property string $markup
 * @property Product|ActiveQuery $product
 * @property int $manual_markup
 * @property int $use_markup
 * @property int $quantity
 * @property int $status
 */
class SpecificationProduct extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'specification_product';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['product_id', 'specification_id', 'manual_markup', 'use_markup', 'status'], 'integer'],
            [['price', 'markup'], 'number'],
            [['product_uuid'], 'string', 'max' => 255],
            ['quantity', 'safe']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'product_id' => 'Product ID',
            'product_uuid' => 'Product Uuid',
            'specification_id' => 'Specification ID',
            'price' => 'Price',
            'markup' => 'Markup',
            'manual_markup' => 'Manual Markup',
        ];
    }

    public static function addProducts($id, $data)
    {
        $insert = [];
        if (!empty($data)) {
            foreach ($data as $value) {
                $item = Product::find()->where(['id' => $value['id']])->one();
                if ($item) {
                    $insert[] = [
                        'specification_id' => $id,
                        'product_id' => $item->id,
                        'product_uuid' => $item->uuid,
                        'price' => 0,
                        'markup' => 0,
                        'manual_markup' => 0,
                        'status' => 1
                    ];
                }
            }
        }
        if (!empty($insert)) {
            try {
                Yii::$app->db->createCommand()->batchInsert(
                    self::tableName(),
                    ['specification_id', 'product_id', 'product_uuid', 'price', 'markup', 'manual_markup', 'status'],
                    $insert
                )->execute();
            } catch (Exception $e) {
                Yii::error($e);
            }
        }
    }

    /**
     * @return ActiveQuery | Product
     */
    public function getProduct()
    {
        return $this->hasOne(Product::class, ['id' => 'product_id']);
    }

    /**
     * @param $data
     * @return bool
     */
    public function updateProduct($data)
    {
        $this->manual_markup = 1;
        $markupFromUser = ArrayHelper::getValue($data, 'markup', 0);
        $markup = str_replace(',', '.', $markupFromUser);
        $markupFromDb = str_replace('.', ',', $this->markup);
        $priceFromUser = ArrayHelper::getValue($data, 'price', 0);
        $price = str_replace(',', '.', $priceFromUser);
        $priceFromDb = str_replace('.', ',', $this->price);

        if ($priceFromUser === $priceFromDb) {
            return true;
        }
        if ($markupFromUser === $markupFromDb) {
            return true;
        }
        if ($this->price == $price) {
            if ($markup > 0) {
                $this->markup = $markup;
                $this->price = $this->product->buy_price * (100 + $markup) / 100;
                $this->use_markup = 1;
            } else {
                $this->markup = 0;
                $this->price = $this->product->buy_price;
                $this->use_markup = 0;
            }
        } elseif ($this->markup == $markup) {
            if ($price > 0) {
                $this->price = $price;
                $this->markup = 0;
                $this->use_markup = 0;
            } else {
                $this->markup = 0;
                $this->price = $this->product->buy_price;
                $this->use_markup = 0;
            }
        } else {
            $this->markup = 0;
            $this->price = $this->product->buy_price;
            $this->use_markup = 0;
        }
        return $this->save();
    }

    public function updateProductQuantity($data)
    {
        $quantity = ArrayHelper::getValue($data, 'quantity', -1);
        if (is_numeric($quantity)) {
            if ($quantity < -1) {
                $quantity = -1;
            }
        } else {
            $quantity = -1;
        }
        $this->quantity = $quantity;
        return $this->save();
    }

    public static function recalculateAvailable(int $id)
    {
        $specification = Specification::find()
            ->joinWith(['locations', 'products'])
            ->where(['specification.id' => $id])->one();
        if ($specification) {
            $specifications = Specification::specificationsInPeriod($specification);
            if ($specifications) {
                $locationsSpec = SpecificationLocation::find()
                    ->where(['in', 'specification_id', $specifications])
                    ->andWhere(['in', 'location_id', ArrayHelper::getColumn($specification->locations, 'location_id')])
                    ->asArray()
                    ->all();
                if ($locationsSpec) {
                    $products = SpecificationProduct::find()
                        ->select(['product_uuid', 'status'])
                        ->where(['in', 'specification_id', ArrayHelper::getColumn($locationsSpec, 'specification_id')])
                        ->asArray()
                        ->all();
                    if ($products) {
                        $productUuid = ArrayHelper::index($products, function ($element) {
                            return $element['product_uuid'] . '-' . $element['status'];
                        });
                        Yii::info($productUuid);
                    }
                    $temp = [];
                    if ($specification->products) {
                        foreach ($specification->products as $product) {
                            /** @var $product SpecificationProduct */
                            $temp[] = [
                                'id' => $product->id,
                                'status' => (isset($productUuid[$product->product_uuid . '-1'])) ? 0 : 1
                            ];
                            Yii::info($product->product_uuid . '-1');
                        }
                    }
                    if (!empty($temp)) {
                        try {
                            $db = Yii::$app->db;
                            $sql = $db->queryBuilder->batchInsert(
                                SpecificationProduct::tableName(),
                                ['id', 'status'],
                                $temp
                            );
                            $db->createCommand(
                                $sql . ' ON DUPLICATE KEY UPDATE `status`=VALUES(`status`)'
                            )->execute();
                        } catch (Exception $e) {
                            Yii::error($e);
                        }
                    }
                }
            }
        }
    }
}
