<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 08.06.2019
 * Time: 15:35
 */

namespace api\schema;

use common\models\AuthItem;
use common\models\AuthItemParent;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use common\models\User;
use yii\helpers\ArrayHelper;

class AuthItemParentType extends BasicType
{
    public function __construct($pagination = false)
    {
        $config = [
            'fields' => function () {
                return [
                    'id' => Type::int(),
                    'name' => Type::string(),
                    'description' => Type::string(),
                    'children' => [
                        'type' => Type::listOf(Types::authItem()),
                        'resolve' => function (AuthItemParent $model) {
                            return $model->children;
                        },
                    ],
                ];
            }
        ];
        parent::__construct($config, $pagination);
    }
}
