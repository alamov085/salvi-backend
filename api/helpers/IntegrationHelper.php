<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 05.02.2020
 * Time: 12:48
 */

namespace api\helpers;

use common\models\Product;
use common\models\ProductAttribute;
use common\models\ProductPhoto;
use Exception;
use Ramsey\Uuid\Uuid;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * Class IntegrationHelper
 * @package api\helpers
 */
class IntegrationHelper
{
    /**
     * @param Product $product
     * @return array
     */
    public static function getUnit(Product $product)
    {
        $unit = [];
        if ($product->unit) {
            $unit = [
                'uuid' => $product->unit->uuid,
                'name' => $product->unit->name
            ];
        }
        return $unit;
    }

    /**
     * @param Product $product
     * @return array
     */
    public static function getUktzed(Product $product)
    {
        $uktzed = [];
        if ($product->productUktzed) {
            $uktzed = [
                'uuid' => $product->productUktzed->uuid,
                'short_name' => $product->productUktzed->short_name,
                'full_name' => $product->productUktzed->full_name,
                'type' => $product->productUktzed->type,
            ];
        }
        return $uktzed;
    }

    /**
     * @param Product $product
     * @return array
     */
    public static function getVendor(Product $product)
    {
        $vendor = [];
        if ($product->vendor) {
            $vendor = [
                'uuid' => $product->vendor->uuid,
                'name' => $product->vendor->name
            ];
        }
        return $vendor;
    }

    /**
     * @param Product $product
     * @return array
     */
    public static function getBrand(Product $product)
    {
        $brand = [];
        if ($product->productBrand) {
            $brand = [
                'uuid' => $product->productBrand->uuid,
                'name' => $product->productBrand->name,
                'vendor_uuid' => $product->productBrand->vendor_uuid
            ];
        }
        return $brand;
    }

    /**
     * @param Product $product
     * @return array
     */
    public static function getImage(Product $product)
    {
        return ($product->images)
            ? ArrayHelper::getColumn($product->images, function (ProductPhoto $image) {
                return Yii::$app->urlManager->createAbsoluteUrl(['/product/' . $image->name]);
            })
            : [];
    }

    /**
     * @param Product $product
     * @return mixed|null
     */
    public static function getType(Product $product)
    {
        return ($product->categories)
            ? ArrayHelper::getValue($product->categories, '0.type')
            : null;
    }

    /**
     * @param Product $product
     * @return mixed|null
     */
    public static function getCategory(Product $product)
    {
        return ($product->categoriesNew)
            ? ArrayHelper::getValue(ArrayHelper::index($product->categoriesNew, 'has_child'), '0.uuid')
            : null;
    }

    /**
     * @param Product $product
     * @return array
     */
    public static function getProps(Product $product)
    {
        $props = [];
        if ($product->productAttribute) {
            foreach ($product->productAttribute as $item) {
                /** @var $item ProductAttribute */
                $props[] = [
                    'id_attr' => $item->attribute_id,
                    'attr' => ($item->attr) ? $item->attr->name : null,
                    'id_value' => $item->value_id,
                    'value' => ($item->value) ? $item->value->name : null
                ];
            }
        }
        return $props;
    }

    /**
     * @param bool $with_timestamp
     * @return string
     * @throws \yii\base\Exception
     */
    public static function generateUuid($with_timestamp = false)
    {
        try {
            $uuidInstance = Uuid::uuid1();
            $uuidString = $uuidInstance->toString();
        } catch (Exception $e) {
            Yii::error($e->getMessage());
            $uuidString = Yii::$app->security->generateRandomString(12);
        }
        if ($with_timestamp) {
            $uuidString .= '_' . time();
        }
        return $uuidString;
    }
}
