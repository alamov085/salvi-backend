<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 18.06.2020
 * Time: 11:28
 */

namespace api\models\integration;

use common\models\Product;
use yii\base\Model;

/**
 * Class UpdatePriceForm
 * @package api\models\integration
 */
class UpdatePriceForm extends Model
{
    public $uuid;
    public $price;

    public function rules()
    {
        return [
            [['uuid', 'price'], 'required'],
            ['price', 'number'],
            ['uuid', 'string']
        ];
    }

    /**
     * @return bool
     */
    public function save()
    {
        $product = Product::find()
            ->where(['uuid' => $this->uuid])
            ->one();
        if ($product) {
            $product->buy_price = $this->price;
            return $product->save();
        }
        return false;
    }
}
