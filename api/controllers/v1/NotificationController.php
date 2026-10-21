<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 01.10.2019
 * Time: 12:21
 */

namespace api\controllers\v1;

use api\controllers\RestController;
use common\models\Notification;
use common\models\User;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * Class NotificationController
 * @package api\controllers\v1
 */
class NotificationController extends RestController
{
    /**
     * @return array
     * @throws \Throwable
     */
    public function actionReadAll()
    {
        /** @var  $user User */
        $user = Yii::$app->user->getIdentity();
        Notification::updateAll(
            ['is_read' => 1, 'updated_at' => time()],
            [
                'and',
                ['user_id' => $user->id],
                ['is_read' => 0]
            ]
        );
        return $this->successResponse();
    }

    /**
     * @return array
     * @throws \Throwable
     */
    public function actionSetReadNotification()
    {
        /** @var  $user User */
        $user = Yii::$app->user->getIdentity();
        $id = ArrayHelper::getValue($this->post, 'id', 0);
        if (is_numeric($id) && $id) {
            $notification = Notification::find()
                ->where(['id' => $id])
                ->andWhere(['user_id' => $user->id])
                ->one();
            if ($notification) {
                $notification->is_read = 1;
                $notification->updated_at = time();
                if ($notification->save()) {
                    return $this->successResponse();
                }
                return $this->failResponse();
            }
        }
        return $this->accessDenied();
    }
}
