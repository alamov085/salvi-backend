<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 14.11.2019
 * Time: 18:51
 */

namespace common\helpers;

use Yii;

/**
 * Class GoogleMapsHelper
 * @package common\helpers
 */
class GoogleMapsHelper
{
    /**
     * @param $address
     * @return array
     */
    public static function getCoordinate($address)
    {
        $result = [];
        if ($address != '') {
            $address = "https://maps.google.com/maps/api/geocode/json?address=" .
                urlencode($address) . "&key=" . Yii::$app->params['googleMapsGeoCode'];
            $data = json_decode(file_get_contents($address));
            if (!empty($data) && !empty($data->results)) {
                foreach ($data->results as $item) {
                    $result['lat'] = $item->geometry->location->lat;
                    $result['lng'] = $item->geometry->location->lng;
                    foreach ($item->address_components as $address) {
                        if ($address->types[0] == 'administrative_area_level_1') {
                            $region = $address->long_name;
                            $regionNumber = array_search($region, self::isoCode());
                            if ($regionNumber) {
                                $result['region'] = $regionNumber;
                                break;
                            }
                        }
                    }
                }
            }
        }
        return $result;
    }

    public static function isoCode()
    {
        return [
            '71' => "Черкаська область",
            '74' => "Чернігівська область",
            '77' => "Чернівецька область",
            '12' => "Дніпропетровська область",
            '14' => "Донецька область",
            '26' => "Івано-Франківська область",
            '63' => "Харківська область",
            '65' => "Херсонська область",
            '68' => "Хмельницька область",
            '35' => "Кіровоградська область",
            '32' => "Київська область",
            '09' => "Луганська область",
            '46' => "Львівська область",
            '48' => "Миколаївська область",
            '51' => "Одеська область",
            '53' => "Полтавська область",
            '56' => "Рівненська область",
            '59' => "Сумська область",
            '61' => "Тернопільська область",
            '05' => "Вінницька область",
            '07' => "Волинська область",
            '21' => "Закарпатська область",
            '23' => "Запорізька область",
            '18' => "Житомирська область",
            '43' => "Автономна Республіка Крим",
            '30' => "Київ",
            '40' => "Севастополь",
        ];
    }
}
