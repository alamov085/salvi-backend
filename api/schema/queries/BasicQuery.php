<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 29.06.2019
 * Time: 13:34
 */

namespace api\schema\queries;

/**
 * Class BasicQuery
 * @package api\schema\queries
 */
abstract class BasicQuery implements QueryInterface
{
    /**
     * @return array
     */
    public function getData()
    {
        return [
            'type' => $this->type(),
            'args' => $this->args(),
            'resolve' => $this->resolve()
        ];
    }

    /**
     * @return array
     */
    public static function accessDeniedPagination()
    {
        return [
            'model' => [],
            'total' => 0,
        ];
    }
}
