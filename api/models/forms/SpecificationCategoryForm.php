<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 21.11.2019
 * Time: 17:41
 */

namespace api\models\forms;

use common\models\Product;
use common\models\ProductCategory;
use common\models\Specification;
use common\models\SpecificationProduct;
use yii\base\Model;
use yii\helpers\ArrayHelper;

/**
 * Class SpecificationCategoryForm
 * @package api\models\forms
 */
class SpecificationCategoryForm extends Model
{
    public $id;
    public $category = [];

    private $products;

    public function rules()
    {
        return [
            [['id', 'category'], 'required']
        ];
    }

    public function addCategory()
    {
        $first = [];
        $second = [];
        foreach ($this->category as $category) {
            if (!isset($category['children'])) {
                $first[] = $category;
            } else {
                $second[] = $category;
            }
        }
        \Yii::beginProfile('product');
        $this->makeArray($first);
        if (!empty($second)) {
            $this->makeArray($second);
        }
        \Yii::endProfile('product');
        $specification = Specification::find()->where(['id' => $this->id])->one();
        if ($specification) {
            \Yii::beginProfile('addProduct');
            $result = $specification->addProducts($this->products);
            \Yii::endProfile('addProduct');
            SpecificationProduct::recalculateAvailable($this->id);
            return $result;
        }
        return false;
    }

    private function makeArray($first)
    {
        $notId = Product::find()
            ->select('uuid')
            ->where(['deleted' => 1])
            ->orWhere(['is_buffer' => 1])
            ->asArray()
            ->all();
        if (!empty($first)) {
            $productCategory = ProductCategory::find()
                ->select(['product_uuid', 'product_id', 'category_uuid'])
                ->where(['in', 'category_uuid', ArrayHelper::getColumn($first, 'uuid')])
                ->andWhere(['not in', 'product_uuid', ArrayHelper::getColumn($notId, 'uuid')])
                ->asArray()
                ->groupBy(['product_uuid'])
                ->all();
            foreach ($first as $category) {
                if ($productCategory) {
                    foreach ($productCategory as $k => $product) {
                        if ($product['category_uuid'] == $category['uuid'] &&
                            !isset($this->products[$product['product_uuid']])) {
                            $this->products[$product['product_uuid']] = [
                                'uuid' => $product['product_uuid'],
                                'id' => $product['product_id'],
                                'markup' => $category['markup']
                            ];
                            unset($productCategory[$k]);
                        }
                    }
                }
            }
        }
    }
}
