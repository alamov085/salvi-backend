<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 20.07.2019
 * Time: 8:43
 */

namespace api\controllers\v1;

use api\controllers\RestController;
use common\models\Order;
use common\models\OrderFile;
use common\models\User;
use Throwable;
use Yii;
use yii\web\Response;

/**
 * Class FileController
 * @package api\controllers\v1
 */
class FileController extends RestController
{
    /**
     * @param $id
     * @return array|void
     * @throws Throwable
     */
    public function actionDownload($id)
    {
        if (Yii::$app->user->can('updateOrder')) {
            /** @var  $user User */
            $user = Yii::$app->user->getIdentity();
            $fileOrder = OrderFile::find()->where(['id' => $id])->one();
            if ($fileOrder != false) {
                $order = Order::find()
                    ->where(['id' => $fileOrder->order_id])
                    ->andWhere(['company_id' => $user->company_id])
                    ->one();
                if ($order != false) {
                    if (file_exists($fileOrder->filePath)) {
                        return Yii::$app->response->sendFile(
                            $fileOrder->filePath,
                            $fileOrder->old_name
                        )->send();
                    }
                }
            }
        }
        return $this->accessDenied();
    }
}
