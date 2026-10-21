<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 29.06.2019
 * Time: 14:32
 */

namespace api\schema\queries;

/**
 * Class QueryInterface
 * @package api\schema\queries
 */
interface QueryInterface
{
    public function type();
    public function args();
    public function resolve();
}
