<?php

namespace api\controllers\v2;

use api\controllers\RestController;
use api\models\integration\SpecificationForm;
use api\models\integration\SpecificationProductForm;
use common\models\SpecificationProduct;
use api\models\integration\SpecificationsForm;
use common\models\Specification;
use Yii;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use api\helpers\ParseXlsx;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use yii\caching\TagDependency;


/**
 * Class IntegrationController
 * @package api\controllers\v2
 */
class IntegrationController extends RestController
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();
//        $behaviors['access'] = [
//            'class' => AccessControl::className(),
//            'only'  => ['orders', 'product', 'update-product', 'update-specification'],
//            'rules' => [
//                [
//                    'allow'   => true,
//                    'actions' => ['orders', 'product', 'update-product', 'update-specification'],
//                    'roles'   => ['@'],
////                    'ips' => ['127.0.0.1']
//                ],
//            ],
//        ];

        return $behaviors;
    }

    public function actionUpdateSpecification()
    {
        if (Yii::$app->user->can('apiModel')) {

            $date = date('Y-m-d_H:i:s');
            $file = Yii::getAlias('@runtime/logs/specification/upload_' . $date . '.log');
            $fp = fopen($file, 'a+');
            fwrite($fp, "\r\n----------------------------------------------------");
            fwrite($fp, "\r\nСтарт импорта: " . $date);
            fwrite($fp, "\r\nВыгрузка: " . json_encode(Yii::$app->getRequest()->getBodyParams()));
            fclose($fp);

            $postData = Yii::$app->getRequest()->getBodyParams();

            $issetProductSpec = [];

            $specData = [
                'edrpou' => $postData['edrpou'],
                'specName' => $postData['specName'],
                'Num' => $postData['Num'],
                'SDat' => $postData['SDat'],
                'EDat' => $postData['EDat'],
                'Status' => $postData['Status'],
            ];

            $specModel = new SpecificationForm();
            if ($specModel->load($specData, '') && $specModel->validate()) {
                $result = $specModel->save();

                $specificationId = $specModel->specId;

                $specificationProduct = SpecificationProduct::find()->select(['id','product_uuid'])->where([
                    'specification_id' => $specificationId,
                ])->createCommand()->queryAll();


                if(!empty($specificationProduct))
                {
                    foreach ($specificationProduct as $item)
                    {
                        $issetProductSpec[$item['id']] = $item['product_uuid'];
                    }
                }

                if (isset($postData['Products']) && !empty($postData['Products']))
                {
                    foreach ($postData['Products'] as $specProduct)
                    {

                        if($key = array_search($specProduct['uuid'],$issetProductSpec))
                        {
                            unset($issetProductSpec[$key]);
                        }

                        $specProductModel = new SpecificationProductForm();

                        $productData = [
                            'specId' => $specificationId,
                            'uuid' => $specProduct['uuid'],
                            'qty' => $specProduct['qty'],
                            'price' => $specProduct['price']
                        ];

                        if ($specProductModel->load($productData, '') && $specProductModel->validate()) {
                            $spResult = $specProductModel->save();
                        } else {
                            $specProductModel->validate();
                            return $specProductModel;
                        }
                    }
                }


                if(!empty($issetProductSpec))
                {
                    foreach ($issetProductSpec as $key =>  $item)
                    {
                        SpecificationProduct::find()
                            ->where(['id'=>$key])
                            ->one()
                            ->delete();
                    }
                }

                TagDependency::invalidate(
                    Yii::$app->cache,
                    'getListByParamsSpecification'
                );

            } else {
                $specModel->validate();
            }

            return $specModel;
        }
        return $this->accessDenied();
    }
}