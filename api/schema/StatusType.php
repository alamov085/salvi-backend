<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 27.06.2019
 * Time: 12:50
 */

namespace api\schema;

use common\models\AuthItem;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use yii\helpers\ArrayHelper;

/**
 * Class StatusType
 * @package api\schema
 */
class StatusType extends BasicType
{
    public function __construct($pagination = false)
    {
        $config = [
            'description' => 'Статусы',
            'fields' => function () {
                return [
                    'id' => Type::int(),
                    'name' => Type::string(),
                    'class' => [
                        self::FIELD_TYPE => Type::string(),
                        self::FIELD_RESOLVE => function ($model) {
                            return ArrayHelper::getValue($model, 'class', '');
                        }
                    ],
                ];
            }
        ];
        parent::__construct($config, $pagination);
    }
}
