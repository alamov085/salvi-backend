<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 25.06.2019
 * Time: 21:03
 */

namespace api\schema;

use common\models\Specification;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

/**
 * Class PaginationType
 * @package api\schema
 */
class PaginationType extends ObjectType
{
    public function __construct(ObjectType $type)
    {
        $config = [
            'name' => $this->getName($type),
            'fields' => function () use ($type) {
                return [
                    'items' => [
                        'type' => Type::listOf($type),
                        'resolve' => function ($model) {
                            return $model['model'];
                        },
                    ],
                    'totalCount' => [
                        'type' => Type::int(),
                        'resolve' => function ($model) {
                            return $model['total'];
                        },
                    ]
                ];
            },
        ];
        parent::__construct($config);
    }

    public function getName(ObjectType $type)
    {
        $name = explode('_', $type->name);
        return $name[0] . '_pagiantion';
    }

    /**
     * @param $args
     * @param $arguments
     * @return array
     */
    public static function arguments($args, $arguments = [], $joinTables = [])
    {
        $result = [];
        if (isset($args['page'])) {
            $result['page'] = $args['page'];
            if ($result['page'] <= 1) {
                $result['page'] = 0;
            } else {
                $result['page'] -= 1;
            }
        } else {
            $result['page'] = 0;
        }

        if (isset($args['perPage'])) {
            $result['perPage'] = $args['perPage'];
            if ($result['perPage'] <= 0) {
                $result['perPage'] = 5;
            }
        } else {
            $result['perPage'] = 5;
        }

        $result['order'] = 'DESC';
        if (isset($args['order'])) {
            if (in_array($args['order'], ['DESC', 'ASC'])) {
                $result['order'] = $args['order'];
            }
        }

        $result['orderBy'] = 'id';
        if (isset($args['orderBy'])) {
            if (in_array($args['orderBy'], $arguments)) {
                $result['orderBy'] = $args['orderBy'];
            }
            if (!empty($joinTables)) {
                $explode = explode('.', $args['orderBy']);
                if (count($explode) > 1) {
                    foreach ($joinTables as $class) {
                        if ($class::tableName() == $explode[0]) {
                            $columns = array_keys($class::getTableSchema()->columns);
                            if (in_array($explode[1], $columns)) {
                                $result['orderBy'] = $args['orderBy'];
                            }
                        }
                    }
                }
            }
        }

        if (!empty($args)) {
            foreach ($args as $k => $value) {
                if (!in_array($k, ['orderBy', 'order', 'perPage', 'page', 'search'])) {
                    $result['where'][] = [$k => $value];
                }
            }
        }

        return $result;
    }

    /**
     * @return array
     */
    public static function argsDescription()
    {
        return [
            'page' => [
                'type' => Type::int(),
                'description' => 'Номер страницы, значение должно быть больше 0. По умолчанию - 1',
                'defaultValue' => 0
            ],
            'perPage' => [
                'type' => Type::int(),
                'description' => 'Количество выводимых записей. 
                                Значение должно быть больше 0. По умолчанию - 5',
                'defaultValue' => 5
            ],
            'orderBy' => [
                'type' => Type::string(),
                'description' => 'Столбец по которому идем сортировка. По умолчанию id'
            ],
            'order' => [
                'type' => Type::string(),
                'description' => 'Порядок сортировки. По умолчанию DESC'
            ],
            'search' => [
                'type' => Type::string(),
                'description' => 'Искомая строка'
            ]
        ];
    }
}
