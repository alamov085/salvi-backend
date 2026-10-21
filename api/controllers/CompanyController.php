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
use Spatie\ArrayToXml\ArrayToXml;
use yii\web\Controller;

/**
 * Class CompanyController
 * @package api\controllers
 */
class CompanyController extends Controller
{
    public $array = [];

    public function actionIndex()
    {
        $companies = Company::find()->joinWith([
            'companyEntities',
            'locations',
            'locations.costCenters',
            'locations.companyEntities'
        ])->all();
        $array = [];
        if ($companies != false) {
            foreach ($companies as $i => $company) {
                $this->array['Корпорация'][$i] = [
                    '_attributes' => [
                        'УникальныйИД1С' => $company->id,
                        'Наименование' => $company->short_name,
                        'КонтактноеЛицо' => '',
                        'УникальныйИДСайт' => $company->id
                    ],
                    'СписокКонтрагентов' => $this->companyEntity($company->companyEntities),
                    'Локации' => $this->locations($company->locations)
                ];
            }
        }

        $result = ArrayToXml::convert($this->array, 'Корпорации', true, 'UTF-8');
        header('Content-type: text/xml');
        header('Pragma: public');
        header('Cache-control: private');
        header('Expires: -1');
        print($result);
        die();
    }

    private function companyEntity($data)
    {
        $result = [];
        if ($data != false) {
            foreach ($data as $item) {
                /** @var $item CompanyEntity */
                $result['Контрагент'][] = [
                    '_attributes' => [
                        'УникальныйИД1С' => $item->id,
                        'Наименование' => $item->short_name,
                        'ПолноеНаименование' => $item->full_name,
                        'ЮрФизЛицо' => $this->booleanYesNo(1),
                        'КодЄДРПОУ' => $item->edrpou,
                        'ИНН' => $item->inn,
                        'ЮрАдрес' => $item->address,
                        'УникальныйИДСайт' => $item->id,
                    ]
                ];
            }
        }
        return $result;
    }

    private function booleanYesNo($val)
    {
        if ($val) {
            return 'Да';
        } else {
            return 'Нет';
        }
    }

    private function locations($locations)
    {
        $result = [];
        if ($locations != false) {
            foreach ($locations as $location) {
                /** @var $location Locations */
                $result['Локация'][] = [
                    '_attributes' => [
                        'УникальныйИД1С' => $location->id,
                        'Наименование' => $location->name,
                        'ПочтовыйИндекс' => $location->zip_code,
                        'Город' => $location->city,
                        'Улица' => $location->street,
                        'НомерДома' => $location->building,
                        'Этаж' => $location->floor,
                        'Офис' => $location->office,
                        'Активная' => $this->booleanYesNo($location->isActive),
                        'УникальныйИДСайт' => $location->id,
                    ],
                    'СписокЗонДоставки' => $this->delivery(),
                    'СписокКостЦентров' => $this->costCenter($location->costCenters),
                    'СписокДоступныхКонтрагентов' => $this->companyEntityId($location->companyEntities),
                ];
            }
        }
        return $result;
    }

    private function delivery()
    {
        return ['ЗонаДоставки' => [
            '_attributes' => [
                'УникальныйИД1С' => 'static',
                'Наименование' => 'static',
                'Описание' => 'static',
                'УникальныйИДродителя' => 'static',
                'ЭтоГруппа' => 'static',
                'УникальныйИДСайт' => 'static',
            ]
        ]];
    }

    private function costCenter($costCenters)
    {
        $result = [];
        if ($costCenters != false) {
            foreach ($costCenters as $cost) {
                /** @var $cost CostCenter */
                $result['КострЦентр'][] = [
                    '_attributes' => [
                        'УникальныйИД1С' => $cost->id,
                        'Наименование' => $cost->name,
                        'УникальныйИДСайт' => $cost->id,
                    ],
                ];
            }
        }
        return $result;
    }

    private function companyEntityId($data)
    {
        $result = [];
        if ($data != false) {
            foreach ($data as $item) {
                /** @var $item CompanyEntityLocation */
                $result['ДоступныйКонтрагент'][] = [
                    '_attributes' => [
                        'УникальныйИДКонтрагента' => $item->company_id
                    ],
                ];
            }
        }
        return $result;
    }
}
