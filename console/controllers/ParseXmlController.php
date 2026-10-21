<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 22.07.2019
 * Time: 12:26
 */

namespace console\controllers;

use common\components\EmptyLogger;
use common\components\XmlParser;
use common\models\Category;
use Exception;
use Yii;
use yii\console\Controller;
use yii\helpers\ArrayHelper;

/**
 * Class ParseXmlController
 * @package console\controllers
 */
class ParseXmlController extends Controller
{
    public $array = [];

    public function actionIndex()
    {
        Yii::setLogger(new EmptyLogger());
        $nodes = (new XmlParser(Yii::getAlias('@console/xml') . DIRECTORY_SEPARATOR . 'Category.xml'))
            ->read('Категории');
        foreach ($nodes as $node) {
            foreach ($node as $i => $item) {
                $item = (array)$item;
                $this->parseCategory($item);
            }
        }
        try {
            Yii::$app->db->createCommand()->truncateTable(Category::tableName())->execute();
            Yii::$app->db->createCommand()->batchInsert(
                Category::tableName(),
                ['uuid', 'name', 'is_view', 'parent_uuid', 'has_child'],
                $this->array
            )->execute();
        } catch (Exception $e) {
            Yii::error($e);
        }
        $this->actionUpdateParents();
    }

    public function actionUpdateParents()
    {
        $categories = Category::find()->all();
        if ($categories != false) {
            $categoriesIndex = ArrayHelper::index($categories, 'uuid');
            $i = 0;
            foreach ($categories as $k => $category) {
                /** @var $category Category */
                if (isset($categoriesIndex[$category->parent_uuid])) {
                    $category->parent_id = ArrayHelper::getValue(
                        $categoriesIndex,
                        $category->parent_uuid . '.id',
                        null
                    );
                    $category->save();
                    $i++;
                }
            }
        }
        print_r($i);
    }

    private function parseCategory($rawData)
    {
        if (isset($rawData['@attributes'])) {
            $hasChild = 0;
            if (isset($rawData['ПодчиненныеЭлементы'])) {
                $hasChild = 1;
            }

            $data = $rawData['@attributes'];
            $result = [];
            $result['uuid'] = str_replace(' ', '', ArrayHelper::getValue($data, 'УникальныйИД'));
            $result['name'] = ArrayHelper::getValue($data, 'Наименование');
            $result['is_view'] = self::boolean(ArrayHelper::getValue($data, 'ЭтоГруппаВидов'));
            $result['parent_uuid'] = str_replace(' ', '', ArrayHelper::getValue($data, 'УникальныйИДРодителя'));
            $result['has_child'] = $hasChild;
            $this->array[$result['uuid']] = $result;
            if ($hasChild) {
                $temp = (array)$rawData['ПодчиненныеЭлементы'];
                foreach ($temp as $value) {
                    $value = (array)$value;
                    if (count($value) == 1) {
                        $this->subCategory($value);
                    } else {
                        foreach ($value as $val) {
                            $val = (array)$val;
                            $this->subCategory($val);
                        }
                    }
                }
            }
        } elseif (isset($rawData['УникальныйИД'])) {
            $rawData = (array)$rawData;
            $hasChild = 0;
            if (isset($rawData['ПодчиненныеЭлементы'])) {
                $hasChild = 1;
            }
            $data = $rawData;
            $result = [];
            $result['uuid'] = str_replace(' ', '', ArrayHelper::getValue($data, 'УникальныйИД'));
            $result['name'] = ArrayHelper::getValue($data, 'Наименование');
            $result['is_view'] = self::boolean(ArrayHelper::getValue($data, 'ЭтоГруппаВидов'));
            $result['parent_uuid'] = str_replace(' ', '', ArrayHelper::getValue($data, 'УникальныйИДРодителя'));
            $result['has_child'] = $hasChild;
            $this->array[$result['uuid']] = $result;

            if ($hasChild) {
                $temp = (array)$rawData['ПодчиненныеЭлементы'];
                foreach ($temp as $value) {
                    $value = (array)$value;
                    if (count($value) == 1) {
                        $this->subCategory($value);
                    } else {
                        foreach ($value as $val) {
                            $val = (array)$val;
                            $this->subCategory($val);
                        }
                    }
                }
            }
        } else {
            if (isset($rawData['Категория'])) {
                foreach ($rawData['Категория'] as $data) {
                    $this->parseCategory($data);
                }
            }
        }
        return true;
    }

    private function subCategory($data)
    {
        $data = (array)$data;
        if (isset($data['Категория'])) {
            foreach ($data['Категория'] as $value) {
                $value = (array)$value;
                $this->subCategory($value);
            }
            return true;
        }
        $this->parseCategory($data);
        if (isset($data['ПодчиненныеЭлементы'])) {
            foreach ($data['ПодчиненныеЭлементы'] as $value) {
                if (count($value) == 1) {
                    $this->subCategory($value);
                } else {
                    foreach ($value as $val) {
                        $this->subCategory($val);
                    }
                }
            }
        }
    }

    private static function boolean($value)
    {
        if ($value == 'Истина') {
            return 1;
        } else {
            return 0;
        }
    }
}
