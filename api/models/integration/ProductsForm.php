<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 05.02.2020
 * Time: 14:53
 */

namespace api\models\integration;

use yii\base\Model;
use Yii;

/**
 * Class ProductsForm
 * @package api\models\integration
 */
class ProductsForm extends Model
{
    public $products;

    public function rules()
    {
        return [
            ['products', 'required'],
            ['products', 'safe'],
            ['products', 'checkJson']
        ];
    }

    /**
     * @return bool
     */
    public function checkJson()
    {
        $errors = [];
        if ($this->products) {
            foreach ($this->products as $k => $product) {
                $model = new ProductForm();
                if ($model->load($product, '') && !$model->validate()) {
                    $errors[$k] = $model->errors;
                }
            }
        }
        if ($errors) {
            $this->addErrors($errors);
            return false;
        }
    }

    /**
     * @return array
     */
    public function save()
    {
        $success = [];
        $error = [];
        foreach ($this->products as $k => $product) {
            $model = new ProductForm();
            if ($model->load($product, '') && $model->validate() && $model->save()) {
                $success[] = $k;
            } else {
                $error[] = $k;
            }
        }

        Yii::$app->cache->delete('CategoryPopular');

        return ['ok' => $success, 'fail' => $error];
    }
}
