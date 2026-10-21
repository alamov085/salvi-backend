<?php

namespace api\controllers;

/**
 * Created by PhpStorm.
 * User: tik
 * Date: 21.05.2019
 * Time: 18:49
 */

/**
 * Class PublicController
 */
class PublicController extends RestController
{
    /**
     * @return array
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        unset($behaviors["authenticator"]);
        return $behaviors;
    }
}
