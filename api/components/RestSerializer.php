<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 24.05.2019
 * Time: 18:52
 */

namespace api\components;

use yii\base\Model;
use yii\rest\Serializer;

/**
 * Class RestSerializer
 * @package api\components
 */
class RestSerializer extends Serializer
{
    /**
     * @param Model $model
     * @return array
     */
    protected function serializeModelErrors($model)
    {
        $this->response->setStatusCode(200, '');
        $result = [];
        $result['success'] = false;
        $result['errors'] = [];
        foreach ($model->getFirstErrors() as $name => $message) {
            $result['errors'][] = [
                'field' => $name,
                'message' => $message,
            ];
        }
        return $result;
    }
}
