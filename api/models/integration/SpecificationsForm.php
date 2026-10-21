<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 05.02.2020
 * Time: 14:53
 */

namespace api\models\integration;

use yii\base\Model;

/**
 * Class ProductsForm
 * @package api\models\integration
 */
class SpecificationsForm extends Model
{
    public $specifications;

    public function rules()
    {
        return [
            ['specifications', 'required'],
            ['specifications', 'safe'],
            ['specifications', 'checkJson']
        ];
    }

    /**
     * @return bool
     */
    public function checkJson()
    {
        $errors = [];
        if ($this->specifications) {
            foreach ($this->specifications as $k => $specification) {
                $model = new SpecificationForm();
                if ($model->load($specification, '') && !$model->validate()) {
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
        foreach ($this->specifications as $k => $specification) {
            $model = new SpecificationForm();
            if ($model->load($specification, '') && $model->validate() && $model->save()) {
                $success[] = $k;
            } else {
                $error[] = $k;
            }
        }
        return ['ok' => $success, 'fail' => $error];
    }
}
