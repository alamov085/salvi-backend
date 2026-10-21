<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 22.07.2019
 * Time: 17:08
 */

namespace console\controllers;

use common\components\EmptyLogger;
use common\components\XmlParser;
use common\models\Category;
use common\models\Product;
use common\models\ProductCategory;
use common\models\Unit;
use Exception;
use Yii;
use yii\console\Controller;
use yii\helpers\ArrayHelper;

/**
 * Class ProductsController
 * @package console\controllers
 */
class ProductsController extends Controller
{
    public $product = [];
    public $unit = [];
    public $category = [];

    public function actionIndex()
    {
        Yii::setLogger(new EmptyLogger());
        $nodes = (new XmlParser(Yii::getAlias('@console/xml') . DIRECTORY_SEPARATOR . 'Products.xml'))->read('Товары');
        foreach ($nodes as $node) {
            foreach ($node as $item) {
                $item = (array)$item;
                $this->toProduct($item['@attributes']);
                $this->toCategory(
                    (array)$item['СписокКатегорий'],
                    ArrayHelper::getValue($item['@attributes'], 'УникальныйИдентификаторТовара')
                );
            }
        }
        try {
            Yii::$app->db->createCommand()->truncateTable(Product::tableName())->execute();
            Yii::$app->db->createCommand()->batchInsert(
                Product::tableName(),
                ['uuid', 'sku', 'uktzed', 'buy_price', 'name', 'description', 'unit_uuid'],
                $this->product
            )->execute();

            Yii::$app->db->createCommand()->truncateTable(Unit::tableName())->execute();
            Yii::$app->db->createCommand()->batchInsert(
                Unit::tableName(),
                ['uuid', 'name'],
                $this->unit
            )->execute();

            Yii::$app->db->createCommand()->truncateTable(ProductCategory::tableName())->execute();
            Yii::$app->db->createCommand()->batchInsert(
                ProductCategory::tableName(),
                ['category_uuid', 'product_uuid', 'is_main'],
                $this->category
            )->execute();
        } catch (Exception $e) {
            Yii::error($e);
            return false;
        }
        $this->intKey();
    }

    private function toProduct($value)
    {
        $this->product[] = [
            'uuid' => ArrayHelper::getValue($value, 'УникальныйИдентификаторТовара'),
            'sku' => ArrayHelper::getValue($value, 'Артикул'),
            'uktzed' => ArrayHelper::getValue($value, 'КодУКТЗЕД'),
            'buy_price' => str_replace(',', '.', ArrayHelper::getValue($value, 'ЗакупочнаяЦена')),
            'name' => ArrayHelper::getValue($value, 'Наименование'),
            'description' => ArrayHelper::getValue($value, 'ОписаниеТовара'),
            'unit_uuid' => ArrayHelper::getValue($value, 'УникальныйИДЕдиницыИзмерения'),
            'synonym' =>  ArrayHelper::getValue($value, 'Синонимы'),
        ];
        $this->unit[ArrayHelper::getValue($value, 'УникальныйИДЕдиницыИзмерения')] = [
            'uuid' => ArrayHelper::getValue($value, 'УникальныйИДЕдиницыИзмерения'),
            'name' => ArrayHelper::getValue($value, 'НаименованиеЕдиницыИзмерения')
        ];
    }

    private function toCategory($data, $product_uuid)
    {
        $categories = ArrayHelper::getValue($data, 'Категория', []);
        if (!empty($categories)) {
            foreach ($categories as $k => $category) {
                $category = (array)$category;
                $this->productToCategory($category['@attributes'], $product_uuid, $k);
            }
        }
    }

    private function productToCategory($data, $product_uuid, $k)
    {
        $is_main = 0;
        if ($k == 0) {
            $is_main = 1;
        }
        $this->category[] = [
            'category_uuid' => ArrayHelper::getValue($data, 'УникальныйИДКатегории'),
            'product_uuid' => $product_uuid,
            'is_main' => $is_main
        ];
    }

    private function intKey()
    {
        $units = Unit::find()->all();
        $units = ArrayHelper::index($units, 'uuid');
        $products = Product::find()->all();
        if ($products != false) {
            foreach ($products as $product) {
                $product->unit_id = ArrayHelper::getValue($units, $product->unit_uuid . '.id');
                $product->save();
            }
        }
        $products = Product::find()->select('id, uuid')->asArray()->all();
        $products = ArrayHelper::index($products, 'uuid');
        $categories = Category::find()->select('id, uuid')->asArray()->all();
        $categories = ArrayHelper::index($categories, 'uuid');
        $this->category = [];
        foreach (ProductCategory::find()->batch() as $items) {
            foreach ($items as $item) {
                $this->category[] = [
                    'category_uuid' => ArrayHelper::getValue($item, 'category_uuid'),
                    'product_uuid' => ArrayHelper::getValue($item, 'product_uuid'),
                    'is_main' => ArrayHelper::getValue($item, 'is_main'),
                    'product_id' => ArrayHelper::getValue(
                        $products,
                        ArrayHelper::getValue($item, 'product_uuid') . '.id'
                    ),
                    'category_id' => ArrayHelper::getValue(
                        $categories,
                        ArrayHelper::getValue($item, 'category_uuid') . '.id'
                    ),
                ];
            }
        }

        try {
            Yii::$app->db->createCommand()->truncateTable(ProductCategory::tableName())->execute();
            Yii::$app->db->createCommand()->batchInsert(
                ProductCategory::tableName(),
                ['category_uuid', 'product_uuid', 'is_main', 'product_id', 'category_id'],
                $this->category
            )->execute();
        } catch (Exception $e) {
            Yii::error($e);
            return false;
        }
    }
}
