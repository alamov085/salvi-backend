<?php

namespace api\job;

use common\helpers\ImageConvertHelper;
use common\models\Category;
use Ramsey\Uuid\Uuid;
use Yii;
use yii\base\BaseObject;
use yii\helpers\FileHelper;
use yii\queue\JobInterface;
use yii\queue\Queue;
use yii\caching\TagDependency;

class UploadPhotoCategory extends BaseObject implements JobInterface {

    public $category;
    public $image;

    public function execute($queue) {
        $this->uploadImage($this->image);
    }

    private function uploadImage(string $url) {
        $chunks = explode('/', $url);
        $chunks = array_reverse($chunks);

        if (!isset($chunks[0])) {
            return false;
        }

        try {
            $mode = FTP_ASCII;
            $asynchronous = false;
            $name = Uuid::uuid4()->toString() . '.' . pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION);
            $path = Yii::getAlias('@api') . '/web/category/';

            FileHelper::createDirectory($path);

            $fileFullPath = $path . $name;

            Yii::$app->ftp->get('/upload/treeicons/' . $chunks[0], $fileFullPath, $mode, $asynchronous);

            if (!file_exists($fileFullPath) || filesize($fileFullPath) === 0) {
                Yii::error('UploadPhotoCategory: downloaded file is missing or empty, keeping old image: ' . $fileFullPath);
                return false;
            }

            Yii::$app->ftp->delete('/upload/treeicons/' . $chunks[0]);

            if (strtolower(pathinfo($name, PATHINFO_EXTENSION)) === 'png') {
                $jpgName = pathinfo($name, PATHINFO_FILENAME) . '.jpg';
                $jpgFullPath = $path . $jpgName;
                if (ImageConvertHelper::convertPngToJpg($fileFullPath, $jpgFullPath)) {
                    unlink($fileFullPath);
                    $name = $jpgName;
                    $fileFullPath = $jpgFullPath;
                }
            }

            $categoryDb = Category::find()->where(['uuid' => $this->category->uuid])->one();

            if (!$categoryDb) {
                return false;
            }

            $oldFile = Yii::getAlias('@api') . '/web/category/' . $categoryDb->img_path;

            $categoryDb->img_path = $name;
            $categoryDb->save();

            if ($categoryDb->img_path && file_exists($oldFile)) {
                try {
                    unlink($oldFile);
                } catch (\Exception $e) {
                    Yii::error($e->getMessage());
                }
            }

            TagDependency::invalidate(
                Yii::$app->cache,
                'CategoryTreeAllCategories'
            );
        } catch (\Exception $e) {
            Yii::error($e->getMessage());
        }
    }
}
