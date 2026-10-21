<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 23.07.2019
 * Time: 14:19
 */

namespace api\schema;

use common\models\Category;
use GraphQL\Type\Definition\Type;
use yii\helpers\ArrayHelper;

/**
 * Class CategoryType
 * @package api\schema
 */
class CategoryType extends BasicType
{
    public function __construct($pagination = false)
    {
        $config = [
            'fields' => function () {
                return [
                    'id' => Type::int(),
                    'uuid' => Type::string(),
                    'name' => Type::string(),
                    'is_view' => Type::boolean(),
                    'parent_uuid' => Type::string(),
                    'parent_id' => Type::int(),
                    'has_child' => Type::boolean(),
                    'is_view' => [
                        self::FIELD_TYPE => Type::boolean(),
                        self::FIELD_RESOLVE => function ($a) {
                            return true;
                        }
                    ],
                    'is_props' => [
                        self::FIELD_TYPE => Type::boolean(),
                        self::FIELD_RESOLVE => function ($a) {
                            return true;
                        }
                    ],
                    'is_props_value' => [
                        self::FIELD_TYPE => Type::boolean(),
                        self::FIELD_RESOLVE => function ($a) {
                            return true;
                        }
                    ],
                    'total_product' => [
                        'type' => Type::int(),
                        'resolve' => function (Category $model) {
                            return ArrayHelper::getValue($model, 'limitation.total_product', 0);
                        }
                    ],
                    'type' => Type::int(),
                    'sort' => Type::int(),
                    'color' => Type::string(),
                    'img_path' => Type::string(),
                ];
            }
        ];
        parent::__construct($config, $pagination);
    }
}
