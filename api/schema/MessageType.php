<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 21.08.2019
 * Time: 10:36
 */

namespace api\schema;

use common\models\ChatMessage;
use common\models\User;
use GraphQL\Type\Definition\Type;
use Yii;

/**
 * Class MessageType
 * @package api\schema
 */
class MessageType extends BasicType
{
    public function __construct($pagination = false)
    {
        $config = [
            'fields' => function () {
                return [
                    'id' => Type::int(),
                    'type' => [
                        'type' => Type::string(),
                        'resolve' => function (ChatMessage $model) {
                            return 'text';
                        }
                    ],
                    'author' => [
                        'type' => Type::string(),
                        'resolve' => function (ChatMessage $model) {
                            if (Yii::$app->user->isGuest && $model->from_guest) {
                                return 'me';
                            } else {
                                /** @var  $currentUser User */
                                $currentUser = Yii::$app->user->getIdentity();
                                if ($currentUser->id == $model->user_id) {
                                    return 'me';
                                } else {
                                    return $model->user_id;
                                }
                            }
                        }
                    ],
                    'user' => [
                        'type' => Type::string(),
                        'resolve' => function (ChatMessage $model) {
                            if ($model->from_guest) {
                                return $model->guestUser->name;
                            } else {
                                if ($model->user_id == Yii::$app->user->getId()) {
                                    return 'Ви';
                                } else {
                                    return $model->user->first_name . ' ' . $model->user->last_name;
                                }
                            }
                        }
                    ],
                    'typeId' => [
                        'type' => Type::int(),
                        'resolve' => function (ChatMessage $model) {
                            if ($model->user_id == Yii::$app->user->getId()) {
                                return 1;
                            }
                            return 0;
                        }
                    ],
                    'data' => [
                        'type' => Types::text(),
                        'resolve' => function (ChatMessage $model) {
                            return ['text' => $model->message];
                        }
                    ],
                    'text' => [
                        'type' => Type::string(),
                        'resolve' => function (ChatMessage $model) {
                            return $model->message;
                        }
                    ],
                    'created_at' => Type::int()
                ];
            }];
        parent::__construct($config, $pagination);
    }
}
