<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 30.07.2019
 * Time: 18:41
 */

namespace common\components;

/**
 * Class Permission
 * @package common\components
 */
class Permission extends \yii\rbac\Permission
{
    public $parent_id;
}
