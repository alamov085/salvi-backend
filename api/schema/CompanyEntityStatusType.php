<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 27.06.2019
 * Time: 12:50
 */

namespace api\schema;

use common\models\AuthItem;
use common\models\CompanyEntity;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

/**
 * Class CompanyEntityStatusType
 * @package api\schema
 */
class CompanyEntityStatusType extends BasicType
{
    public function __construct($pagination = false)
    {
        $config = [
            'fields' => function () {
                return [
                    'id' => Type::int(),
                    'name' => Type::string(),
                    'class' => Type::string()
                ];
            }
        ];
        parent::__construct($config, $pagination);
    }
}
