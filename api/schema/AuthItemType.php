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
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use common\models\User;
use Yii;

class AuthItemType extends BasicType
{
    public function __construct($pagination = false)
    {
        $config = [
            'fields' => function () {
                return [
                    'name' => Type::string(),
                    'type' => [
                        'type' => Type::int(),
                        'description' => '1 - роль, 2 - права доступа'
                    ],
                    'description' => Type::string(),
                    'company_id' => Type::int(),
                    'parent' => [
                        'type' => Types::authItemParent(),
                        'resolve' => function (AuthItem $model) {
                            return $model->parentGroup;
                        },
                    ],
                    'parent_id' => Type::int(),
                    'status' => Type::int(),
                    'permission' => [
                        'type' => Type::listOf(Types::authItem()),
                        'resolve' => function (AuthItem $model) {
                            return Yii::$app->authManager->getPermissionsByRole($model->name);
                        }
                    ],
                    'default_role' => Type::boolean(),
                    'history' => Types::history()
                ];
            }
        ];
        parent::__construct($config, $pagination);
    }
}
