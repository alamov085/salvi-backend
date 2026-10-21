<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 06.12.2019
 * Time: 13:16
 */

namespace api\controllers\v1;

use api\controllers\RestController;
use api\models\forms\ImportForm;
use Yii;
use yii\web\UploadedFile;

/**
 * Class ImportController
 * @package api\controllers\v1
 */
class ImportController extends RestController
{
    public function actionIndex()
    {
        if (Yii::$app->user->can('importModel') || Yii::$app->user->can('managementImportCatalog') ||
            Yii::$app->user->can('managementImportPrice') || Yii::$app->user->can('managementImportImage')) {
            $model = new ImportForm();
            if ($model->load(Yii::$app->request->post(), '')) {
                $model->files = UploadedFile::getInstanceByName('files');
                if ($model->validate()) {
                    $model->upload();
                    return $this->successResponse();
                }
            }
            $model->validate();
            return $model;
        }
        return $this->accessDenied();
    }
}
