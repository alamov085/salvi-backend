<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 02.07.2019
 * Time: 9:56
 */

namespace api\controllers\v1;

use api\controllers\RestController;
use api\models\forms\BudgetForm;
use common\models\Budget;
use common\models\User;
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;

/**
 * Class BudgetController
 * @package api\controllers\v1
 */
class BudgetController extends RestController
{
    /**
     * @return BudgetForm|array
     * @throws Throwable
     * @throws InvalidConfigException
     */
    public function actionCreate()
    {
        if (Yii::$app->user->can('createBudget')) {
            $model = new BudgetForm();
            if ($model->load(Yii::$app->getRequest()->getBodyParams(), '') &&
                $model->validate() &&
                $model->create()) {
                return $this->successResponse();
            }
            $model->validate();
            return $model;
        } else {
            return $this->accessDenied();
        }
    }

    /**
     * @return BudgetForm|array
     * @throws Throwable
     * @throws InvalidConfigException
     */
    public function actionUpdate()
    {
        if (Yii::$app->user->can('updateBudget')) {
            $model = new BudgetForm();
            if ($model->load(Yii::$app->getRequest()->getBodyParams(), '') && $model->validate() &&
                $model->update()) {
                return $this->successResponse();
            }
            $model->validate();
            return $model;
        } else {
            return $this->accessDenied();
        }
    }

    /**
     * @return array
     * @throws Throwable
     */
    public function actionDelete()
    {
        if (Yii::$app->user->can('deleteBudget')) {
            $id = ArrayHelper::getValue($this->post, 'id', 0);
            /** @var  $user User */
            $user = Yii::$app->user->getIdentity();
            if ($id > 0 && is_numeric($id)) {
                $model = Budget::find()
                    ->where(['id' => $id])
                    ->andWhere(['company_id' => $user->company_id])
                    ->one();
                if ($model) {
                    $model->status = Budget::STATUS_DELETED;
                    if ($model->save()) {
                        return $this->successResponse();
                    }
                }
            }
            return $this->failResponse();
        } else {
            return $this->accessDenied();
        }
    }

    /**
     * @return array
     * @throws Throwable
     */
    public function actionGetListOfBudgets()
    {
        /** @var  $user User */
        $user = Yii::$app->user->getIdentity();
        $cost_center_id = ArrayHelper::getValue($this->post, 'cost_center_id', 0);
        $location_id = ArrayHelper::getValue($this->post, 'location_id', 0);
        $budgets = Budget::findByCostCenter($cost_center_id, $user->company_id, $location_id);
        $result = [];
        foreach ($budgets as $k => $budget) {
            $result[$k] = $budget->toArray();
            $result[$k]['available'] = $budget->getAvailableSum();
        }
        return $this->successResponse([
            'budgets' => $result
        ]);
    }
}
