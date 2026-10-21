<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 06.12.2019
 * Time: 13:19
 */

namespace api\models\forms;

use api\helpers\ParseXlsx;
use api\job\ParseXlsxJob;
use common\helpers\UploadHelper;
use common\models\User;
use Yii;
use yii\base\Model;
use yii\helpers\FileHelper;

/**
 * Class ImportForm
 * @package api\models\forms
 */
class ImportForm extends Model
{
    const FILE_LOCATION = 1;
    const FILE_COST = 2;
    const FILE_BUDGET = 3;
    const FILE_USER = 4;
    const FILE_SPEC = 5;
    const FILE_PRICE = 6;
    const FILE_CATALOG = 7;
    const FILE_IMAGE = 8;

    public $files;
    public $id;

    public function rules()
    {
        return [
            [['id', 'files'], 'required'],
            [
                'files',
                'file',
                'maxFiles' => 1,
                'extensions' => 'xlsx',
                'checkExtensionByMimeType' => false,
//                'mimeTypes' => [
//                    'application/vnd.ms-office',
//                    'application/vnd.ms-excel',
//                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
//                ]
            ]
        ];
    }

    public function upload()
    {
        $path = Yii::getAlias('@api/web/import_files');
        $newFileName = UploadHelper::generateNewFilename($this->files);
        $name = $newFileName['old_name'];
        $notFullPath = $path . DIRECTORY_SEPARATOR;
        FileHelper::createDirectory($notFullPath);
        $fullPath = $notFullPath . $name;
        $saved = $this->files->saveAs($fullPath);
        if ($saved) {
            $user = User::findOne(['id' => Yii::$app->user->getId()]);
            Yii::$app->queue->push(new ParseXlsxJob([
                'id' => $this->id,
                'user' => $user,
                'name' => $name
            ]));
            return true;
        }
        return false;
    }
}
