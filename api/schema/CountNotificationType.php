<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 09.09.2019
 * Time: 16:55
 */

namespace api\schema;

use GraphQL\Type\Definition\Type;
use yii\helpers\ArrayHelper;

/**
 * Class CountNotificationType
 * @package api\schema
 */
class CountNotificationType extends BasicType
{
    public function __construct($pagination = false)
    {
        $config = [
            'fields' => function () {
                return [
                    'count' => [
                        self::FIELD_TYPE => Type::int(),
                        self::FIELD_RESOLVE => function ($model) {
                            return ArrayHelper::getValue($model, 'total', 0);
                        }
                    ],
                    'chat' => [
                        self::FIELD_TYPE => Type::int(),
                        self::FIELD_RESOLVE => function ($model) {
                            return ArrayHelper::getValue($model, 'chat', 0);
                        }
                    ],
                    'notification' => [
                        self::FIELD_TYPE => Type::int(),
                        self::FIELD_RESOLVE => function ($model) {
                            return ArrayHelper::getValue($model, 'notification', 0);
                        }
                    ]
                ];
            }
        ];
        parent::__construct($config, $pagination);
    }
}
