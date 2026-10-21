<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 25.07.2019
 * Time: 17:35
 */

namespace api\schema;

use common\models\Company;
use GraphQL\Type\Definition\Type;

/**
 * Class CatalogLimitationType
 * @package api\schema
 */
class CatalogLimitationType extends BasicType
{
    public function __construct($pagination = false)
    {
        $config = [
            'fields' => function () {
                return [
                    'id' => Type::int(),
                    'short_name' => Type::string(),
                    'full_name' => Type::string(),
                    'total_categories' => [
                        'type' => Type::int(),
                        'resolve' => function (Company $model) {
                            return count($model->categories);
                        }
                    ]
                ];
            }
        ];
        parent::__construct($config, $pagination);
    }
}
