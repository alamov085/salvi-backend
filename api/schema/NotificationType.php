<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 27.08.2019
 * Time: 9:54
 */

namespace api\schema;

use common\models\Chat;
use common\models\Notification;
use GraphQL\Type\Definition\Type;

/**
 * Class NotificationType
 * @package api\schema
 */
class NotificationType extends BasicType
{
    public function __construct($pagination = false)
    {
        $config = [
            'fields' => function () {
                return [
                    'id' => Type::int(),
                    'type' => Type::int(),
                    'user' => Type::string(),
                    'date' => Type::int(),
                    'text' => Type::string(),
                    'is_read' => Type::boolean()
                ];
            }
        ];
        parent::__construct($config, $pagination);
    }
}
