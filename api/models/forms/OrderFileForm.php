<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 19.07.2019
 * Time: 15:31
 */

namespace api\models\forms;

use api\helpers\NotificationHelper;
use common\helpers\UploadHelper;
use common\models\Order;
use common\models\User;
use yii\base\Model;

/**
 * Class OrderFileForm
 * @package api\models\forms
 */
class OrderFileForm extends Model
{
    public $order_id;
    public $files;

    public function rules()
    {
        return [
            [['order_id'], 'required'],
            [['files'], 'file', 'skipOnEmpty' => false, 'checkExtensionByMimeType' => false,
                'extensions' => 'png, jpg, txt, pdf, jpeg, rar, zip, doc, docx, csv, xls, xlsx', 'maxFiles' => 4]
        ];
    }

    public function upload()
    {
        /** @var  $user User */
        $user = \Yii::$app->user->getIdentity();
        $order = Order::findOne(['id' => $this->order_id]);
        foreach ($this->files as $file) {
            UploadHelper::saveDocument($this->order_id, $file, $user->id);
            NotificationHelper::addFileToOrder($order, $user, $file->name);
            NotificationHelper::addFileToOrderOwner($order, $user, $file->name);
        }
        return true;
    }
}
