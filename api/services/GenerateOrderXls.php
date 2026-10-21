<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 06.07.2020
 * Time: 15:24
 */

namespace api\services;

use api\helpers\NumberHelper;
use common\models\Order;
use common\models\OrderProduct;
use XLSXWriter;
use Yii;

/**
 * Class GenerateOrderXls
 * @package api\services
 */
class GenerateOrderXls
{
    private $id;
    private $companyId;
    /** @var Order */
    private $order;

    private $sheetName = 'sheet1';


    /**
     * GenerateOrderXls constructor.
     * @param int $id
     * @param int $companyId
     */
    public function __construct(int $id, int $companyId)
    {
        $this->id = $id;
        $this->companyId = $companyId;
    }

    private function getOrder()
    {
        $this->order = Order::find()
            ->joinWith(['location', 'costCenter', 'budget'])
            ->where(['order.company_id' => $this->companyId])
            ->andWhere(['order.id' => $this->id])
            ->one();
        if (!$this->order) {
            throw new \DomainException('Order not found');
        }
    }

    public function generate()
    {
        $this->getOrder();
        $path = '/report' . DIRECTORY_SEPARATOR . $this->id . '.xlsx';
        $fileName = Yii::getAlias('@api/web') . $path;

        $writer = new XLSXWriterRewrite(Yii::getAlias('@console') . '/xml');
        $writer->writeSheetHeader(
            $this->sheetName,
            ["string", "string", "string", "GENERAL", "GENERAL", "GENERAL"],
            [
                'widths'       => [25, 60, 15, 15, 15, 15],
                "suppress_row" => true
            ]
        );
        $rowStyle = [
            'border'       => 'left,right,top,bottom',
            "border-style" => "thin",
            'border-color' => "#000000",
            'halign'       => 'left'
        ];
        $writer->setTempDir(Yii::getAlias('@console') . '/xml');
        $start_row = 0;
        $orderData = [
            ['Контрагент', $this->order->company->short_name],
            ['Бажана дата доставки', date('d.m.Y', $this->order->delivery_date)],
            ['Локація', $this->order->location->name],
            ['Кост центр', $this->order->costCenter->name],
            ['Бюджет', ($this->order->budget) ? NumberHelper::convertToExcel($this->order->budget->sum) : '-'],
            ['Ініціатор', $this->order->createdBy->first_name . ' ' . $this->order->createdBy->last_name],
            ['№ Purchase order', $this->order->ro_number],
            ['Сума замовлення, грн з ПДВ', NumberHelper::convertToExcel($this->order->total_sum)],
        ];
        foreach ($orderData as $data) {
            $start_row++;
            $writer->writeSheetRow($this->sheetName, $data, $rowStyle);
        }
        $rowStyle['halign'] = "center";
        $rowStyle['valign'] = "distributed";
//        $rowStyle['height'] = "30";
        $writer->writeSheetRow($this->sheetName, ['', '', '', '', '', '']);
        $writer->writeSheetRow(
            $this->sheetName,
            ['Замовлення №' . $this->id, '', '', '', '', ''],
            ['halign' => 'center']
        );
        $writer->markMergedCell($this->sheetName, $start_row + 1, 0, $start_row + 1, $end_col = 5);
        $writer->writeSheetRow(
            $this->sheetName,
            ["Артикул", "Найменування товару", "Од. вим.", "Кількість", "Ціна з ПДВ", "Сума з ПДВ"],
            $rowStyle
        );
        $rowStyleLeft = $rowStyle;
        $rowStyleLeft['halign'] = 'left';
        $start_row++;
        $products = OrderProduct::find()
            ->where(['order_id' => $this->id])
            ->andWhere(['is_delete' => 0])
            ->all();
        if ($products) {
            foreach ($products as $product) {
                $start_row++;
                $writer->writeSheetRow(
                    $this->sheetName,
                    [
                        ($product->custom) ? "" : $product->product->sku,
                        ($product->custom) ? $product->name : $product->product->name,
                        ($product->custom) ? "шт" : $product->product->unit->name,
                        $product->quantity,
                        NumberHelper::convert($product->price),
                        NumberHelper::convert($product->total_sum)
                    ],
                    [
                        $rowStyleLeft,
                        $rowStyleLeft,
                        $rowStyle,
                        $rowStyle,
                        $rowStyle,
                        $rowStyle
                    ]
                );
            }
        }
        $writer->writeSheetRow($this->sheetName, ['', '', '', '', '', '']);
        $writer->writeSheetRow(
            $this->sheetName,
            ['', '', '', '', 'Всього сума з ПДВ', NumberHelper::convertToExcel($this->order->total_sum)],
            ['valign' => "distributed", "halign" => "right"]
        );

        $writer->writeToFile($fileName);
        return Yii::$app->params['backUrl'] . str_replace('\\', '/', $path);
    }
}
