<?php

namespace api\models\integration;

use yii\base\Model;

/**
 * Class UpdatePricesForm
 * @package api\models\integration
 */
class UpdatePricesForm extends Model
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
                $model = new UpdatePriceForm();
                if ($model->load($product, '') && !$model->validate()) {
                    $errors[$k] = $model->errors;
                }
            }
        }
        if ($errors) {
            $this->addErrors($errors);
            return false;
        }
        return  true;
    }

    /**
     * @return array
     */
    public function save()
    {
        $success = [];
        $error = [];
        foreach ($this->products as $k => $product) {
            $model = new UpdatePriceForm();
            if ($model->load($product, '') && $model->validate() && $model->save()) {
                $success[] = $k;
            } else {
                $error[] = $k;
            }
        }
        return ['ok' => $success, 'fail' => $error];
    }
}
