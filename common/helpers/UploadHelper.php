<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 15.02.19
 * Time: 11:30
 */

namespace common\helpers;

use common\models\OrderFile;
use common\models\Product;
use common\models\ProductPhoto;
use Yii;
use yii\base\Exception;
use yii\helpers\FileHelper;
use yii\web\UploadedFile;

/**
 * Класс для сохранения файлов.
 *
 * Class UploadHelper
 * @package common\helpers
 */
class UploadHelper extends UploadedFile
{
    /**
     * @param $order_id
     * @param UploadedFile $file
     * @param $user_id
     * @return bool
     * @throws Exception
     */
    public static function saveDocument($order_id, UploadedFile $file, $user_id)
    {
        //Save document
        $newFileName = self::generateNewFilename($file);
        $path = self::getPathForSaveDocuments();

        $notFullPath = $path . DIRECTORY_SEPARATOR . $order_id . DIRECTORY_SEPARATOR;
        FileHelper::createDirectory($notFullPath);
        $fullPath = $notFullPath . $newFileName['new_name'] . '.' . $newFileName['ext'];
        $saved = $file->saveAs($fullPath);

        //Insert database data
        $document = new OrderFile([
            'order_id' => $order_id,
            'ext' => $newFileName['ext'],
            'created_at' => date('U'),
            'new_name' => $newFileName['new_name'],
            'old_name' => $newFileName['old_name'],
            'size' => $newFileName['size'],
            'user_id' => $user_id
        ]);
        $docSaved = $document->save();
        return $saved && $docSaved;
    }

    public static function saveProductPhoto(Product $product, UploadedFile $file)
    {
        //Save document
        $newFileName = self::generateNewFilename($file);
        $path = Yii::getAlias('@api/web/product');

        $notFullPath = $path . DIRECTORY_SEPARATOR;
        FileHelper::createDirectory($notFullPath);
        $fullPath = $notFullPath . $newFileName['new_name'] . '.' . $newFileName['ext'];
        $saved = $file->saveAs($fullPath);

        //Insert database data
        $document = new ProductPhoto([
            'name' => $newFileName['new_name'] . '.' . $newFileName['ext'],
            'product_uuid' => $product->uuid,
            'product_id' => $product->id
        ]);
        $docSaved = $document->save();
        return $saved && $docSaved;
    }

    /**
     * @param bool $aliasOnly
     * @return string
     */
    protected static function getPathForSaveDocuments($aliasOnly = false)
    {
        /**
         * Alias documents - look at common/bootstrap.
         *
         * @var string $path
         */
        $alias = '@files';
        if ($aliasOnly) {
            return $alias;
        }
        $path = Yii::getAlias($alias);
        return $path;
    }

    /**
     * @param UploadedFile $file
     * @return array
     * @throws Exception
     */
    public static function generateNewFilename(UploadedFile $file)
    {
        $fileExtension = $file->getExtension();
        $newFileName = Yii::$app->security->generateRandomString(12);
        return [
            'new_name' => $newFileName,
            'ext' => $fileExtension,
            'old_name' => $file->name,
            'size' => $file->size
        ];
    }
}
