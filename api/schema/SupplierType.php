<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 08.07.2019
 * Time: 14:08
 */

namespace api\schema;

use common\models\Supplier;
use GraphQL\Type\Definition\Type;

/**
 * Class SupplierType
 * @package api\schema
 */
class SupplierType extends BasicType
{
    public function __construct($pagination = false)
    {
        $config = [
            'fields' => function () {
                return [
                    'id' => Type::int(),
                    'name' => Type::string(),
                    'edrpou' => Type::string(),
                    'inn' => Type::string(),
                    'description' => Type::string(),
                    'status' => Type::int(),
                    'companySupplier' => [
                        self::FIELD_TYPE => Type::listOf(Types::companySupplier()),
                        self::FIELD_RESOLVE => function (Supplier $model) {
                            return $model->companySupplier;
                        }
                    ],
                    'history' => Types::history()
                ];
            }
        ];
        parent::__construct($config, $pagination);
    }
}
