<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 03.07.2019
 * Time: 18:12
 */

namespace api\schema;

use common\models\CoordinationRoles;
use GraphQL\Type\Definition\Type;
use yii\helpers\ArrayHelper;

/**
 * Class CoordinationRolesType
 * @package api\schema
 */
class CoordinationRolesType extends BasicType
{
    public function __construct($pagination = false)
    {
        $config = [
            'fields' => function () {
                return [
                    'id' => Type::int(),
                    'coordination_id' => Type::int(),
                    'role' => Type::string(),
                    'name' => [
                        'type' => Type::string(),
                        'resolve' => function (CoordinationRoles $model) {
                            return ArrayHelper::getValue($model->authItem, 'description');
                        }
                    ]
                ];
            }
        ];
        parent::__construct($config, $pagination);
    }
}
