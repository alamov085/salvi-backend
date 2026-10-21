<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 08.06.2019
 * Time: 17:19
 */

namespace backend\schema\mutations;

use backend\schema\ErrorsObject;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use common\models\User;
use backend\schema\Types;

/**
 * Class UserMutationType
 * @package backend\schema\mutations
 */
class UserMutationType extends ObjectType
{
    /**
     * UserMutationType constructor.
     */
    public function __construct()
    {
        $config = [
            'fields' => function () {
                return [
                    'update' => [
                        'type' => Types::validationErrorsUnionType(Types::user()),
                        'description' => 'Update user data.',
                        'args' => [
                            'first_name' => Type::string(),
                            'last_name' => Type::string(),
                            'currency_id' => Type::string()
                        ],
                        'resolve' => function (User $user, $args) {
                            $user->setAttributes($args);
                            if ($user->save()) {
                                return $user;
                            } else {
                                return ['errors' => (new ErrorsObject($user))->getErrors()];
                            }
                        }
                    ],
                ];
            }
        ];
        parent::__construct($config);
    }
}
