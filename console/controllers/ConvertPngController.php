<?php

namespace console\controllers;

use common\helpers\ImageConvertHelper;
use common\models\Category;
use common\models\ProductPhoto;
use Yii;
use yii\caching\TagDependency;
use yii\console\Controller;

/**
 * Converts PNG images of categories and products to JPG and updates the stored filename.
 */
class ConvertPngController extends Controller
{
    public function actionIndex()
    {
       // $this->convertCategories();
        $this->convertProducts();
    }

    private function convertCategories()
    {

        $path = Yii::getAlias('@api') . '/web/category/';
        $categories = Category::find()->where(['not', ['img_path' => null]])->all();

        $changed = false;
        foreach ($categories as $category) {
            try {
                if (strtolower(pathinfo($category->img_path, PATHINFO_EXTENSION)) !== 'png') {
                    continue;
                }

                $oldFile = $path . $category->img_path;
                $newName = pathinfo($category->img_path, PATHINFO_FILENAME) . '.jpg';
                $newFile = $path . $newName;

                if (!ImageConvertHelper::convertPngToJpg($oldFile, $newFile)) {
                    echo "Category {$category->id}: failed to convert {$oldFile}\n";
                    continue;
                }

                $category->img_path = $newName;
                $category->save();
                unlink($oldFile);
                $changed = true;

                echo "Category {$category->id}: {$oldFile} -> {$newFile}\n";
            } catch (\Throwable $e) {
                echo "Category {$category->id}: error - {$e->getMessage()}\n";
            }
        }

        if ($changed) {
            TagDependency::invalidate(Yii::$app->cache, 'CategoryTreeAllCategories');
        }
    }

    private function convertProducts()
    {
        $path = Yii::getAlias('@api') . '/web/product/';
        $photos = ProductPhoto::find()->all();

        $changed = false;
        foreach ($photos as $photo) {
            try {
                if (strtolower(pathinfo($photo->name, PATHINFO_EXTENSION)) !== 'png') {
                    continue;
                }

                $oldFile = $path . $photo->name;
                $newName = pathinfo($photo->name, PATHINFO_FILENAME) . '.jpg';
                $newFile = $path . $newName;

                if (!ImageConvertHelper::convertPngToJpg($oldFile, $newFile)) {
                    echo "ProductPhoto {$photo->id}: failed to convert {$oldFile}\n";
                    continue;
                }

                $photo->name = $newName;
                $photo->save();
                unlink($oldFile);
                $changed = true;

                echo "ProductPhoto {$photo->id}: {$oldFile} -> {$newFile}\n";
            } catch (\Throwable $e) {
                echo "ProductPhoto {$photo->id}: error - {$e->getMessage()}\n";
            }
        }

        if ($changed) {
            $listCacheToClear = [
                'GetSeasonGoods',
                'CategoryPopular',
                'Get4BlockCategories',
                'GetCategoryPage',
                'GetProductByCategoryOnCategoryPage',
            ];
            foreach ($listCacheToClear as $item) {
                TagDependency::invalidate(Yii::$app->cache, $item);
            }
        }
    }
}
