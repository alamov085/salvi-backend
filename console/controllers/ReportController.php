<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 23.03.2020
 * Time: 10:34
 */

namespace console\controllers;

use api\helpers\NumberHelper;
use common\components\EmptyLogger;
use common\models\Category;
use common\models\Order;
use common\models\OrderProduct;
use common\models\Product;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use XLSXWriter;
use Yii;
use yii\console\Controller;
use yii\helpers\ArrayHelper;

/**
 * Class ReportController
 * @package console\controllers
 */
class ReportController extends Controller
{
    private $sheetName = 'sheet1';

    public function actionIndex()
    {
        $nameXlsx = date('d_m_Y') . '.xlsx';
        $fileName = Yii::getAlias('@api/web/report') . DIRECTORY_SEPARATOR . $nameXlsx;

        $head = ['Выберите период', '', 'с', 'ДД.ММ.ГГГГ', 'по', 'ДД.ММ.ГГГГ'];

        $writer = new XLSXWriter();
        $writer->writeSheetRow($this->sheetName, $head);
        $writer->markMergedCell($this->sheetName, $start_row = 0, 0, 0, 1);

        $orders = Order::find()
            ->select(['order.id as id', 'locations.name as l_name', 'cost_center.name as c_name'])
            ->joinWith(['location', 'costCenter'], false)
            ->where(['order.company_id' => 4])
//            ->limit(10)
            ->asArray()
            ->all();

        $orderData = [
            ['№ заказа на платформе', '', '', '', '', ''],
            ['Локация', '', '', '', '', ''],
            ['Кост-центр', '', '', '', '', ''],
            [
                'Артикул',
                'Товар',
                'Категория товара(1ая родительская в каталоге)',
                'Наименование покупателя',
                'Код Клиента',
                'Ед измерения'
            ]
        ];
        if ($orders) {
            foreach ($orders as $k => $order) {
                $orderData[0][] = $order['id'];
                $orderData[0][] = '';
                $orderData[1][] = $order['l_name'];
                $orderData[1][] = '';
                $orderData[2][] = $order['c_name'];
                $orderData[2][] = '';
                $orderData[3][] = 'Колв-во в заказе';
                $orderData[3][] = 'Сумма по товару';
            }
            $orderData[3][] = 'Общее количество заказанно';
            $orderData[3][] = 'Цена за ед. Без НДС';
            $orderData[3][] = 'Цена за ед. с НДС';
            $orderData[3][] = 'Сумма по заказам с НДС';
        }
        $totalCells = count($orders);
        foreach ($orderData as $k => $data) {
            $start_row++;
            $writer->writeSheetRow($this->sheetName, $data);
            if ($k < 3) {
                $writer->markMergedCell($this->sheetName, $start_row, 0, $start_row, $end_col = 5);

                $startCell = 6;
                for ($i = 0; $i < $totalCells; $i++) {
                    $writer->markMergedCell($this->sheetName, $start_row, $startCell, $start_row, $startCell + 1);
                    $startCell += 2;
                }
            }

        }

        $productsOrder = OrderProduct::find()
            ->select(['product_uuid', 'order_id', 'quantity', 'total_sum', 'price'])
            ->where(['in', 'order_id', ArrayHelper::getColumn($orders, 'id')])
            ->andWhere(['custom' => 0])
            ->asArray()
            ->all();
        $productUuidWithId = ArrayHelper::index($productsOrder, function ($el) {
            return $el['order_id'] . '_' . $el['product_uuid'];
        });

        $products = Product::find()
            ->select(['product.sku', 'product.name', 'unit.name as u_name', 'product.uuid'])
            ->joinWith(['unit'], false)
            ->where(['in', 'product.uuid', ArrayHelper::getColumn($productsOrder, 'product_uuid')])
            ->asArray()
            ->all();
        $oneMoreProducts = [];
        if ($products) {
            foreach ($products as $prod) {
                $currPrice = 0;
                $data = [
                    $prod['sku'],
                    $prod['name'],
                    '-',
                    '-',
                    '-',
                    $prod['u_name']
                ];
                $totalQuantity = 0;
                $totalSum = 0;
                foreach ($orders as $order) {
                    $quantity = 0;
                    $sum = 0;
                    $price = ArrayHelper::getValue(
                        $productUuidWithId,
                        $order['id'] . '_' . $prod['uuid'] . '.price',
                        0
                    );
                    if (!$currPrice && $price) {
                        $currPrice = $price;
                    }
                    if ($currPrice != $price && $price) {
                        $oneMoreProducts[$prod['uuid']][$price][] = $order['id'];
                    } else {
                        $quantity = ArrayHelper::getValue(
                            $productUuidWithId,
                            $order['id'] . '_' . $prod['uuid'] . '.quantity',
                            0
                        );
                        $sum = ArrayHelper::getValue(
                            $productUuidWithId,
                            $order['id'] . '_' . $prod['uuid'] . '.total_sum',
                            0
                        );
                    }
                    $data[] = $quantity;
                    $data[] = $sum;
                    $totalQuantity += $quantity;
                    $totalSum += $sum;
                }
                $data[] = $totalQuantity;
                $data[] = $currPrice;
                $data[] = $currPrice;
                $data[] = $totalSum;
                $start_row++;
                $writer->writeSheetRow($this->sheetName, $data);
            }
        }

        if ($oneMoreProducts) {
            $productUuid = ArrayHelper::index($products, 'uuid');
            foreach ($oneMoreProducts as $uuid => $oneMore) {
                foreach ($oneMore as $price => $orderId) {
                    $data = [
                        ArrayHelper::getValue($productUuid, $uuid . '.sku'),
                        ArrayHelper::getValue($productUuid, $uuid . '.name'),
                        '-',
                        '-',
                        '-',
                        ArrayHelper::getValue($productUuid, $uuid . '.u_name')
                    ];
                    $totalQuantity = 0;
                    $totalSum = 0;
                    foreach ($orders as $order) {
                        $quantity = 0;
                        $sum = 0;
                        $productPrice = ArrayHelper::getValue(
                            $productUuidWithId,
                            $order['id'] . '_' . $uuid . '.price',
                            0
                        );
                        if ($price == $productPrice) {
                            $quantity = ArrayHelper::getValue(
                                $productUuidWithId,
                                $order['id'] . '_' . $uuid . '.quantity',
                                0
                            );
                            $sum = ArrayHelper::getValue(
                                $productUuidWithId,
                                $order['id'] . '_' . $uuid . '.total_sum',
                                0
                            );
                        }
                        $data[] = $quantity;
                        $data[] = $sum;
                        $totalQuantity += $quantity;
                        $totalSum += $sum;
                    }
                    $data[] = $totalQuantity;
                    $data[] = $price;
                    $data[] = $price;
                    $data[] = $totalSum;
                    $start_row++;
                    $writer->writeSheetRow($this->sheetName, $data);
                }

            }
        }
        $productsOrderGroup = ArrayHelper::index($productsOrder, null, 'order_id');
        $totalRow = ['Тотал грн по заказу', '', '', '', '', ''];
        $totalSumOfAllOrders = 0;
        foreach ($orders as $order) {
            $totalRow[] = '';
            $sum = 0;
            if (isset($productsOrderGroup[$order['id']])) {
                foreach ($productsOrderGroup[$order['id']] as $item) {
                    $sum += $item['total_sum'];
                }
            }
            $totalSumOfAllOrders += $sum;
            $totalRow[] = $sum;
        }
        $totalRow[] = '';
        $totalRow[] = '';
        $totalRow[] = '';
        $totalRow[] = $totalSumOfAllOrders;
        $writer->writeSheetRow($this->sheetName, []);
        $writer->writeSheetRow($this->sheetName, $totalRow);

        $categories = Category::find()
            ->where(['is', 'parent_id', null])
            ->all();
        if ($categories) {
            foreach ($categories as $category) {
                $data = ['', ''];
                $data[] = $category->name;
                $totalQuery = OrderProduct::find()
                    ->where(['parent_category_uuid' => $category->uuid]);
                $totalQuery->andWhere(['in', 'order_id', ArrayHelper::getColumn($orders, 'id')])
                    ->andWhere(['is_delete' => 0]);
                $total = $totalQuery->sum('total_sum');
                $data[] = NumberHelper::convert($total);
                if ($total) {
                    $writer->writeSheetRow($this->sheetName, $data);
                }
            }
        }

        $writer->writeToFile($fileName);
        echo round(memory_get_usage() / 1048576, 2) . " prodData \n";
    }
}
