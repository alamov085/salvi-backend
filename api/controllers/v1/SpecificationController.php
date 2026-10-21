<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 01.08.2019
 * Time: 18:39
 */

namespace api\controllers\v1;

use api\controllers\RestController;
use api\helpers\NotificationHelper;
use api\helpers\SpecificationHelper;
use api\models\forms\SpecificationCategoryForm;
use api\models\forms\SpecificationForm;
use common\models\Company;
use common\models\Contract;
use common\models\ContractLocation;
use common\models\Locations;
use common\models\Specification;
use common\models\SpecificationLocation;
use common\models\SpecificationProduct;
use common\models\User;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * Class SpecificationController
 * @package api\controllers\v1
 */
class SpecificationController extends RestController
{
    public function actionCreate()
    {
        if (Yii::$app->user->can('createSpecification')) {
            $model = new SpecificationForm();
            if ($model->load(Yii::$app->getRequest()->getBodyParams(), '') && $model->validate() &&
                $model->create()) {
                (new SpecificationHelper())->init();
                return $this->successResponse();
            }
            $model->validate();
            return $model;
        }
        return $this->accessDenied();
    }

    public function actionGetListOfLocations()
    {
        $contract_id = ArrayHelper::getValue($this->post, 'id', 0);
        if (!$contract_id) {
            return $this->failResponse();
        }
        return $this->successResponse(['locations' => Locations::getListOfLocationsByContract($contract_id)]);
    }

    public function actionAddProduct()
    {
        if (Yii::$app->user->can('updateSpecification')) {
            $id = ArrayHelper::getValue($this->post, 'id', 0);
            $products = ArrayHelper::getValue($this->post, 'products', []);
            if (!$id || empty($products)) {
                return $this->accessDenied();
            }
            $specification = Specification::find()->where(['id' => $id])->one();
            if ($specification) {
                if ($specification->addProducts($products)) {
                    (new SpecificationHelper())->init();
                    SpecificationProduct::recalculateAvailable($id);
                    return $this->successResponse();
                } else {
                    return $this->failResponse();
                }
            }
        }
        return $this->accessDenied();
    }

    public function actionUpdateProduct()
    {
        if (Yii::$app->user->can('updateSpecification')) {
            $id = ArrayHelper::getValue($this->post, 'id', 0);
            if ($id) {
                $product = SpecificationProduct::find()->where(['id' => $id])->one();
                if ($product && $product->updateProduct($this->post)) {
                    return $this->successResponse();
                }
            }
            return $this->failResponse();
        }
        return $this->accessDenied();
    }

    public function actionUpdateProductQuantity()
    {
        if (Yii::$app->user->can('updateSpecification')) {
            $id = ArrayHelper::getValue($this->post, 'id', 0);
            if ($id) {
                $product = SpecificationProduct::find()->where(['id' => $id])->one();
                if ($product && $product->updateProductQuantity($this->post)) {
                    return $this->successResponse();
                }
            }
            return $this->failResponse();
        }
        return $this->accessDenied();
    }

    public function actionUpdateAllProducts()
    {
        if (Yii::$app->user->can('updateSpecification')) {
            $markup = ArrayHelper::getValue($this->post, 'markup', 0);
            $id = ArrayHelper::getValue($this->post, 'id', 0);
            $markup = str_replace(',', '.', $markup);
            if (is_numeric($markup) && $id) {
                Specification::updateAll(['markup' => $markup], ['id' => $id]);
                $products = SpecificationProduct::find()
                    ->joinWith(['product'])
                    ->where(['specification_product.specification_id' => $id])
                    ->andWhere(['specification_product.manual_markup' => 0])
                    ->all();
                if ($products) {
                    foreach ($products as $product) {
                        $product->use_markup = 1;
                        $product->markup = $markup;
                        $product->price = $product->product->buy_price * (100 + $markup) / 100;
                        $product->save();
                    }
                    return $this->successResponse();
                }
            }
            return $this->failResponse();
        }
        return $this->accessDenied();
    }

    public function actionManualMarkup()
    {
        if (Yii::$app->user->can('updateSpecification')) {
            $id = ArrayHelper::getValue($this->post, 'id', 0);
            if ($id) {
                $product = SpecificationProduct::find()->where(['id' => $id])->one();
                if ($product) {
                    if ($product->manual_markup) {
                        $product->manual_markup = 0;
                    } else {
                        $product->manual_markup = 1;
                    }
                    if ($product->save()) {
                        return $this->successResponse();
                    }
                }
            }
        }
        return $this->accessDenied();
    }

    public function actionDeleteProduct()
    {
        if (Yii::$app->user->can('updateSpecification')) {
            $id = ArrayHelper::getValue($this->post, 'id', 0);
            if ($id) {
                $product = SpecificationProduct::find()->where(['id' => $id])->one();
                if ($product && $product->delete()) {
                    (new SpecificationHelper())->init();
                    return $this->successResponse();
                }
            }
            return $this->failResponse();
        }
        return $this->accessDenied();
    }

    public function actionUpdate()
    {
        if (Yii::$app->user->can('updateSpecification')) {
            $model = new SpecificationForm();
            if ($model->load(Yii::$app->getRequest()->getBodyParams(), '') && $model->validate() &&
                $model->update()) {
                return $this->successResponse();
            }
            $model->validate();
            return $model;
        }
        return $this->accessDenied();
    }

    /**
     * @return array
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     * @deprecated Пока-что не используем
     */
    public function actionDelete()
    {
        return $this->accessDenied();
        $id = ArrayHelper::getValue($this->post, 'id', 0);
        /** @var  $user User */
        $user = Yii::$app->user->getIdentity();
        $specification = Specification::find()
            ->where(['id' => $id])
            ->andWhere(['company_id' => $user->company_id])
            ->one();
        if ($specification && $specification->delete()) {
            (new SpecificationHelper())->init();
            return $this->successResponse();
        }
        return $this->failResponse();
    }

    /**
     * @return array
     * @throws \Throwable
     */
    public function actionChangeStatus()
    {
        if (Yii::$app->user->can('changeStatusSpecification')) {
            /** @var  $user User */
            $user = Yii::$app->user->getIdentity();
            $id = ArrayHelper::getValue($this->post, 'id', 0);
            $status = ArrayHelper::getValue($this->post, 'status', 0);
            $model = Specification::find()
                ->where(['company_id' => $user->company_id])
                ->andWhere(['id' => $id])
                ->one();
            if (in_array($status, [Specification::STATUS_ACTIVE, Specification::STATUS_NON_ACTIVE])) {
                if ($model) {
                    $model->status = (int)$status;
                    if ($model->save()) {
                        SpecificationProduct::recalculateAvailable($model->id);
                        NotificationHelper::changeSpecificationStatusManager($model);
                        (new SpecificationHelper($model->company_id))->init();
                        return $this->successResponse();
                    }
                }
            }
        }
        return $this->accessDenied();
    }

    public function actionActivateProduct()
    {
        if (Yii::$app->user->can('updateSpecification')) {
            $id = ArrayHelper::getValue($this->post, 'id', 0);
            $specificationProduct = SpecificationProduct::find()
                ->where(['id' => $id])
                ->one();
            if ($specificationProduct) {
                $specification = Specification::find()
                    ->where(['id' => $specificationProduct->specification_id])
                    ->one();
                $specifications = Specification::specificationsInPeriod($specification);
                if ($specifications) {
                    SpecificationProduct::updateAll(
                        ['status' => 0],
                        [
                            'and',
                            ['product_uuid' => $specificationProduct->product_uuid],
                            ['in', 'specification_id', $specifications]
                        ]
                    );
                }
                $specificationProduct->status = 1;
                $specificationProduct->save();
            }
            return $this->failResponse();
        }
        return $this->accessDenied();
    }

    public function actionAddCategory()
    {
        if (Yii::$app->user->can('updateSpecification')) {
            $model = new SpecificationCategoryForm();
            if ($model->load(Yii::$app->getRequest()->getBodyParams(), '') && $model->validate() &&
                $model->addCategory()) {
                return $this->successResponse();
            }
            $model->validate();
            return $model;
        }
        return $this->accessDenied();
    }

    public function actionCheckProduct()
    {
        $id = ArrayHelper::getValue($this->post, 'id', 0);
        $product = SpecificationProduct::find()
            ->where(['id' => $id])
            ->one();
        if ($product) {
            $spec = Specification::find()->where(['id' => $product->specification_id])->one();
            if ($spec) {
                $specification = Specification::specificationsInPeriod($spec);
                $productsSpec = SpecificationProduct::find()
                    ->where(['in', 'specification_id', $specification])
                    ->andWhere(['product_uuid' => $product->product_uuid])
                    ->all();
                $doubleSpecification = Specification::find()
                    ->select(['id', 'name'])
                    ->where(['in', 'id', ArrayHelper::getColumn($productsSpec, 'specification_id')])
                    ->asArray()
                    ->all();
                return $this->successResponse(['data' => $doubleSpecification]);
            }
        }
        return $this->failResponse();
    }

    public function actionGetListByLocation()
    {
        $locations = ArrayHelper::getValue($this->post, 'locations', []);
        $all_location = ArrayHelper::getValue($this->post, 'all_location', false);
        $data = Specification::getListByLocation($locations, $all_location);
        return $this->successResponse(['data' => $data]);
    }
}
