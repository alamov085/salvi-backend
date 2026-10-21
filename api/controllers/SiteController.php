<?php

namespace api\controllers;

use Exception;
use Yii;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;

/**
 * Class SiteController
 * @package api\controllers
 */
class SiteController extends PublicController
{

    /**
     * @throws NotFoundHttpException
     */
    public function actionIndex()
    {
        throw new NotFoundHttpException();
    }

    /**
     * @throws NotFoundHttpException
     */
    public function actionError()
    {
        throw new NotFoundHttpException();
    }
}
