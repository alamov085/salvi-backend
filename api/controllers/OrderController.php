<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 24.07.2019
 * Time: 11:03
 */

namespace api\controllers;

use common\models\Company;
use common\models\CompanyEntity;
use common\models\CompanyEntityLocation;
use common\models\CostCenter;
use common\models\Locations;
use common\models\Order;
use common\models\OrderProduct;
use Spatie\ArrayToXml\ArrayToXml;
use yii\helpers\ArrayHelper;
use yii\web\Controller;

/**
 * Class OrderController
 * @package api\controllers
 */
class OrderController extends Controller
{
    public $array = [];

    public function actionIndex()
    {
        return false;
        $orders = Order::find()
            ->joinWith(['location', 'costCenter', 'companyEntity', 'contract.supplier', 'product'])
            ->andWhere(['order.status'=>Order::STATUS_AT_SUPPLIER])
            ->all();
        $array = [];
        if ($orders) {
            foreach ($orders as $i => $order) {
                $comment = $this->customProduct($order->product);
                $this->array['Заказ'][$i] = [
                    'Номер' => $order->id,
                    'Дата' => date('d.m.Y H:i:s'),
                    'Клиент' => ArrayHelper::getValue($order, 'costCenter.uuid_client', ''),
                    'Контрагент' => ArrayHelper::getValue($order, 'companyEntity.edrpou', ''),
                    'Соглашение' => ArrayHelper::getValue($order, 'costCenter.uuid_contract', ''),
                    'Операция' => '',
                    'Организация' => ArrayHelper::getValue($order, 'contract.supplier.edrpou', ''),
                    'Договор' => ArrayHelper::getValue($order, 'contract.number', ''),
                    'Склад' => '',
                    'Товары' => $this->product($order->product),
                    'Комментарий' => $order->comment . ' ' . $comment,
                    'Доставка' => [
                        'СпособДоставки' => '',
                        'АдресДоставки' => '',
                        'Локация' => ArrayHelper::getValue($order, 'location.fullAddress', '') . ' + ' .
                            ArrayHelper::getValue($order, 'costCenter.name', '')
                    ],
                    'ДатаДоставки' => date('d.m.Y', $order->delivery_date)
                ];
            }
        }

        $result = ArrayToXml::convert($this->array, 'Заказы', true, 'UTF-8');
        header('Content-type: text/xml');
        header('Pragma: public');
        header('Cache-control: private');
        header('Expires: -1');
        print($result);
        die();
    }


    private function product($data)
    {
        $result = [];
        if ($data) {
            foreach ($data as $item) {
                /** @var $item OrderProduct */
                if (!$item->custom) {
                    $result['Товар'][] = [
                        'Номенклатура' => $item->product_uuid,
                        'Количество' => $item->quantity,
                        'Цена с НДС' => str_replace('.', ',', $item->price)
                    ];
                }
            }
        }
        return $result;
    }

    private function customProduct($data)
    {
        $result = '';
        if ($data) {
            foreach ($data as $item) {
                /** @var $item OrderProduct */
                if ($item->custom) {
                    $result .= $item->name . '_' . $item->quantity . '_' . 'шт_' . str_replace('.', ',', $item->price) . ' ';
                }
            }
        }
        return $result;
    }
}
