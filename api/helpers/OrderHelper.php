<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 18.11.2019
 * Time: 14:37
 */

namespace api\helpers;

use common\models\BudgetSpecification;
use common\models\CategoryExclude;
use common\models\Order;
use common\models\OrderProduct;
use common\models\Product;
use common\models\ProductCategory;
use common\models\Specification;
use common\models\SpecificationLocation;
use common\models\SpecificationProduct;
use common\models\User;
use yii\helpers\ArrayHelper;

/**
 * Class OrderHelper
 * @package api\helpers
 */
class OrderHelper
{
    /**
     * @param Order $order
     */
    public static function calculateProductsUpdate(Order $order)
    {
        $order = Order::find()
            ->joinWith(['product', 'company'])
            ->where(['order.id' => $order->id])
            ->one();
        if ($order && $order->product) {
            $permissions = \Yii::$app->authManager->getPermissionsByUser($order->created_by_user);
            $can = false;
            if (isset($permissions['orderShowBtn'])) {
                $can = true;
            }

            $specificationProductsIds = [];
            $catalogProductIds = [];
            $specifications = Specification::find()
                ->where(['company_id' => $order->company_id])
                ->active()
                ->asArray()
                ->all();
            if ($specifications) {
                $specificationBudget = BudgetSpecification::find()
                    ->where(['budget_id' => $order->budget_id])
                    ->all();
                $specificationQuery = SpecificationLocation::find()
                    ->where(['in', 'specification_id', ArrayHelper::getColumn($specifications, 'id')])
                    ->andWhere(['location_id' => $order->location_id]);
                if ($specificationBudget) {
                    $specificationQuery->andWhere([
                        'in',
                        'specification_id',
                        ArrayHelper::getColumn($specificationBudget, 'specification_id')
                    ]);
                }
                $specification = $specificationQuery->all();
                if ($specification) {
                    $specificationProducts = SpecificationProduct::find()
                        ->where(['in', 'specification_id', ArrayHelper::getColumn($specification, 'specification_id')])
                        ->andWhere(['status' => 1])
                        ->asArray()
                        ->all();
                    $specificationProductsIds = ArrayHelper::getColumn($specificationProducts, 'product_uuid');
                    $specificationProducts = ArrayHelper::index($specificationProducts, function ($element) {
                        return $element['product_uuid'] . '-' . $element['status'];
                    });

                }
            }
            $limitation = CategoryExclude::find()
                ->where(['company_id' => $order->company_id])
                ->asArray()
                ->all();
            $catalogProductsQuery = ProductCategory::find();
            if ($limitation) {
                $catalogProductsQuery->where([
                    'not in',
                    'category_uuid',
                    ArrayHelper::getColumn($limitation, 'category_uuid')
                ]);
            }
            $catalogProducts = $catalogProductsQuery->asArray()
                ->all();
            if ($catalogProducts) {
                $catalogProductIds = ArrayHelper::getColumn($catalogProducts, 'product_uuid');
            }
            foreach ($order->product as $product) {
                /** @var $product OrderProduct */
                $item = Product::find()->where(['uuid' => $product->product_uuid])->one();
                if ($item) {
                    if (!$can) {
                        if (!empty($specificationProductsIds) && in_array($item->uuid, $specificationProductsIds)) {
                            $use_markup = ArrayHelper::getValue($specificationProducts, $item->uuid . '-1.use_markup');
                            $markup = ArrayHelper::getValue($specificationProducts, $item->uuid . '-1.markup');
                            $prodPrice = (ArrayHelper::getValue($specificationProducts, $item->uuid . '-1.price') > 0) ?
                                ArrayHelper::getValue($specificationProducts, $item->uuid . '-1.price') :
                                $item->price;
                            $price = ($use_markup) ?
                                $item->buy_price * ((100 + $markup) / 100) :
                                $prodPrice;
                            $viewOnly = 0;
                        } else {
                            $price = $item->price;
                            $viewOnly = 1;
                        }
                    } else {
                        if (!empty($specificationProductsIds) && in_array($item->uuid, $specificationProductsIds) ||
                            !empty($catalogProductIds) && in_array($item->uuid, $catalogProductIds)) {
                            if (in_array($item->uuid, $specificationProductsIds)) {
                                $use_markup = ArrayHelper::getValue($specificationProducts, $item->uuid . '-1.use_markup');
                                $markup = ArrayHelper::getValue($specificationProducts, $item->uuid . '-1.markup');
                                $prodPrice = (ArrayHelper::getValue($specificationProducts, $item->uuid . '-1.price') > 0) ?
                                    ArrayHelper::getValue($specificationProducts, $item->uuid . '-1.price') :
                                    $item->price;
                                $price = ($use_markup) ?
                                    $item->buy_price * ((100 + $markup) / 100) :
                                    $prodPrice;
                            } else {
                                $price = $item->price;
                            }
                            $viewOnly = 0;
                        } else {
                            $price = $item->price;
                            $viewOnly = 1;
                        }
                    }
                    $catalog = 1;
                    if (!empty($specificationProductsIds) && in_array($item->uuid, $specificationProductsIds)) {
                        $catalog = 0;
                    }
                    $product->price = $price;
                    $product->total_sum = NumberHelper::calculateTaxPrice($product);
                    $product->view_only = $viewOnly;
                    $product->approved = OrderProduct::NOT_APPROVED;
                    $product->catalog = $catalog;
                    $product->save();
                }
            }
        }
    }

    /**
     * @param int $id
     * @return float|int
     */
    public static function getTotalSum($id = 0)
    {
        $result = OrderProduct::find()
            ->where(['order_id' => $id])
            ->andWhere([
                'or',
                ['view_only' => 0],
                [
                    'and',
                    ['view_only' => 1],
                    ['approved' => 1]
                ]
            ])
            ->andWhere(['is_delete' => 0])
            ->sum('total_sum');
        return ($result) ? NumberHelper::convert($result) : 0;
    }

    public static function prepareProductAttrForCheckDuplicateOrder($products,$params)
    {
        $user = \Yii::$app->user->getIdentity();

        $permissions = \Yii::$app->authManager->getPermissionsByUser($user->id);
        $can = false;
        if (isset($permissions['orderShowBtn'])) {
            $can = true;
        }

        $specificationProductsIds = [];
        $catalogProductIds = [];
        $specifications = Specification::find()
            ->where(['company_id' => $user->company_id])
            ->active()
            ->asArray()
            ->all();
        if ($specifications) {
            $specificationBudget = BudgetSpecification::find()
                ->where(['budget_id' => $params->budget_id])
                ->all();
            $specificationQuery = SpecificationLocation::find()
                ->where(['in', 'specification_id', ArrayHelper::getColumn($specifications, 'id')])
                ->andWhere(['location_id' => $params->location_id]);
            if ($specificationBudget) {
                $specificationQuery->andWhere([
                    'in',
                    'specification_id',
                    ArrayHelper::getColumn($specificationBudget, 'specification_id')
                ]);
            }
            $specification = $specificationQuery->all();
            if ($specification) {
                $specificationProducts = SpecificationProduct::find()
                    ->where(['in', 'specification_id', ArrayHelper::getColumn($specification, 'specification_id')])
                    ->andWhere(['status' => 1])
                    ->asArray()
                    ->all();
                $specificationProductsIds = ArrayHelper::getColumn($specificationProducts, 'product_uuid');
            }
        }
        $limitation = CategoryExclude::find()
            ->where(['company_id' => $user->company_id])
            ->asArray()
            ->all();

        $catalogProductsQuery = ProductCategory::find();

        if ($limitation)
        {
            $catalogProductsQuery->where([
                'not in',
                'category_uuid',
                ArrayHelper::getColumn($limitation, 'category_uuid')
            ]);
        }

        $catalogProducts = $catalogProductsQuery->asArray()
            ->all();
        if ($catalogProducts)
        {
            $catalogProductIds = ArrayHelper::getColumn($catalogProducts, 'product_uuid');
        }

        foreach ($products as &$product)
        {
            /** @var $product OrderProduct */
            $item = Product::find()->where(['uuid' => $product->product_uuid])->one();

            if ($item)
            {
                if (!$can)
                {
                    if (!empty($specificationProductsIds) && in_array($item->uuid, $specificationProductsIds))
                    {
                        $viewOnly = 0;
                    }
                    else
                    {
                        $viewOnly = 1;
                    }
                }
                else
                {
                    if (!empty($specificationProductsIds) && in_array($item->uuid, $specificationProductsIds) ||
                        !empty($catalogProductIds) && in_array($item->uuid, $catalogProductIds))
                    {
                        $viewOnly = 0;
                    }
                    else
                    {
                        $viewOnly = 1;
                    }
                }

                $product->view_only = $viewOnly;
            }
        }

        return $products;
    }
}
