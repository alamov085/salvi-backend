<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 27.08.2019
 * Time: 12:22
 */

namespace api\schema;

use common\models\Chat;
use GraphQL\Type\Definition\Type;

/**
 * Class ChatInfoType
 * @package api\schema
 */
class ChatInfoType extends BasicType
{
    public function __construct($pagination = false)
    {
        $config = [
            'fields' => function () {
                return [
                    'id' => Type::int(),
                    'is_guest' => Type::boolean(),
                    'name' => [
                        'type' => Type::string(),
                        'resolve' => function (Chat $model) {
                            if ($model->is_guest) {
                                return $model->guestUser->name;
                            }
                            return $model->user->first_name . ' ' . $model->user->last_name;
                        }
                    ],
                    'email' => [
                        'type' => Type::string(),
                        'resolve' => function (Chat $model) {
                            if ($model->is_guest) {
                                return $model->guestUser->email;
                            }
                            return $model->user->email;
                        }
                    ],
                    'phone' => [
                        'type' => Type::string(),
                        'resolve' => function (Chat $model) {
                            if ($model->is_guest) {
                                return '+380' . $model->guestUser->phone;
                            } else {
                                if ($model->user->phone_number) {
                                    return '+380' . $model->user->phone_number;
                                }
                            }
                            return '';
                        }
                    ]
                ];
            }
        ];
        parent::__construct($config, $pagination);
    }
}
