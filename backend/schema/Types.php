<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 08.06.2019
 * Time: 15:31
 */

namespace backend\schema;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\UnionType;
use yii\base\Model;
use backend\schema\mutations\UserMutationType;

/**
 * Class Types
 * @package backend\schema
 */
class Types
{
    private static $query;
    private static $mutation;
    private static $user;
    private static $currency;

    private static $userMutation;

    private static $validationError;
    private static $validationErrorsList;
    private static $validationTypes;

    /**
     * @return QueryType
     */
    public static function query()
    {
        return self::$query ?: (self::$query = new QueryType());
    }

    /**
     * @return MutationType
     */
    public static function mutation()
    {
        return self::$mutation ?: (self::$mutation = new MutationType());
    }

    /**
     * @return UserType
     */
    public static function user()
    {
        return self::$user ?: (self::$user = new UserType());
    }

    /**
     * @return CurrencyType
     */
    public static function currency()
    {
        return self::$currency ?: (self::$currency = new CurrencyType());
    }

    /**
     * @return UserMutationType
     */
    public static function userMutation()
    {
        return self::$userMutation ?: (self::$userMutation = new UserMutationType());
    }

    /**
     * @return ValidationErrorType
     */
    public static function validationError()
    {
        return self::$validationError ?: (self::$validationError = new ValidationErrorType());
    }

    /**
     * @return ValidationErrorsListType
     */
    public static function validationErrorsList()
    {
        return self::$validationErrorsList ?: (self::$validationErrorsList = new ValidationErrorsListType());
    }

    /**
     * @param ObjectType $type
     * @return mixed
     */
    public static function validationErrorsUnionType(ObjectType $type)
    {
        if (!isset(self::$validationTypes[$type->name . 'ValidationErrorsType'])) {
            self::$validationTypes[$type->name . 'ValidationErrorsType'] = new UnionType([
                'name' => $type->name . 'ValidationErrorsType',
                'types' => [
                    $type,
                    Types::validationErrorsList(),
                ],
                'resolveType' => function ($value) use ($type) {
                    if ($value instanceof Model) {
                        return $type;
                    } else {
                        return Types::validationErrorsList();
                    }
                }
            ]);
        }
        return self::$validationTypes[$type->name . 'ValidationErrorsType'];
    }
}
