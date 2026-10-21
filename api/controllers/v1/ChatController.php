<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 20.08.2019
 * Time: 11:42
 */

namespace api\controllers\v1;

use api\helpers\NotificationHelper;
use api\models\forms\MessageForm;
use common\models\Chat;
use common\models\ChatMessage;
use common\models\ChatMessageRead;
use common\models\Notification;
use Yii;
use yii\base\InvalidConfigException;
use yii\filters\AccessControl;
use yii\filters\auth\HttpBearerAuth;
use yii\helpers\ArrayHelper;

class ChatController extends \api\controllers\PublicController
{
    const USER_ID = 'user_id';
    const CHAT_ID = 'chat_id';

    /**
     * @return array
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::class,
            'optional' => ['send-message', 'read-message']
        ];
        return $behaviors;
    }

    /**
     * @return MessageForm|array
     * @throws InvalidConfigException
     */
    public function actionSendMessage()
    {
        $model = new MessageForm();
        if (Yii::$app->user->isGuest &&
            !ArrayHelper::getValue($this->post, self::USER_ID, false)) {
            $model->scenario = 'register_guest';
        }
        if ($model->load(Yii::$app->getRequest()->getBodyParams(), '') &&
            $model->validate()) {
            $result = $model->sendMessage();
            if ($result['result']) {
                unset($result['result']);
                return $this->successResponse(['chat' => $result]);
            }
        }
        $model->validate();
        return $model;
    }

    /**
     * @return array
     */
    public function actionReadMessage()
    {
        $user_id = ArrayHelper::getValue($this->post, self::USER_ID, 0);
        $chat_id = ArrayHelper::getValue($this->post, self::CHAT_ID, 0);
        ChatMessageRead::updateAll(
            ['is_read' => 1],
            [
                'and',
                [self::CHAT_ID => $chat_id],
                [self::USER_ID => $user_id]
            ]
        );
        return $this->successResponse();
    }

    /**
     * @return array
     */
    public function actionCloseChat()
    {
        $id = ArrayHelper::getValue($this->post, 'id', 0);
        $model = Chat::find()->where(['id' => $id])->one();
        $model->status = Chat::STATUS_CLOSED;
        if ($model->save()) {
            ChatMessageRead::updateAll(['is_read' => 1], [self::CHAT_ID => $model->id]);
            $message = new ChatMessage([
                'message' => 'Діалог завершено',
                'user_id' => Yii::$app->user->getId(),
                'chat_id' => $id,
                'from_guest' => 0
            ]);
            $message->save();
            NotificationHelper::chatWasClosed($id);
            return $this->successResponse();
        }
        return $this->accessDenied();
    }

    /**
     * @return array
     */
    public function actionCloseNotification()
    {
        $id = ArrayHelper::getValue($this->post, 'id', 0);
        $model = Notification::find()->where(['id' => $id])->one();
        $model->status = Notification::STATUS_HIDE;
        if ($model->save()) {
            return $this->successResponse();
        }
        return $this->failResponse();
    }
}
