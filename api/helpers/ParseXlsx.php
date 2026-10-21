<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 06.12.2019
 * Time: 15:44
 */

namespace api\helpers;

use api\models\forms\ManagementProductForm;
use Box\Spout\Common\Type;
use Box\Spout\Reader\Common\Creator\ReaderFactory;
use common\models\AttributeValue;
use common\models\Budget;
use common\models\BudgetCostCenter;
use common\models\BudgetLocation;
use common\models\Company;
use common\models\CompanyEntity;
use common\models\CompanyEntityLocation;
use common\models\CostCenter;
use common\models\Locations;
use common\models\Notification;
use common\models\Product;
use common\models\ProductAttribute;
use common\models\ProductBrand;
use common\models\ProductCategory;
use common\models\ProductPhoto;
use common\models\ProductUktzed;
use common\models\Specification;
use common\models\SpecificationProduct;
use common\models\User;
use common\models\UserCostCenter;
use common\models\UserLocation;
use common\models\Vendor;
use Exception;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;

/**
 * Class ParseXlsx
 * @package api\helpers
 */
class ParseXlsx
{
    public static function readLocation(User $user, $name)
    {
        $inputFileName = Yii::getAlias('@api/web/import_files') . DIRECTORY_SEPARATOR . $name;

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
        $company = Company::find()->where(['id' => $user->company_id])->one();
        foreach ($sheetData as $k => $data) {
            if ($company) {
                if (empty(trim($data['name']))) {
                    continue;
                }
                $model = Locations::find()
                    ->where(['like', 'name', trim($data['name'])])
                    ->andWhere(['company_id' => $company->id])
                    ->one();
                if (!$model) {
                    $model = new Locations();
                }
                $phone_one = substr(
                    preg_replace(
                        '/\D/',
                        '',
                        $data['phone']
                    ),
                    1
                );
                $phone_two = substr(str_replace('-', '', $data['add_phone']), 1);
                $model->company_id = $company->id;
                $model->name = trim($data['name']);
                $zip = trim(str_replace('*', '', $data['zip_code']));
                if (!empty($zip)) {
                    $model->zip_code = $zip;
                }
                $model->city = $data['city'];
                $model->street = $data['street'];
                $model->building = str_replace('*', '', $data['building']) . '';
                $model->floor = $data['level'];
                $model->office = $data['apartment'] . '';
                $model->phone_one = $phone_one;
                if (!empty($phone_two)) {
                    $model->phone_two = $phone_two;
                }
                $model->budgetLocationOnly = $data['budget'];
                $model->allCompanies = $data['all_company_entity'];
                $model->isActive = 1;
                $model->validate();
                $result = $model->save();
                if ($result) {
                    CompanyEntityLocation::deleteAll(['location_id' => $model->id]);
                    $entities = explode('||', $data['company_entity']);
                    if ($entities) {
                        foreach ($entities as $entity) {
                            $entity = trim($entity);
                            $entityModel = CompanyEntity::find()
                                ->where(['like', 'short_name', $entity])
                                ->andWhere(['company_id' => $company->id])
                                ->one();
                            if (!$entityModel) {
                                $entityModel = new CompanyEntity([
                                    'short_name' => $entity,
                                    'full_name'  => $entity,
                                    'company_id' => $company->id
                                ]);
                                $entityModel->validate();
                                $entityModel->save();
                            }
                            $newModel = new CompanyEntityLocation([
                                'company_id'  => $entityModel->id,
                                'location_id' => $model->id
                            ]);
                            $newModel->save();
                        }
                    }
                }
            }
        }
        self::notification($user, 'Імпорт локацій завершено!');
        unlink($inputFileName);
    }

    private static function notification(User $user, $message)
    {
        $nModel = new Notification([
            'status'     => Notification::STATUS_SHOW,
            'user_id'    => $user->id,
            'is_read'    => 0,
            'message'    => $message,
            'created_at' => date('U')
        ]);
        $nModel->save();
        $redis = Yii::$app->redis;
        $redis->publish(
            'notification_' . $user->id,
            'new notification'
        );
    }

    public static function readCost(User $user, $name)
    {
        $inputFileName = Yii::getAlias('@api/web/import_files') . DIRECTORY_SEPARATOR . $name;

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
        $company = Company::find()->where(['id' => $user->company_id])->one();
        foreach ($sheetData as $k => $data) {
            $location = Locations::find()
                ->where(['like', 'name', trim($data['location_name'])])
                ->andWhere(['company_id' => $company->id])
                ->one();
            if (empty(trim($data['location_name']))) {
                continue;
            }
            if ($location) {
                $model = new CostCenter([
                    'location_id' => $location->id,
                    'name'        => trim($data['cost_name'])
                ]);
                $model->save();
            }
        }
        self::notification($user, 'Імпорт кост-центрів завершено!');
        unlink($inputFileName);
    }

    public static function readBudget(User $user, $name)
    {
        $inputFileName = Yii::getAlias('@api/web/import_files') . DIRECTORY_SEPARATOR . $name;

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
        $company = Company::find()->where(['id' => $user->company_id])->one();
        foreach ($sheetData as $k => $data) {
            if (empty(trim($data['name']))) {
                continue;
            }
            if ($company) {
                $location = Locations::find()
                    ->where(['company_id' => $company->id])
                    ->andWhere(['like', 'name', trim($data['Location'])])
                    ->one();
                if ($location) {
                    $cost = CostCenter::find()
                        ->where(['location_id' => $location->id])
                        ->andWhere(['like', 'name', trim($data['cost_center'])])
                        ->one();
                    if ($cost) {
                        $model = new Budget([
                            'name'                => trim($data['name']),
                            'sum'                 => $data['sum'],
                            'date_end'            => strtotime($data['date_end']),
                            'date_start'          => strtotime($data['date_start']),
                            'budget_restriction'  => $data['strict_budget'],
                            'all_cost'            => $data['all_cost_center'],
                            'company_id'          => $company->id,
                            'status'              => 1,
                            'user_id_responsible' => 1
                        ]);
                        if ($model->save()) {
                            $modelCost = new BudgetCostCenter([
                                'budget_id'      => $model->id,
                                'cost_center_id' => $cost->id
                            ]);
                            $modelCost->save();
                            $modelLocation = new BudgetLocation([
                                'budget_id'   => $model->id,
                                'location_id' => $location->id
                            ]);
                            $modelLocation->save();
                        }
                    }
                }
            }
        }
        self::notification($user, 'Імпорт бюджетів завершено!');
        unlink($inputFileName);
    }

    public static function readUser(User $user, $name)
    {
        $inputFileName = Yii::getAlias('@api/web/import_files') . DIRECTORY_SEPARATOR . $name;

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
        $company = Company::find()->where(['id' => $user->company_id])->one();
        foreach ($sheetData as $k => $data) {
            if ($company) {
                if (empty(trim($data['email']))) {
                    continue;
                }
                $location = Locations::find()
                    ->where(['company_id' => $company->id])
                    ->andWhere(['like', 'name', trim($data['locations'])])
                    ->one();
                if ($location) {
                    $cost = CostCenter::find()
                        ->where(['location_id' => $location->id])
                        ->andWhere(['like', 'name', trim($data['cost_centrs'])])
                        ->one();
                    if ($cost) {
                        $user = User::find()->where(['email' => trim($data['email'])])->one();
                        if ($user) {
                            continue;
                        }
                        $user = new User([
                            'company_id'   => $company->id,
                            'first_name'   => trim($data['first_name']),
                            'last_name'    => trim($data['last_name']),
                            'email'        => trim($data['email']),
                            'username'     => trim($data['email']),
                            'phone_number' => str_replace('-', '', trim($data['phone'])),
                            'position'     => trim($data['position']),
                            'all_location' => $data['all_location']
                        ]);
                        $pass = Yii::$app->security->generateRandomString();
                        $user->setPassword($pass);
                        $user->generateAuthKey();
                        $user->generateEmailVerificationToken();
                        $user->status = User::STATUS_ACTIVE;
                        if ($user->save()) {
                            $manager = Yii::$app->authManager;
                            $role = $manager->getRole('defaultUser');
                            $manager->assign($role, $user->id);
                            $modelCost = new UserCostCenter([
                                'user_id'        => $user->id,
                                'cost_center_id' => $cost->id
                            ]);
                            $modelCost->save();
                            $modelLocation = new UserLocation([
                                'user_id'     => $user->id,
                                'location_id' => $location->id
                            ]);
                            $modelLocation->save();
                            UserEmailHelper::createdUser($user, $pass);
                        } else {
                            print_r([$k, $data]);
                        }
                    } else {
                        print_r([$k, $data]);
                    }
                } else {
                    print_r([$k, $data]);
                }
            }
        }
        self::notification($user, 'Імпорт користувачів завершено!');
        unlink($inputFileName);
    }

    public static function readSpec(User $user, $name)
    {
        $inputFileName = Yii::getAlias('@api/web/import_files') . DIRECTORY_SEPARATOR . $name;

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
        $company = Company::find()->where(['id' => $user->company_id])->one();
        $productLoadFail = [];
        $upload = [];
        $moreThanOne = [];
        foreach ($sheetData as $k => $data) {
            if (empty(trim($data['name']))) {
                $productLoadFail[] = $k + 2;
                continue;
            }
            $specification = Specification::find()
                ->where(['company_id' => $company->id])
                ->andWhere(['like', 'name', trim($data['spec_name'])])
                ->one();
            if ($specification) {
                $product = Product::find()
                    ->where(['name' => trim($data['name'])])
                    ->andWhere(['deleted' => 0])
                    ->andWhere(['is_buffer' => 0])
                    ->one();
                if (!$product && !empty(trim($data['sku']))) {
                    $product = Product::find()
                        ->where(['sku' => trim($data['sku'])])
                        ->andWhere(['deleted' => 0])
                        ->andWhere(['is_buffer' => 0])
                        ->one();
                }
                if ($product) {
                    $model = SpecificationProduct::find()
                        ->where(['specification_id' => $specification->id])
                        ->andWhere(['product_uuid' => $product->uuid])
                        ->one();
                    if ($model) {
                        $model->price = str_replace(',', '', $data['price']) * 1.2;
                        $model->markup = 0;
                        $model->manual_markup = 1;
                        $model->use_markup = 0;
                        $model->quantity = $data['qty'];
                    } else {
                        $model = new SpecificationProduct([
                            'product_uuid'     => $product->uuid,
                            'product_id'       => $product->id,
                            'specification_id' => $specification->id,
                            'price'            => str_replace(',', '', $data['price']) * 1.2,
                            'markup'           => 0,
                            'manual_markup'    => 1,
                            'use_markup'       => 0,
                            'quantity'         => $data['qty']
                        ]);
                    }
                    $model->validate();
                    if ($model->errors) {
                        $productLoadFail[] = $k + 2;
                    }
                    $model->save();
                    if (in_array($model->id, $upload)) {
                        $moreThanOne[] = $k + 2;
                    } else {
                        $upload[] = $model->id;
                    }
                } else {
                    $productLoadFail[] = $k + 2;
                }
                SpecificationProduct::recalculateAvailable($specification->id);
            } else {
                $productLoadFail[] = $k + 2;
            }
        }
        $message = 'Імпорт специфікацій завершено!';
        if ($productLoadFail) {
            $message .= ' Не завантажені: ' . implode(', ', $productLoadFail) . '.';
        }
        if ($moreThanOne) {
            $message .= ' Повторювання: ' . implode(', ', $moreThanOne) . '.';
        }
        self::notification($user, $message);
        unlink($inputFileName);
    }

    public static function updatePrice($name)
    {
        $inputFileName = Yii::getAlias('@api/web/import_files') . DIRECTORY_SEPARATOR . $name;

        $reader = ReaderFactory::createFromType(Type::XLSX);
        $reader->open($inputFileName);
        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $row) {
                $cells = $row->toArray();
                $model = Product::find()->where(['uuid' => $cells[0]])->one();
                if ($model) {
                    $price = NumberHelper::convert($cells[2] * 1.2);
                    if ($price > 0) {
                        $model->buy_price = $price;
                    }
                    if (!empty(ArrayHelper::getValue($cells, '1')) && ArrayHelper::getValue($cells, '1') != '-1') {
                        $model->name = trim($cells[1]);
                    } elseif (ArrayHelper::getValue($cells, '1') == '-1') {
                        $model->name = '';
                    }
                    $model->save();
                }
            }
        }
        $reader->close();
        unlink($inputFileName);
    }

    public static function updateCatalog($name)
    {
        $inputFileName = Yii::getAlias('@api/web/import_files') . DIRECTORY_SEPARATOR . $name;

        $reader = ReaderFactory::createFromType(Type::XLSX);
        $reader->open($inputFileName);
        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $k => $row) {
                $cells = $row->toArray();
                if ($k > 2) {
                    /**
                     * 0 - uuid
                     * 1 - sku
                     * 2 - uktzed_uuid
                     * 3 - buy_price
                     * 4 - name
                     * 5 - description
                     * 6 - unit_uuid
                     * 7 - vendor_uuid
                     * 8 - brand_uuid
                     * 9 - category_uuid
                     * 10 - props1
                     * 11..n - props_n
                     */
                    print_r($cells);
                    $product = Product::find()
                        ->where(['uuid' => ArrayHelper::getValue($cells, '0')])
                        ->one();
                    if (!$product) {
                        $product = new Product(['uuid' => ArrayHelper::getValue($cells, '0') . '_' . time()]);
                        if (empty(ArrayHelper::getValue($cells, '0'))) {
                            $product->uuid = IntegrationHelper::generateUuid(true);
                        }
                        $product->buy_price = 0;
                        $product->is_buffer = 1;
                        $product->buffer_status = Product::BUFFER_WAITING;
                    }
                    $brand = ProductBrand::find()
                        ->where(['uuid' => ArrayHelper::getValue($cells, '8')])
                        ->one();
                    $vendor = Vendor::find()
                        ->where(['uuid' => ArrayHelper::getValue($cells, '7')])
                        ->one();
                    if (!empty(ArrayHelper::getValue($cells, '4')) && ArrayHelper::getValue($cells, '4') != '-1') {
                        $product->name = substr(trim(ArrayHelper::getValue($cells, '4')), 0, 255);
                    } elseif (ArrayHelper::getValue($cells, '4') == '-1') {
                        $product->name = null;
                    }
                    if (!empty(ArrayHelper::getValue($cells, '1')) && ArrayHelper::getValue($cells, '1') != '-1') {
                        $product->sku = ArrayHelper::getValue($cells, '1');
                    } elseif (ArrayHelper::getValue($cells, '1') == '-1') {
                        $product->sku = null;
                    }
                    if (!empty(ArrayHelper::getValue($cells, '2')) && ArrayHelper::getValue($cells, '2') != '-1') {
                        $product->uktzed = ArrayHelper::getValue($cells, '2');
                    } elseif (ArrayHelper::getValue($cells, '2') == '-1') {
                        $product->uktzed = null;
                    }
                    if (!empty(ArrayHelper::getValue($cells, '3')) && ArrayHelper::getValue($cells, '3') != '-1') {
                        try {
                            $price = +ArrayHelper::getValue($cells, '3');
                        } catch (Exception $e) {
                            $price = 0;
                        }
                        if (is_numeric($price) && $price <= 100000000 && $price > 0) {
                            $product->buy_price = NumberHelper::convert($price * 1.2);
                        } else {
                            $product->buy_price = 0;
                        }
                    } elseif (ArrayHelper::getValue($cells, '3') == '-1') {
                        $product->buy_price = 0;
                    }
                    if (!empty(ArrayHelper::getValue($cells, '5')) && ArrayHelper::getValue($cells, '5') != '-1') {
                        $product->description = ArrayHelper::getValue($cells, '5');
                    } elseif (ArrayHelper::getValue($cells, '5') == '-1') {
                        $product->description = '';
                    }
                    if (!empty(ArrayHelper::getValue($cells, '6')) && ArrayHelper::getValue($cells, '6') != '-1') {
                        $product->unit_uuid = ArrayHelper::getValue($cells, '6');
                    } elseif (ArrayHelper::getValue($cells, '6') == '-1') {
                        $product->unit_uuid = null;
                    }
                    if (!empty(ArrayHelper::getValue($cells, '8')) && ArrayHelper::getValue($cells, '8') != '-1') {
                        $product->brand_id = ($brand) ? $brand->id : null;
                    } elseif (ArrayHelper::getValue($cells, '8') == '-1') {
                        $product->brand_id = null;
                    }
                    if (!empty(ArrayHelper::getValue($cells, '7')) && ArrayHelper::getValue($cells, '7') != '-1') {
                        $product->vendor_id = ($vendor) ? $vendor->id : null;
                    } elseif (ArrayHelper::getValue($cells, '7') == '-1') {
                        $product->vendor_id = null;
                    }
                    if ($product->save(false)) {
                        if (!empty(ArrayHelper::getValue($cells, '9')) && ArrayHelper::getValue($cells, '9') != '-1') {
                            ProductCategory::deleteAll(['product_uuid' => $product->uuid]);
                            $categories = ManagementProductForm::getTreeCategory(ArrayHelper::getValue($cells, '9'));
                            if ($categories) {
                                $categoriesInsert = [];
                                foreach ($categories as $k => $category) {
                                    $categoriesInsert[] = [
                                        'category_id'   => $category['id'],
                                        'product_id'    => $product->id,
                                        'category_uuid' => $category['uuid'],
                                        'product_uuid'  => $product->uuid,
                                        'is_main'       => ($k > 0) ? 0 : 1
                                    ];
                                }
                                if ($categoriesInsert) {
                                    try {
                                        Yii::$app->db->createCommand()->batchInsert(
                                            ProductCategory::tableName(),
                                            ['category_id', 'product_id', 'category_uuid', 'product_uuid', 'is_main'],
                                            $categoriesInsert
                                        )->execute();
                                    } catch (Exception $e) {
                                        Yii::error($e);
                                    }
                                }
                            }
                        } elseif (ArrayHelper::getValue($cells, '9') == '-1') {
                            ProductCategory::deleteAll(['product_uuid' => $product->uuid]);
                        }
                        ProductAttribute::deleteAll(['product_uuid' => $product->uuid]);
                        if (count($cells) > 10) {
                            for ($i = 10; $i <= count($cells) - 1; $i++) {
                                $value = AttributeValue::find()->where(['uuid' => ArrayHelper::getValue($cells, $i)])->one();
                                if ($value) {
                                    $model = new ProductAttribute([
                                        'product_uuid' => $product->uuid,
                                        'attribute_id' => $value->attribute_id,
                                        'value_id'     => $value->id
                                    ]);
                                    $model->save();
                                }
                            }
                        }
                    }
                }
            }
        }
        $reader->close();
        unlink($inputFileName);
    }

    /**
     * @param $name
     * @throws \Box\Spout\Common\Exception\IOException
     * @throws \Box\Spout\Common\Exception\UnsupportedTypeException
     * @throws \Box\Spout\Reader\Exception\ReaderNotOpenedException
     */
    public static function updateUktzed($name)
    {
        $inputFileName = Yii::getAlias('@api/web/import_files') . DIRECTORY_SEPARATOR . $name;

        $reader = ReaderFactory::createFromType(Type::XLSX);
        $reader->open($inputFileName);
        $headings = [];
        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $k => $row) {
                $cells = $row->toArray();
                if ($k == 1) {
                    $headings = $cells;
                } else {
                    $cells = array_combine($headings, $cells);
                    $model = ProductUktzed::find()
                        ->where(['uuid' => ArrayHelper::getValue($cells, 'uuid')])
                        ->one();
                    if ($model) {
                        $model = new ProductUktzed(['uuid' => ArrayHelper::getValue($cells, 'uuid')]);
                    }
                    $model->full_name = ArrayHelper::getValue($cells, 'full_name');
                    $model->short_name = ArrayHelper::getValue($cells, 'short_name');
                    $model->type = ArrayHelper::getValue($cells, 'type');
                    $model->save();
                }
            }
        }
        $reader->close();
        unlink($inputFileName);
    }

    public static function updateImage($name)
    {
        $inputFileName = Yii::getAlias('@api/web/import_files') . DIRECTORY_SEPARATOR . $name;

        $reader = ReaderFactory::createFromType(Type::XLSX);
        $reader->open($inputFileName);
        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $k => $row) {
                $cells = $row->toArray();
                if ($k > 1) {
                    /**
                     * 0 - uuid
                     * 1 - image
                     * 2..n - image
                     */
                    $product = Product::find()
                        ->where(['uuid' => ArrayHelper::getValue($cells, '0')])
                        ->one();
                    if ($product) {
                        $productPhotos = ProductPhoto::find()
                            ->where(['product_uuid' => $product->uuid])
                            ->all();
                        if ($productPhotos) {
                            foreach ($productPhotos as $photo) {
                                $photo->delete();
                            }
                        }
                        if (count($cells) > 1) {
                            for ($i = 1; $i <= count($cells) - 1; $i++) {
                                try {
                                    $picture = file_get_contents($cells[$i]);
                                    $name = Yii::$app->security->generateRandomString(12) .
                                        '.' . pathinfo(parse_url($cells[$i], PHP_URL_PATH), PATHINFO_EXTENSION);
                                    $path = Yii::getAlias('@api') . '/web/product/';
                                    FileHelper::createDirectory($path);
                                    $file = $path . $name;
                                    file_put_contents($file, $picture);
                                    $modelImage = new ProductPhoto([
                                        'name'         => $name,
                                        'product_uuid' => $product->uuid,
                                        'product_id'   => $product->id
                                    ]);
                                    $modelImage->save();
                                } catch (\Exception $e) {
                                    \Yii::error($e->getMessage());
                                }
                            }
                        }
                    }
                }
            }
        }
        $reader->close();
        unlink($inputFileName);
    }
}
