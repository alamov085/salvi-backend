<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 08.06.2019
 * Time: 17:26
 */

namespace backend\schema;

use yii\base\Model;

/**
 * Class ErrorsObject
 * @package backend\schema
 */
class ErrorsObject
{
    private $errors = [];

    /**
     * ErrorsObject constructor.
     * @param $model
     */
    public function __construct($model)
    {
        if ($model instanceof Model) {
            foreach ($model->getErrors() as $field => $messages) {
                $this->errors[] = [
                    'field' => $field,
                    'messages' => $messages,
                ];
            }
        }
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }
}
