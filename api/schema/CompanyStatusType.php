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
 * Class CompanyStatusType
 * @package api\schema
 */
class CompanyStatusType extends BasicType
{
    public function __construct($pagination = false)
    {
        $config = [
            'description' => 'Статусы предприятия',
            'fields' => function () {
                return [
                    'id' => Type::int(),
                    'name' => Type::string(),
                    'class' => Type::string(),
                    'icon' => [
                        'type' => Type::string(),
                        'resolve' => function ($model) {
                            return ArrayHelper::getValue($model, 'icon', '');
                        }
                    ],
                    'color' => [
                        'type' => Type::string(),
                        'resolve' => function ($model) {
                            return ArrayHelper::getValue($model, 'color', '');
                        }
                    ]
                ];
            }
        ];
        parent::__construct($config, $pagination);
    }
}
