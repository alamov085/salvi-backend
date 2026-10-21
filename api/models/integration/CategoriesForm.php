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
 * Class CategoriesForm
 * @package api\models\integration
 */
class CategoriesForm extends Model
{
    public $categories;

    public function rules()
    {
        return [
            ['categories', 'required'],
            ['categories', 'safe'],
            ['categories', 'checkJson']
        ];
    }

    /**
     * @return bool
     */
    public function checkJson()
    {
        $errors = [];
        if ($this->categories) {
            foreach ($this->categories as $k => $category) {
                $model = new CategoryForm();
                if ($model->load($category, '') && !$model->validate()) {
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
        foreach ($this->categories as $k => $category) {
            $model = new CategoryForm();
            if ($model->load($category, '') && $model->validate() && $model->save()) {
                $success[] = $k;
            } else {
                $error[] = $k;
            }
        }
        return ['ok' => $success, 'fail' => $error];
    }
}
