<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 12.07.2019
 * Time: 14:52
 */

namespace api\controllers\v1;

use api\controllers\RestController;
use api\models\forms\ContractForm;
use common\models\Contract;
use common\models\ContractLocation;
use common\models\User;
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;

/**
 * Class ContractController
 * @package api\controllers\v1
 */
class ContractController extends RestController
{
    /**
     * @return ContractForm|array
     * @throws InvalidConfigException
     */
    public function actionCreate()
    {
        if (Yii::$app->user->can('createContract')) {
            $model = new ContractForm();
            if ($model->load(Yii::$app->getRequest()->getBodyParams(), '') && $model->validate() &&
                $model->create()) {
                return $this->successResponse();
            }
            $model->validate();
            return $model;
        }
        return $this->accessDenied();
    }

    public function actionUpdate()
    {
        if (Yii::$app->user->can('updateContract')) {
            $model = new ContractForm();
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
     * @throws Throwable
     */
    public function actionGetListOfContracts()
    {
        /** @var  $user User */
        $user = Yii::$app->user->getIdentity();
        $company_entity_id = ArrayHelper::getValue($this->post, 'company_entity_id', 0);
        $location_id = ArrayHelper::getValue($this->post, 'location_id', 0);
        $cost_center_id = ArrayHelper::getValue($this->post, 'cost_center_id', 0);
        $contractIds = ContractLocation::find()->where(['location_id' => $location_id])->all();
        $ids = [];
        if ($contractIds != false) {
            $ids = ArrayHelper::getColumn($contractIds, 'contract_id');
        }
        $all_locations = Contract::find()
            ->where(['all_location' => 1])
            ->andWhere(['company_id' => $user->company_id])
            ->asArray()
            ->all();
        if ($all_locations) {
            $all_locations = ArrayHelper::getColumn($all_locations, 'id');
            $ids = ArrayHelper::merge($ids, $all_locations);
        }
        $date = date('d.m.Y');
        $time = strtotime($date);
        $contracts = Contract::find()
            ->joinWith('costCenters')
            ->where(['company_entity_id' => $company_entity_id])
            ->andWhere(['in', 'contract.id', $ids])
            ->andWhere(['contract_cost_center.cost_center_id' => $cost_center_id])
            ->andWhere(['company_id' => $user->company_id])
            ->orWhere([
                'and',
                ['company_id' => $user->company_id],
                ['company_entity_id' => $company_entity_id],
                ['all_location' => 1],
                ['status' => Contract::STATUS_ACTIVE],
                ['<=', 'date_start', $time],
                ['>=', 'date_end', $time]
            ])
            ->active()
            ->all();
        return $this->successResponse(['contracts' => $contracts]);
    }

    public function actionChangeStatus()
    {
        if (Yii::$app->user->can('statusContract')) {
            /** @var  $user User */
            $user = Yii::$app->user->getIdentity();
            $id = ArrayHelper::getValue($this->post, 'id', 0);
            $status = ArrayHelper::getValue($this->post, 'status', 0);
            $model = Contract::find()
                ->where(['company_id' => $user->company_id])
                ->andWhere(['id' => $id])
                ->one();
            if ($model) {
                $model->status = (int)$status;
                if ($model->save()) {
                    return $this->successResponse();
                }
            }
        }
        return $this->accessDenied();
    }

    public function actionIssetNumberContract()
    {
	    $numberContract = $this->request->get('number',null);

	    $idContract = $this->request->get('id',0);

	    if(!is_null($numberContract))
	    {
		    $model = Contract::find()
			    ->where(['number' => $numberContract])
			    ->andWhere(['<>','id',$idContract])
			    ->one();

		    if(!is_null($model))
		    {
			    return $this->successResponse(['issetNumberContract' => true]);
		    }
		    else
		    {
			    return $this->successResponse(['issetNumberContract' => false]);
		    }
	    }
	    else
	    {
		    return $this->successResponse(['issetNumberContract' => true]);
	    }
    }

	public function actionIssetNumberContracts()
	{

	}
}
