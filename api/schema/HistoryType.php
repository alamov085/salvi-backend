<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 12.07.2019
 * Time: 17:44
 */

namespace api\schema;

use common\models\Contract;
use common\models\Coordination;
use GraphQL\Type\Definition\Type;
use yii\helpers\ArrayHelper;

/**
 * Class HistoryType
 * @package api\schema
 */
class HistoryType extends BasicType
{
    public function __construct($pagination = false)
    {
        $config = [
            'fields' => function () {
                return [
                    'id' => Type::int(),
                    'email' => Type::string(),
                    'role' => Type::string()
                ];
            }
        ];
        parent::__construct($config, $pagination);
    }
}
