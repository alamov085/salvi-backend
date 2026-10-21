<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 08.07.2019
 * Time: 14:32
 */

namespace api\controllers\v1;

use api\controllers\RestController;
use api\models\forms\SupplierForm;
use common\models\CompanySupplier;
use common\models\Contract;
use common\models\Supplier;
use common\models\User;
use Exception;
use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;

/**
 * Class SupplierController
 * @package api\controllers\v1
 */
class SupplierController extends RestController
{
    /**
     * @return SupplierForm|array
     * @throws InvalidConfigException
     */
    public function actionCreate()
    {
        if (Yii::$app->user->can('createSupplier')) {
            $model = new SupplierForm();
            if ($model->load(Yii::$app->getRequest()->getBodyParams(), '') && $model->validate() &&
                $model->create()) {
                return $this->successResponse();
            }
            $model->validate();
            return $model;
        }
        return $this->accessDenied();
    }

    /**
     * @return SupplierForm|array
     * @throws InvalidConfigException
     */
    public function actionUpdate()
    {
        if (Yii::$app->user->can('updateSupplier')) {
            $model = new SupplierForm();
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
     */
    public function actionRemoveFromCompany()
    {
        if (Yii::$app->user->can('updateSupplier')) {
            /** @var  $user User */
            $user = Yii::$app->user->getIdentity();
            $id = ArrayHelper::getValue($this->post, 'id', 0);
            CompanySupplier::deleteAll([
                'and',
                ['supplier_id' => $id],
                ['company_id' => $user->company_id]
            ]);
            Contract::updateAll(
                ['status' => Contract::STATUS_NON_ACTIVE],
                [
                    'and',
                    ['company_id' => $user->company_id],
                    ['supplier_id' => $id]
                ]
            );
            return $this->successResponse();
        }
        return $this->accessDenied();
    }

    /**
     * @return array
     * @throws \Throwable
     */
    public function actionGetValidContract()
    {
        if (Yii::$app->user->can('updateSupplier')) {
            /** @var  $user User */
            $user = Yii::$app->user->getIdentity();
            $id = ArrayHelper::getValue($this->post, 'id', 0);
            $contract = Contract::find()
                ->where(['company_id' => $user->company_id])
                ->andWhere(['supplier_id' => $id])
                ->active()
                ->all();
            return $this->successResponse(['contract' => $contract]);
        }
        return $this->accessDenied();
    }

    /**
     * @return array
     * @throws \Throwable
     */
    public function actionGetListToAdd()
    {
        /** @var  $user User */
        $user = Yii::$app->user->getIdentity();
        $notInList = CompanySupplier::find()
            ->where(['company_id' => $user->company_id])
            ->all();
        $notIn = ArrayHelper::getColumn($notInList, 'supplier_id');
        $supplier = Supplier::find()
            ->select(['id', 'name'])
            ->where(['not in', 'id', $notIn])
            ->asArray()
            ->all();
        return $this->successResponse(['supplier' => $supplier]);
    }

    /**
     * @return array
     * @throws \Throwable
     */
    public function actionAddCompany()
    {
        if (Yii::$app->user->can('createSupplier')) {
            /** @var  $user User */
            $user = Yii::$app->user->getIdentity();
            $suppliers = ArrayHelper::getValue($this->post, 'supplier', []);
            if (!empty($suppliers)) {
                $data = [];
                foreach ($suppliers as $supplier) {
                    $data[] = [
                        'company_id' => $user->company_id,
                        'supplier_id' => $supplier
                    ];
                }
                if (!empty($data)) {
                    try {
                        Yii::$app->db->createCommand()->batchInsert(
                            CompanySupplier::tableName(),
                            [
                                'company_id',
                                'supplier_id'
                            ],
                            $data
                        )->execute();
                    } catch (Exception $e) {
                        Yii::error($e);
                    }
                    return $this->successResponse();
                }
            }
            return $this->failResponse();
        }
        return $this->accessDenied();
    }
}
