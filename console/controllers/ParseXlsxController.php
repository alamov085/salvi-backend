<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 18.10.2019
 * Time: 17:35
 */

namespace console\controllers;

use api\helpers\NumberHelper;
use Box\Spout\Common\Type;
use Box\Spout\Reader\Common\Creator\ReaderFactory;
use common\models\Budget;
use common\models\BudgetCostCenter;
use common\models\BudgetLocation;
use common\models\CartProduct;
use common\models\Company;
use common\models\CompanyEntity;
use common\models\CompanyEntityLocation;
use common\models\CostCenter;
use common\models\Locations;
use common\models\OrderProduct;
use common\models\Product;
use common\models\ProductAttribute;
use common\models\ProductBrand;
use common\models\ProductCategory;
use common\models\ProductPhoto;
use common\models\SpecificationProduct;
use common\models\User;
use common\models\UserCostCenter;
use common\models\UserLocation;
use common\models\Vendor;
use PhpOffice\PhpSpreadsheet\Reader\Xls;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use Yii;
use yii\console\Controller;
use yii\helpers\FileHelper;

/**
 * Class ParseXlsx
 * @package console\controllers
 */
class ParseXlsxController extends Controller
{
    public function actionReadLocation()
    {
        $inputFileName = Yii::getAlias('@console/xml') . DIRECTORY_SEPARATOR . 'e_location.xlsx';

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
            if ($k >= 2) {
                $company_name = $data['company_name'];
                $company = Company::find()->where(['like', 'short_name', trim($company_name)])->one();
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
                    $model->zip_code = str_replace('*', '', $data['zip_code']);
                    $model->city = $data['city'];
                    $model->street = $data['street'];
                    $model->building = str_replace('*', '', $data['building']) . '';
                    $model->floor = $data['level'];
                    $model->office = $data['apartment'] . '';
                    $model->phone_one = $phone_one;
                    $model->phone_two = $phone_two;
                    $model->budgetLocationOnly = $data['budget'];
                    $model->allCompanies = $data['all_company_entity'];
                    $model->isActive = 1;
                    $model->validate();
                    if ($model->errors) {
                        print_r($model->errors);
                        print_r([$phone_one, $phone_two]);
                    }
                    $result = $model->save();
//                    $result = false;
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
                                        'full_name' => $entity,
                                        'company_id' => $company->id
                                    ]);
                                    $entityModel->validate();
                                    $entityModel->save();
                                }
                                $newModel = new CompanyEntityLocation([
                                    'company_id' => $entityModel->id,
                                    'location_id' => $model->id
                                ]);
                                $newModel->save();
                            }
                        }
                    } else {
                        print_r($company_name);
                    }
                } else {
                    print_r($data);
                }
            }
        }
    }

    public function actionReadCost()
    {
        $inputFileName = Yii::getAlias('@console/xml') . DIRECTORY_SEPARATOR . 'e_cost.xlsx';

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
            if ($k >= 3) {
                $location = Locations::find()->where(['like', 'name', trim($data['location_name'])])->one();
                if (empty(trim($data['location_name']))) {
                    continue;
                }
                if ($location) {
                    $model = new CostCenter([
                        'location_id' => $location->id,
                        'name' => trim($data['cost_name'])
                    ]);
                    $model->save();
                }
            }
        }
    }

    public function actionReadBudget()
    {
        $inputFileName = Yii::getAlias('@console/xml') . DIRECTORY_SEPARATOR . 'e_budget.xlsx';

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
            if (empty(trim($data['company_name']))) {
                continue;
            }
            $company = Company::find()->where(['like', 'short_name', $data['company_name']])->one();
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
                            'name' => trim($data['name']),
                            'sum' => $data['sum'] * 1.2,
                            'date_end' => strtotime($data['date_end']),
                            'date_start' => strtotime($data['date_start']),
                            'budget_restriction' => $data['strict_budget'],
                            'all_cost' => $data['all_cost_center'],
                            'company_id' => $company->id,
                            'status' => 1,
                            'user_id_responsible' => 1
                        ]);
                        if ($model->save()) {
                            $modelCost = new BudgetCostCenter([
                                'budget_id' => $model->id,
                                'cost_center_id' => $cost->id
                            ]);
                            $modelCost->save();
                            $modelLocation = new BudgetLocation([
                                'budget_id' => $model->id,
                                'location_id' => $location->id
                            ]);
                            $modelLocation->save();
                        }
                    } else {
                        print_r([$k, $data]);
                    }
                } else {
                    print_r([$k, $data]);
                }
            }
        }
    }

    public function actionReadUser()
    {
        $inputFileName = Yii::getAlias('@console/xml') . DIRECTORY_SEPARATOR . 'e_user.xlsx';

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
            if (empty(trim($data['company_name']))) {
                continue;
            }
            if ($k >= 2) {
                $company = Company::find()->where(['like', 'short_name', $data['company_name']])->one();
                if ($company) {
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
                                'company_id' => $company->id,
                                'first_name' => trim($data['first_name']),
                                'last_name' => trim($data['last_name']),
                                'email' => trim($data['email']),
                                'username' => trim($data['email']),
                                'phone_number' => str_replace('-', '', trim($data['phone'])),
                                'position' => trim($data['position']),
                                'all_location' => $data['all_location']
                            ]);
                            $user->setPassword(Yii::$app->security->generateRandomString());
                            $user->generateAuthKey();
                            $user->generateEmailVerificationToken();
                            $user->status = User::STATUS_ACTIVE;
                            if ($user->save()) {
                                $manager = Yii::$app->authManager;
                                $role = $manager->getRole('koristuvacaf_21');
                                $manager->assign($role, $user->id);
                                $modelCost = new UserCostCenter([
                                    'user_id' => $user->id,
                                    'cost_center_id' => $cost->id
                                ]);
                                $modelCost->save();
                                $modelLocation = new UserLocation([
                                    'user_id' => $user->id,
                                    'location_id' => $location->id
                                ]);
                                $modelLocation->save();
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
        }
    }

    public function actionReadSpec()
    {
        $inputFileName = Yii::getAlias('@console/xml') . DIRECTORY_SEPARATOR . 'e_spec.xlsx';

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
                ->one();
            if (!$product && !empty(trim($data['sku']))) {
                $product = Product::find()
                    ->where(['sku' => trim($data['sku'])])
                    ->one();
            }
            if ($product) {
                $model = new SpecificationProduct([
                    'product_uuid' => $product->uuid,
                    'product_id' => $product->id,
                    'specification_id' => 4,
                    'price' => str_replace(',', '', $data['price']) * 1.2,
                    'markup' => 0,
                    'manual_markup' => 0,
                    'use_markup' => 0,
                    'quantity' => $data['qty']
                ]);
                $model->validate();
                if ($model->errors) {
                    print_r($model->errors);
                    echo $data['name'];
                    echo "\n";
                }
                $model->save();
            } else {
                echo $data['name'];
                echo "\n";
            }
        }
    }

    public function actionReadImages()
    {
        $inputFileName = Yii::getAlias('@console/xml') . DIRECTORY_SEPARATOR . 'images.xlsx';

        $reader = ReaderFactory::createFromType(Type::XLSX);
        $reader->open($inputFileName);
        $i = 0;
        $k = 0;
        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $row) {
                $cells = $row->toArray();
                $model = Product::find()
                    ->joinWith(['image'])
                    ->where(['product.sku' => $cells[0]])->one();
                if ($model) {
                    $k++;
                    if ($cells[12]) {
                        try {
                            $picture = file_get_contents($cells[12]);
                            $name = $model->uuid . '.' . pathinfo(parse_url($cells[12], PHP_URL_PATH), PATHINFO_EXTENSION);
                            $path = Yii::getAlias('@api') . '/web/product/';
                            FileHelper::createDirectory($path);
                            $file = $path . $name;
                            file_put_contents($file, $picture);
                            if ($model->image) {
                                $model->image->name = $name;
                                $model->image->save();
                            } else {
                                $modelImage = new ProductPhoto([
                                    'name' => $name,
                                    'product_uuid' => $model->uuid,
                                    'product_id' => $model->id
                                ]);
                                $modelImage->save();
                            }
                        } catch (\Exception $e) {
                            echo $cells[12] . "\n";
                        }
                    }
                }
            }
        }
        $reader->close();
        echo $k;
//        $reader = new Xls();
//        $spreadsheet = $reader->load($inputFileName);
//        $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
//        $headings = array_shift($sheetData);
//        array_walk(
//            $sheetData,
//            function (&$row) use ($headings) {
//                $row = array_combine($headings, $row);
//            }
//        );
//        foreach ($sheetData as $k => $data) {
//           if ($k < 10){
//               print_r($data);
//           }
//        }
    }

    public function actionUpdatePrice()
    {
        $inputFileName = Yii::getAlias('@console/xml') . DIRECTORY_SEPARATOR . 'prices.xlsx';

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
                    $model->name = $cells[1];
                    $model->save();
                }
            }
        }
        $reader->close();
    }

    public function actionUuid()
    {
        $inputFileName = Yii::getAlias('@console/xml') . DIRECTORY_SEPARATOR . 'uuid.xlsx';

        $reader = ReaderFactory::createFromType(Type::XLSX);
        $reader->open($inputFileName);
        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $k => $row) {
                if ($k > 1) {
                    $cells = $row->toArray();
                    $old_uuid = trim($cells[0]);
                    $new_uuid = trim($cells[1]);
                    CartProduct::updateAll(
                        ['product_uuid' => $new_uuid],
                        ['product_uuid' => $old_uuid]
                    );
                    OrderProduct::updateAll(
                        ['product_uuid' => $new_uuid],
                        ['product_uuid' => $old_uuid]
                    );
                    Product::updateAll(
                        ['uuid' => $new_uuid],
                        ['uuid' => $old_uuid]
                    );
                    ProductAttribute::updateAll(
                        ['product_uuid' => $new_uuid],
                        ['product_uuid' => $old_uuid]
                    );
                    ProductCategory::updateAll(
                        ['product_uuid' => $new_uuid],
                        ['product_uuid' => $old_uuid]
                    );
                    ProductPhoto::updateAll(
                        ['product_uuid' => $new_uuid],
                        ['product_uuid' => $old_uuid]
                    );
                    SpecificationProduct::updateAll(
                        ['product_uuid' => $new_uuid],
                        ['product_uuid' => $old_uuid]
                    );
                }
            }
        }
        $reader->close();
    }

    public function actionUpdateVendor()
    {
        $inputFileName = Yii::getAlias('@console/xml') . DIRECTORY_SEPARATOR . 'vendor.xlsx';

        $reader = ReaderFactory::createFromType(Type::XLSX);
        $reader->open($inputFileName);
        foreach ($reader->getSheetIterator() as $a => $sheet) {
            foreach ($sheet->getRowIterator() as $k => $row) {
                $cells = $row->toArray();
                if ($a == 4 && $k > 1 && count($cells)>3) {
                    $vendor = Vendor::find()->where(['uuid' => trim($cells[3])])->one();
                    if (!$vendor) {
                        $vendor = new Vendor([
                            'uuid' => trim($cells[3]),
                            'name' => trim($cells[2])
                        ]);
                    } else {
                        $vendor->name = trim($cells[2]);
                    }
                    $vendor->save();

                    $brand = ProductBrand::find()->where(['uuid' => trim($cells[1])])->one();
                    if (!$brand) {
                        $brand = new ProductBrand([
                            'uuid' => trim($cells[1]),
                            'name' => trim($cells[0]),
                            'vendor_uuid' => trim($cells[3])
                        ]);
                    } else {
                        $brand->name = trim($cells[0]);
                        $brand->vendor_uuid = trim($cells[3]);
                    }
                    $brand->save();
                }
            }
        }
        $reader->close();
    }

}
