<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 03.02.2020
 * Time: 10:04
 */

namespace api\schema;

use common\models\AdminEmail;
use GraphQL\Type\Definition\Type;
use yii\helpers\ArrayHelper;

/**
 * Class AdminEmailType
 * @package api\schema
 */
class AdminEmailType extends BasicType
{
    public function __construct($pagination = false)
    {
        $config = [
            'fields' => function () {
                return [
                    'id' => Type::int(),
                    'email' => Type::string(),
                    'status' => Type::int(),
                    'status_name' => [
                        self::FIELD_TYPE => Type::string(),
                        self::FIELD_RESOLVE => function (AdminEmail $model) {
                            return ArrayHelper::getValue(AdminEmail::statuses(), $model->status, '');
                        }
                    ],
                    'created_at' => Type::int(),
                    'updated_at' => Type::int(),
                ];
            }
        ];
        parent::__construct($config, $pagination);
    }
}
