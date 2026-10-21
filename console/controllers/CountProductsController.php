<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 29.07.2019
 * Time: 14:50
 */

namespace console\controllers;

use common\components\EmptyLogger;
use common\models\CatalogLimitation;
use common\models\Company;
use common\models\Product;
use common\models\ProductCategory;
use common\models\User;
use Exception;
use Yii;
use yii\console\Controller;
use yii\helpers\ArrayHelper;

/**
 * Class CountProductsController
 * @package console\controllers
 */
class CountProductsController extends Controller
{
    private $productIds;
    private $toUpdate;

    public function actionIndex()
    {
        Yii::setLogger(new EmptyLogger());

        $companies = Company::find()->all();
        if ($companies != false) {
            foreach ($companies as $company) {
                $categories = CatalogLimitation::find()
                    ->where(['company_id' => $company->id])
                    ->andWhere(['for_show' => 1])
                    ->all();
                if ($categories != false) {
                    $products = ProductCategory::find()
                        ->where(['in', 'category_uuid', ArrayHelper::getColumn($categories, 'category_uuid')])
                        ->groupBy('product_uuid')
                        ->all();
                    $this->productIds = ArrayHelper::getColumn($products, 'product_uuid');
                    $this->toUpdate = [];
                    $categories = CatalogLimitation::find()
                        ->where(['company_id' => $company->id])
                        ->all();
                    foreach ($categories as $k => $category) {
                        $this->toUpdate[] = [
                            'id' => $category->id,
                            'total_product' => $this->getProducts($category)
                        ];
                        Yii::$app->db->createCommand()
                            ->update(
                                CatalogLimitation::tableName(),
                                ['total_product' => $this->getProducts($category)],
                                ['id' => $category->id]
                            )
                            ->execute();
                        unset($categories[$k]);
                        echo '----' . $category->id . ' ID -----';
                        echo '----' . round(memory_get_usage() / 1024 / 1024, 2) . ' MB' . PHP_EOL;
                    }
                }
            }
        }
    }

    private function getProducts(CatalogLimitation $category)
    {
        $productIds = ProductCategory::find()
            ->where(['category_uuid' => $category->category_uuid])
            ->groupBy('product_category.product_uuid')
            ->all();
        $productIds = ArrayHelper::getColumn($productIds, 'product_uuid');
        $result = array_intersect($this->productIds, $productIds);
//        print_r($category->id);die();
        return count($result);

        $query = Product::find();
//        $query->where(['in', 'product.uuid', $result]);
        $query->where(['in', 'product.uuid', $productIds]);
        $query->andWhere(['in', 'product.uuid', $this->productIds]);
        $total = $query->count();
        return $total;
    }

    public function actionTest()
    {
        $time_start = microtime(true);
        CatalogLimitation::updateAll(['total_product' => 0], ['company_id' => 1, 'specification' => 0]);
        $categories = CatalogLimitation::find()
            ->where(['company_id' => 1])
            ->andWhere(['specification' => 0])
            ->all();
        if ($categories) {
            $categories = ArrayHelper::index($categories, 'category_uuid');
            $productCategories = ProductCategory::find()
                ->where(['in', 'category_uuid', ArrayHelper::getColumn($categories, 'category_uuid')])
                ->all();
//            Yii::error($productCategories);
            if ($productCategories) {
                foreach ($productCategories as $product) {
                    $categories[$product->category_uuid]['total_product'] += 1;
                }
            }
            $data = [];
            foreach ($categories as $category) {
                $data[] = [
                    'id' => $category['id'],
                    'total_product' => $category['total_product'],
                ];
                echo '----' . $category['id'] . ' ID -----';
                echo '----' . round(memory_get_usage() / 1024 / 1024, 2) . ' MB' . PHP_EOL;
            }
            if (!empty($data)) {
                try {
                    $db = \Yii::$app->db;
                    $sql = $db->queryBuilder->batchInsert(
                        CatalogLimitation::tableName(),
                        ['id', 'total_product'],
                        $data
                    );
                    $db->createCommand(
                        $sql . ' ON DUPLICATE KEY UPDATE `total_product`=VALUES(`total_product`)'
                    )->execute();
                } catch (Exception $e) {
                    Yii::error($e);
                }
            }
        }
        echo '----' . round(memory_get_usage() / 1024 / 1024, 2) . ' MB' . PHP_EOL;
        $time_end = microtime(true);

        $execution_time = ($time_end - $time_start);

        echo '<b>Total Execution Time:</b> '.$execution_time.' Mins';
    }
}
