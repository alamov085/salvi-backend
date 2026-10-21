<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 08.10.2019
 * Time: 12:30
 */

namespace common\helpers\xml;

use common\models\Attribute;
use common\models\AttributeValue;
use common\models\Category;
use common\models\Product;
use common\models\ProductAttribute;
use common\models\ProductCategory;
use common\models\ProductPhoto;
use common\models\Unit;
use common\models\Vendor;
use SimpleXMLElement;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;

/**
 * Class CatalogParse
 * @package common\helpers\xml
 */
class CatalogParse
{
    public $xml;
    public $categories;
    public $params;

    /**
     * CatalogParse constructor.
     * @param SimpleXMLElement $el
     */
    public function __construct(SimpleXMLElement $el)
    {
        $this->xml = $el;
    }

    public function readNode()
    {
        if (!empty($this->xml)) {
            $temp = (array)$this->xml;
            $productUuid = ArrayHelper::getValue($temp, '@attributes.uid');
            $product = Product::find()->where(['uuid' => $productUuid])->one();
            if ($product) {
                $product->delete = Product::STATUS_ACTIVE;
                $product->save();
            }
        }
    }
}
