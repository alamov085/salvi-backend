<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 25.07.2019
 * Time: 16:40
 */

namespace api\controllers\v1;

use api\controllers\RestController;
use api\helpers\SpecificationHelper;
use api\models\forms\CatalogLimitationForm;
use Yii;
use yii\base\InvalidConfigException;

/**
 * Class CatalogLimitationController
 * @package api\controllers\v1
 */
class CatalogLimitationController extends RestController
{
    /**
     * @return CatalogLimitationForm|array
     * @throws InvalidConfigException
     */
    public function actionCreate()
    {
        if (Yii::$app->user->can('catalogLimitationCreate')) {
            $model = new CatalogLimitationForm();
            if ($model->load(Yii::$app->getRequest()->getBodyParams(), '') &&
                $model->validate() &&
                $model->create()) {
                return $this->successResponse();
            }
            $model->validate();
            return $model;
        }
        return $this->accessDenied();
    }

    /**
     * @return CatalogLimitationForm|array
     * @throws InvalidConfigException
     */
    public function actionUpdate()
    {
        if (Yii::$app->user->can('catalogLimitationUpdate')) {
            $model = new CatalogLimitationForm();
            if ($model->load(Yii::$app->getRequest()->getBodyParams(), '')) {
                if ($model->company && $model->validate() &&
                    $model->update()) {
                    (new SpecificationHelper($model->companies[0]))->init();
                    return $this->successResponse();
                } elseif (!$model->company && $model->validate() &&
                    $model->update()) {
                    return $this->successResponse();
                }
            }
            $model->validate();
            return $model;
        }
        return $this->accessDenied();
    }
}
