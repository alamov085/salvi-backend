<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 05.07.2019
 * Time: 12:07
 */

namespace common\helpers;

/**
 * Class NumberHelper
 * @package common\helpers
 */
class NumberHelper
{
    public static function numberToString($number)
    {
        if ($number < 1000) {
            $format = number_format($number, 2, '.', '');
        } elseif ($number < 1000000) {
            $format = number_format($number / 1000, 2, '.', '') . ' тис.';
        } elseif ($number < 1000000000) {
            $format = number_format($number / 1000000, 2, '.', '') . ' млн.';
        } elseif ($number < 1000000000000) {
            $format = number_format($number / 1000000000, 2, '.', '') . ' млрд.';
        } elseif ($number < 1000000000000000) {
            $format = number_format($number / 1000000000000, 2, '.', '') . ' трлн.';
        } else {
            $format = number_format($number / 1000000000000000, 2, '.', '') . ' квдр.';
        }
        return $format;
    }
}
