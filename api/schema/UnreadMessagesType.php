<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 21.08.2019
 * Time: 15:19
 */

namespace api\schema;

use GraphQL\Type\Definition\Type;

/**
 * Class UnreadMessagesType
 * @package api\schema
 */
class UnreadMessagesType extends BasicType
{
    public function __construct($pagination = false)
    {
        $config = [
            'description' => 'Непрочитаные сообщения',
            'fields' => function () {
                return [
                    'count' => Type::int(),
                ];
            }
        ];
        parent::__construct($config, $pagination);
    }
}