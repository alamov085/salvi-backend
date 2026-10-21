<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 23.12.2019
 * Time: 16:37
 */

namespace api\controllers\v1;

use api\controllers\RestController;
use api\models\forms\CartForm;
use api\models\forms\NewCartToOldForm;
use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;

/**
 * Class CartController
 * @package api\controllers\v1
 */
class CartController extends RestController
{
    /**
     * @return array
     * @throws InvalidConfigException
     */
    public function actionAddProduct()
    {
        $model = new CartForm();
        if ($model->load(Yii::$app->getRequest()->getBodyParams(), '') &&
            $model->validate()) {
            $response = $model->addProduct();
            if ($response->result) {
                return $this->successResponse(['total' => $response->total]);
            }
        }
        return $this->failResponse();
    }

    /**
     * @return array
     * @throws InvalidConfigException
     */
    public function actionDeleteProduct()
    {
        $model = new CartForm();
        if ($model->load(Yii::$app->getRequest()->getBodyParams(), '') &&
            $model->validate()) {
            $response = $model->deleteProduct();
            if ($response->result) {
                return $this->successResponse(['total' => $response->total]);
            }
        }
        return $this->failResponse();
    }

    /**
     * @return array
     * @throws InvalidConfigException
     */
    public function actionUpdateProduct()
    {
        $model = new CartForm();
        if ($model->load(Yii::$app->getRequest()->getBodyParams(), '') &&
            $model->validate()) {
            $response = $model->updateProduct();
            if ($response->result) {
                return $this->successResponse(['total' => $response->total]);
            }
        }
        return $this->failResponse();
    }

    public function actionUpdateCart()
    {
        $model = new CartForm();
        if ($model->load(Yii::$app->getRequest()->getBodyParams(), '') &&
            $model->validate() &&
            $model->updateCart()) {
            return $this->successResponse();
        }
        return $this->failResponse();
    }

    public function actionClearCart()
    {
        $model = new CartForm();
        if ($model->load(Yii::$app->getRequest()->getBodyParams(), '') &&
            $model->validate() &&
            $model->clearCart()) {
            return $this->successResponse();
        }
        return $this->failResponse();
    }

    public function actionNewToOldCart()
    {
        $model = new NewCartToOldForm();
        if ($model->load(Yii::$app->getRequest()->getBodyParams(), '') &&
            $model->validate()) {
            if ($model->save()) {
                return $this->successResponse();
            }
            return $this->failResponse();
        }
        return $model;
    }

    public function actionCartIsChanged() {
        $postData = [
            'order_id' => ArrayHelper::getValue($this->post, 'orderId', 0),
            'id' => ArrayHelper::getValue($this->post, 'cartId', 0),
        ];

        $model = new CartForm();

        if ($model->load($postData, '') && $model->validate()) {
            $response = $model->cartIsChanged();

            return $this->successResponse(['changed' => $response]);
        }

        return $this->successResponse(['fail' => true]);
    }
}
