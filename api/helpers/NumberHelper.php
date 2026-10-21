<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 07.11.2019
 * Time: 12:01
 */

namespace api\helpers;

use common\models\OrderProduct;
use common\models\Product;
use yii\helpers\ArrayHelper;

/**
 * Class NumberHelper
 * @package api\helpers
 */
class NumberHelper
{
    /**
     * @param $number
     * @param int $decimal
     * @return float
     */
    public static function convert($number, $decimal = 2)
    {
        return (float)number_format($number, $decimal, '.', '');
    }

    public static function calculateTaxPrice(OrderProduct $item)
    {
        if ($item->price == 0) {
            return 0;
        }
        $cof = 1;
        if ($item->product && $item->product->vatPercent) {
            $cof += $item->product->vatPercent;
        }
        $price = $item->price / $cof;
        $price = round($price, 2);
        $total = $price * $item->quantity;
        $totalNds = $total * $cof;
//        $totalNds = round($totalNds, 2);
        return $totalNds;
    }

    public static function calculateTaxPriceItem(Product $product, $oldPrice, $qty = 1)
    {
        if ($oldPrice == 0) {
            return 0;
        }
        $cof = 1;
        if ($product && $product->vatPercent) {
            $cof += $product->vatPercent;
        }
        $price = $oldPrice / $cof;
        $price = round($price, 2);
        $total = $price * $qty;
        $totalNds = $total * $cof;
//        $totalNds = round($totalNds, 2);
        \Yii::info([$oldPrice, $qty]);
        return $totalNds;
    }

    /**
     * @param $amount
     * @return string
     */
    public static function convertToExcel($amount)
    {
        return number_format($amount, 2, ',', '');
    }
}
