<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 25.07.2019
 * Time: 16:04
 */

namespace api\controllers\v1;

use api\controllers\RestController;
use api\models\forms\ManagementProductForm;
use app\models\ProductPhoto;
use common\models\Attribute;
use common\models\CartProduct;
use common\models\Product;
use common\models\ProductAttribute;
use common\models\ProductCategory;
use common\models\Specification;
use common\models\SpecificationProduct;
use Yii;
use yii\helpers\ArrayHelper;
use yii\web\UploadedFile;

/**
 * Class ProductController
 * @package api\controllers\v1
 */
class ProductController extends RestController
{
    public function actionGetByCategories()
    {
        $categories = ArrayHelper::getValue($this->post, 'categories');
        $categories = ArrayHelper::getColumn($categories, 'uuid');
        $productIds = ProductCategory::find()->where(['in', 'category_uuid', $categories])->all();
        $products = Product::find()
            ->where(['in', 'uuid', ArrayHelper::getColumn($productIds, 'product_uuid')])
            ->select(['id', 'uuid', 'name'])
            ->asArray()
            ->all();
        return $this->successResponse(['products' => $products]);
    }

    public function actionCreate()
    {
        if (Yii::$app->user->can('managementProductCreate')) {
            $model = new ManagementProductForm();
            //\Yii::error(Yii::$app->getRequest()->getBodyParams());
            if ($model->load(Yii::$app->getRequest()->getBodyParams(), '')) {
                $model->files = UploadedFile::getInstancesByName('files');
                if ($model->validate()) {
                    $result = $model->create();
                    if ($result) {
                        return $this->successResponse();
                    }
                }
            }
            return $model;
        }
        return $this->accessDenied();
    }

    public function actionUpdate()
    {
        if (Yii::$app->user->can('managementProductUpdate')) {
            $model = new ManagementProductForm();
            if ($model->load(Yii::$app->getRequest()->getBodyParams(), '')) {
                $model->files = UploadedFile::getInstancesByName('files');
                if ($model->validate()) {
                    $result = $model->update();
                    if ($result) {
                        return $this->successResponse();
                    }
                }
            }
            return $model;
        }
        return $this->accessDenied();
    }

    public function actionDelete()
    {
        if (Yii::$app->user->can('managementProductUpdate')) {
            $model = Product::find()->where(['id' => ArrayHelper::getValue($this->post, 'id', 0)])->one();
            if ($model) {
                $model->deleted = Product::STATUS_HIDDEN;
                if ($model->save()) {
                    return $this->successResponse();
                }
            }
            return $this->failResponse();
        }
        return $this->accessDenied();
    }

    public function actionPropsByCategory()
    {
        if (Yii::$app->user->can('managementProductUpdate')) {
            $uuid = ArrayHelper::getValue($this->post, 'uuid', 0);
            $attrs = Attribute::find()->where(['category_uuid' => $uuid])->all();
        }
        return $this->accessDenied();
    }

    public function actionPropsByProduct()
    {
        if (Yii::$app->user->can('managementProductUpdate')) {
            $uuid = ArrayHelper::getValue($this->post, 'uuid', 0);
            $attr = ProductAttribute::find()
                ->joinWith(['attr', 'attr.values'])
                ->where(['product_attribute.product_uuid' => $uuid])
                ->all();
            if ($attr) {
                $result = [];
                foreach ($attr as $value) {
                    if ($value->attr) {
                        $result[] = [
                            'id' => $value->attr->id,
                            'name' => $value->attr->name,
                            'value_uuid' => $value->value_id,
                            'values' => $value->attr->values
                        ];
                    }
                }
                return $this->successResponse(['props' => $result]);
            }
            return $this->failResponse();
        }
        return $this->failResponse();
    }

    public function actionApproveBuffer()
    {
        if (Yii::$app->user->can('managementProductUpdate')) {
            $id = ArrayHelper::getValue($this->post, 'id', 0);
            $product = Product::find()
                ->where(['id' => $id])
                ->andWhere(['is_buffer' => 1])
                ->andWhere(['buffer_status' => Product::BUFFER_EDIT])
                ->one();
            if ($product) {
                $product->buffer_status = Product::BUFFER_WAITING;
                if ($product->save()) {
                    return $this->successResponse();
                }
            }
            return $this->failResponse();
        }
        return $this->accessDenied();
    }

    public function actionRestartBuffer()
    {
        if (Yii::$app->user->can('managementProductUpdate')) {
            $id = ArrayHelper::getValue($this->post, 'id', 0);
            $product = Product::find()
                ->where(['id' => $id])
                ->andWhere(['is_buffer' => 1])
                ->andWhere(['buffer_status' => Product::BUFFER_APPROVED])
                ->one();
            if ($product) {
                $product->buffer_status = Product::BUFFER_EDIT;
                if ($product->save()) {
                    return $this->successResponse();
                }
            }
            return $this->failResponse();
        }
        return $this->accessDenied();
    }
	
	public function actionTest()
	{
		$listProduct = [];
		
		if (($handle = fopen("/var/www/salvi/www_api/listToDel.csv", "r")) !== FALSE)
		{
			while (($data = fgetcsv($handle, 1000, ";")) !== FALSE)
			{
				if(!empty($data[5]))
				{
					$listProduct[$data[5]] = $data;
				}
			}
			
			fclose($handle);
		}
		
	//	unset($listProduct[0]);
		$listProductDb = Product::find()->select(['id','uuid'])->all();
		
		$logFile = "/var/www/salvi/www_api/log.txt";
		$i = 0;
		
		try
		{
			foreach ($listProductDb as $keyDbProduct =>  $productDb)
			{
				if(!isset($listProduct[$productDb->uuid]))
				{
				
//					file_put_contents($logFile,print_r($productDb->uuid,true).PHP_EOL,FILE_APPEND);
					
//					file_put_contents($logFile,print_r(ProductAttribute::find()->where(['product_uuid' => $productDb->uuid])->count(),true).PHP_EOL,FILE_APPEND);
					
					$items = ProductAttribute::find()->where(['product_uuid' => $productDb->uuid])->all();
					
					foreach ($items as $item)
					{
						$item->delete();
					}
					
					unset($items);
					unset($item);
					
//					file_put_contents($logFile,print_r(ProductCategory::find()->where(['product_uuid' => $productDb->uuid])->count(),true).PHP_EOL,FILE_APPEND);
					
					$items = ProductCategory::find()->where(['product_uuid' => $productDb->uuid])->all();
					
					foreach ($items as $item)
					{
						$item->delete();
					}
					
					unset($items);
					unset($item);
					
//					file_put_contents($logFile,print_r(ProductPhoto::find()->where(['product_uuid' => $productDb->uuid])->count(),true).PHP_EOL,FILE_APPEND);
					
					$items = ProductPhoto::find()->where(['product_uuid' => $productDb->uuid])->all();
					
					foreach ($items as $item)
					{
						$item->delete();
					}
					
					unset($items);
					unset($item);
					
//					file_put_contents($logFile,print_r(SpecificationProduct::find()->where(['product_uuid' => $productDb->uuid])->count(),true).PHP_EOL,FILE_APPEND);
					
					$items = SpecificationProduct::find()->where(['product_uuid' => $productDb->uuid])->all();
					
					foreach ($items as $item)
					{
						$item->delete();
					}
					
					unset($items);
					unset($item);
					
					
					$items = CartProduct::find()->where(['product_uuid' => $productDb->uuid])->all();
					
					foreach ($items as $item)
					{
						$item->delete();
					}
					
					unset($items);
					unset($item);
					
					$productDb->delete();
					
					unset($listProduct[$productDb->uuid]);
					unset($listProductDb[$keyDbProduct]);
					
					++$i;
					
					if($i  == 1000)
					{
						echo '1111' . PHP_EOL;
						die();
					}
				}
			}
			
		}
		catch (\Exception $e)
		{
		    echo '<pre>';
		    print_r($e->getTrace());
		    echo '</pre>';
		    echo '<pre>';
		    print_r($e->getMessage());
		    echo '</pre>';
			die();
		}
		
	}
	
	
	
}
