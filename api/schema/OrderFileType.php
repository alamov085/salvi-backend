<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 20.07.2019
 * Time: 9:15
 */

namespace api\schema;

use common\models\OrderFile;
use GraphQL\Type\Definition\Type;
use Yii;

/**
 * Class OrderFileType
 * @package api\schema
 */
class OrderFileType extends BasicType
{
    public function __construct($pagination = false)
    {
        $config = [
            'fields' => function () {
                return [
                    'id' => Type::int(),
                    'order_id' => Type::int(),
                    'old_name' => Type::string(),
                    'new_name' => Type::string(),
                    'ext' => Type::string(),
                    'size' => [
                        'type' => Type::string(),
                        'resolve' => function (OrderFile $model) {
                            return Yii::$app->formatter->asShortSize($model->size, 2);
                        }
                    ],
                    'user_id' => Type::int(),
                    'created_at' => Type::int(),
                    'link' => [
                        'type' => Type::string(),
                        'resolve' => function (OrderFile $model) {
                            return Yii::$app->params['backUrl'] . '/files/' . $model->order_id . '/' .
                                $model->new_name . '.' . $model->ext;
                        }
                    ]
                ];
            }
        ];
        parent::__construct($config, $pagination);
    }
}
