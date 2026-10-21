<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 17.10.2019
 * Time: 12:50
 */

namespace console\controllers;

use api\helpers\ParseXlsx;
use api\helpers\UserEmailHelper;
use api\job\LocationAddedJob;
use api\models\forms\ManagementProductForm;
use api\models\integration\BrandForm;
use api\models\integration\ProductsForm;
use api\models\integration\UktzedForm;
use common\helpers\CropImageHelper;
use common\models\Attribute;
use common\models\AuthItem;
use common\models\AuthItemParent;
use common\models\Budget;
use common\models\Cart;
use common\models\CartProduct;
use common\models\CatalogLimitation;
use common\models\Category;
use common\models\CategoryExclude;
use common\models\Company;
use common\models\Contract;
use common\models\Locations;
use common\models\Order;
use common\models\OrderProduct;
use common\models\Product;
use common\models\ProductCategory;
use common\models\ProductPhoto;
use common\models\Specification;
use common\models\SpecificationLocation;
use common\models\SpecificationProduct;
use common\models\User;
use GraphQL\Language\AST\Location;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use Yii;
use yii\console\Controller;
use yii\helpers\ArrayHelper;

/**
 * Class TestController
 * @package console\controllers
 */
class TestController extends Controller
{
    public function actionGeneratePassword()
    {
        $users = User::find()
            ->where(['company_id' => 136])
            ->andWhere(['!=', 'manager', 1])
            ->all();
        if ($users) {
            foreach ($users as $user) {
                $pass = Yii::$app->security->generateRandomString(16);
                $user->password_difficult = 3;
                $user->setPassword($pass);
                $user->generateAuthKey();
                $user->validate();
                if ($user->save()) {
                    print_r(['email' => $user->email, 'pass' => $pass]);
                }
                UserEmailHelper::createdUser($user, $pass);
            }
        }
    }

//    public function actionIndex()
//    {
//        $executionStartTime = microtime(true);
//
//        $images = ProductPhoto::find()
//            ->all();
//        if ($images) {
//            foreach ($images as $image) {
//                /** @var  $image ProductPhoto */
//                $path = Yii::getAlias('@api') . '/web/product/';
//                $img = $path . $image->name;
//                $imgNew = $path . 'thumb_' . $image->name;
//                $size = filesize($img) / 1024;
//                if ($size > 100) {
//                    try {
//                        CropImageHelper::createThumbnail($img, $img, 260);
////                    unlink($imgNew);
//                    } catch (\Exception $e) {
//                        $result = null;
//                    }
//                }
////                if (is_null($result)) {
////                    copy($img, $imgNew);
////                }
//            }
//        }
//        $executionEndTime = microtime(true);
//        $seconds = $executionEndTime - $executionStartTime;
//        echo "This script took $seconds to execute.";
//    }

    public function actionTest()
    {
     
    	$order  = Order::find()->where(['id' => 39438])->all();
    	
    	foreach ($order as $item)
	    {
		    $item->delete();
	    }
	    
    	
    }
//
//    public function actionIndex()
//    {
//        $exist = ProductPhoto::find()->select(['name'])->asArray()->all();
//        $exist = ArrayHelper::getColumn($exist, 'name');
//        $files = \yii\helpers\FileHelper::findFiles(Yii::getAlias('@api') . '/web/product/');
//        if (isset($files[0])) {
//            echo count($files);
//            foreach ($files as $index => $file) {
//                $nameFicheiro = substr($file, strrpos($file, '/') + 1);
//                $nameFicheiro = substr($nameFicheiro, 1);
//                if (!in_array($nameFicheiro, $exist) && $nameFicheiro != '.gitignore') {
//                    try {
//                        unlink($file);
//                    } catch (\Exception $e) {
//                        echo 'No file' . '\n';
//                    }
//                }
//            }
//        } else {
//            echo "There are no files available for download.";
//        }
//    }
//    public function actionIndex()
//    {
//        $photos = ProductPhoto::find()->where(['product_uuid' => '9afb1b74-f0ab-11e8-80d1-18a90520eefb'])->all();
//        if ($photos) {
//            foreach ($photos as $photo) {
//                $file = Yii::getAlias('@api') . '/web/product/' . $photo->name;
//                unlink($file);
////                $photo->delete();
//            }
//        }
//    }

    public function makeAction()
    {

    }

    public function actionIndex()
    {
        $inputFileName = Yii::getAlias('@api/web/import_files') . DIRECTORY_SEPARATOR . '2.xlsx';

        $reader = new Xlsx();
        $spreadsheet = $reader->load($inputFileName);
        $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
        $headings = array_shift($sheetData);
        array_walk(
            $sheetData,
            function (&$row) use ($headings) {
                $row = array_combine($headings, $row);
            }
        );
        foreach ($sheetData as $k => $data) {
            if (empty(trim($data['name']))) {
                continue;
            }
            $product = Product::find()
                ->where(['name' => trim($data['name'])])
                ->andWhere(['delete' => 0])
                ->andWhere(['is_buffer' => 0])
                ->one();
            if (!$product && !empty(trim($data['sku']))) {
                $product = Product::find()
                    ->where(['sku' => trim($data['sku'])])
                    ->andWhere(['delete' => 0])
                    ->andWhere(['is_buffer' => 0])
                    ->one();
            }
            if (!$product) {
                print_r($data);
            }
        }

        die();
        Product::updateAll(
            ['delete' => 1],
            [
                'and',
                ['<=', 'updated_at', '1588141566'],
                ['is_buffer' => 0]
            ]
        );
        die();
        $location = Locations::find()
            ->where(['id' => 349])
            ->one();
        Yii::$app->queue->push(new LocationAddedJob([
            'location' => $location
        ]));
        die();
//        $categories = Category::find()
//            ->orderBy('id DESC')
//            ->all();
//        $duplicates = [];
//        foreach ($categories as $k => $category) {
//            unset($categories[$k]);
//            foreach ($categories as $categoryOneMore) {
//                if ($category->name == $categoryOneMore->name) {
//                    $duplicates[] = [
//                        'old' => $categoryOneMore->uuid,
//                        'old_id' => $categoryOneMore->id,
//                        'new' => $category->uuid
//                    ];
//                }
//            }
//        }
//        if ($duplicates) {
//            foreach ($duplicates as $duplicate) {
//                $newUuid = $duplicate['new'];
//                $oldUuid = $duplicate['old'];
//                $oldId = $duplicate['old_id'];
//                Category::deleteAll(['uuid' => $newUuid]);
//                Category::updateAll(['uuid' => $newUuid], ['uuid' => $oldUuid]);
//                Attribute::updateAll(
//                    ['category_uuid' => $newUuid, 'category_id' => $oldId],
//                    ['category_uuid' => $oldUuid]
//                );
//                CatalogLimitation::updateAll(
//                    ['category_uuid' => $newUuid, 'category_id' => $oldId],
//                    ['category_uuid' => $oldUuid]
//                );
//                OrderProduct::updateAll(['parent_category_uuid' => $newUuid], ['parent_category_uuid' => $oldUuid]);
//                ProductCategory::updateAll(
//                    ['category_uuid' => $newUuid, 'category_id' => $oldId],
//                    ['category_uuid' => $oldUuid]
//                );
//                CategoryExclude::deleteAll(['category_uuid' => $newUuid]);
//                CategoryExclude::updateAll(
//                    ['category_uuid' => $newUuid, 'category_id' => $oldId],
//                    ['category_uuid' => $oldUuid]
//                );
//            }
//        }
//        print_r($duplicates);
//        return;
        $arr = [
            'products' => [
                0 => [
                    'uuid'        => 'ce35ed99-259d-11ea-a23b-00155dd08a09',
//                    'uktzed' => null,
                    'props'       => [
                        0 => [
                            'attr'  => 'Р’РµСЃ',
                            'value' => '200РіСЂ',
                        ],
                    ],
                    'name'        => 'Р“СѓРјРєРё РґР»СЏ РіСЂРѕС€РµР№ Delta by AXENT 200РіСЂ D4621',
                    'category'    => '72dd7723-2587-11ea-a23b-00155dd08a09',
//                    'brand' => null,
                    'description' => '',
                    'unit'        => [
                        'uuid' => '1e474069-2568-11ea-a23b-00155dd08a09',
                        'name' => 'РїР°С‡',
                    ],
                    'buy_price'   => '0',
                    'vat'         => '20%'
                ]
            ]
        ];
        $model = new ProductsForm();
        if ($model->load($arr, '') && $model->validate()) {
            $result = $model->save();
            print_r($result);
        }
        $model->validate();
        print_r($model->errors);
        return;
        $a = 0;
        $b = 0;
        if ($a > 0) {
            $this->makeAction();
        } elseif ($a > 1) {
            $this->makeAction();
        } elseif ($b > 2) {
            $this->makeAction();
        } elseif ($b > 3) {
            $this->makeAction();
        } elseif ($a > 4) {
            $this->makeAction();
        }
        die();
        print_r(AuthItem::translate('директор'));
        die();
        $query = Product::find()
            ->where(['id' => 1])
            ->createCommand()
            ->getRawSql();
        $sql = Product::findBySql($query . '  FOR UPDATE')->one();
        print_r($sql);
        die();
        $budget = Budget::find()
            ->where(['company_id' => 64])
            ->active()
            ->asArray()
            ->all();
        print_r($budget);
        die();
        ParseXlsx::updateCatalog('prod3.xlsx');
        die();
        $cart = Cart::find()
            ->where(['!=', 'order_id', 0])
            ->all();
        $orders = Order::find()
            ->where(['status' => Order::STATUS_OPEN])
            ->andWhere(['not in', 'id', ArrayHelper::getColumn($cart, 'order_id')])
            ->all();
        if ($orders) {
            foreach ($orders as $order) {
                $model = new Cart([
                    'company_id' => $order->company_id,
                    'order_id'   => $order->id,
                    'user_id'    => 0,
                    'total'      => $order->total_sum
                ]);
                $model->save();
            }
        }
        $carts = Cart::find()
            ->where(['in', 'order_id', ArrayHelper::getColumn($orders, 'id')])
            ->all();
        if ($carts) {
            foreach ($carts as $cart) {
                $products = OrderProduct::find()
                    ->where(['order_id' => $cart->order_id])
                    ->all();
                if ($products) {
                    foreach ($products as $product) {
                        $model = new CartProduct([
                            'cart_id'      => $cart->id,
                            'status'       => CartProduct::STATUS_AT_ORDER,
                            'price'        => $product->price,
                            'total_sum'    => $product->total_sum,
                            'product_id'   => $product->id,
                            'product_uuid' => $product->product_uuid,
                            'quantity'     => $product->quantity,
                            'new_quantity' => 0
                        ]);
                        $model->save();
                    }
                }
            }
        }
    }
}
