<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 02.07.2019
 * Time: 9:58
 */

namespace api\models\forms;

use api\helpers\NotificationHelper;
use common\models\Budget;
use common\models\BudgetCostCenter;
use common\models\BudgetLocation;
use common\models\BudgetSpecification;
use common\models\CostCenter;
use common\models\Locations;
use common\models\Order;
use common\models\Specification;
use common\models\User;
use Throwable;
use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;

/**
 * Class BudgetForm
 * @package api\models\forms
 */
class BudgetForm extends Model
{
    public $id;
    public $company_id;
    public $name;
    public $sum;
    public $date_start;
    public $date_end;
    public $budget_restriction;
    public $all_cost;
    public $all_location;
    public $user_id_responsible;
    public $status;
    public $locations;
    public $cost_centers;
    public $specifications;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['name', 'sum', 'date_end', 'date_start'], 'required'],
            [['company_id', 'user_id_responsible', 'status'], 'integer'],
            [['budget_restriction', 'all_cost', 'all_location'], 'boolean'],
            [['sum'], 'number'],
            [['date_end', 'date_start'], 'filter', 'filter' => function ($value) {
                return strtotime($value);
            }],
            [['name'], 'string', 'max' => 20],
//            [['date_start', 'date_end'], 'integer'],
            [['locations', 'cost_centers', 'id', 'specifications'], 'safe']
        ];
    }

    public function validate($attributeNames = null, $clearErrors = true)
    {
        if ($this->date_end <= $this->date_start) {
            return $this->addError('date_end', 'Дата завершення повинна бути більша дати старту');
        }
        if ($this->id > 0) {
            if ($this->budget_restriction) {
                $budget = Budget::find()->where(['id' => $this->id])->one();
                if ($budget != false) {
                    $ordersSum = Order::find()
                        ->where(['company_id' => $budget->company_id])
                        ->andWhere([
                            'in',
                            'status',
                            [
                                Order::STATUS_ON_APPROVE,
                                Order::STATUS_AT_SUPPLIER,
                                Order::STATUS_RECEIVED,
                                Order::STATUS_AT_MANAGER
                            ]
                        ])
                        ->andWhere(['budget_id' => $this->id])
                        ->sum('total_sum');
                    if ($ordersSum > $this->sum) {
                        return $this->addError('sum', 'Сума бюджету не може бути менша суми куплених заказів');
                    }
                }
            }
        }
        if (!$this->all_cost && empty($this->cost_centers)) {
            return $this->addError('cost_centers', 'Виберіть хоча б один кост центр');
        }
        if (!$this->all_location && empty($this->locations)) {
            return $this->addError('locations', 'Виберіть хоча б одну локацію');
        }
        return parent::validate($attributeNames, $clearErrors);
    }

    /**
     * @return bool
     * @throws Throwable
     */
    public function create()
    {
        /** @var  $user User */
        $user = Yii::$app->user->getIdentity();
        $model = new Budget();
        $model->setAttributes([
            'company_id' => $user->company_id,
            'name' => $this->name,
            'sum' => $this->sum,
            'date_start' => $this->date_start,
            'date_end' => $this->date_end,
            'budget_restriction' => $this->budget_restriction,
            'all_cost' => $this->all_cost,
            'all_location' => $this->all_location,
            'user_id_responsible' => $user->id,
            'status' => $this->budgetStatus()
        ]);
        $result = $model->save();
        if ($result) {
            BudgetLocation::addLocations($model->id, $this->locations);
            if (!$this->all_cost) {
                if ($this->all_location) {
                    $locationList = Locations::find()
                        ->where(['company_id' => $user->company_id])
                        ->asArray()
                        ->all();
                    if ($locationList) {
                        $location = ArrayHelper::getColumn($locationList, 'id');
                    } else {
                        $location = [0];
                    }
                } else {
                    $location = $this->locations;
                }
                $costs = CostCenter::find()
                    ->where(['in', 'id', $this->cost_centers])
                    ->andWhere(['in', 'location_id', $location])
                    ->all();
                $costs = ArrayHelper::getColumn($costs, 'id');
                BudgetCostCenter::addCost($model->id, $costs);
            }
            if ($this->specifications) {
                $specificationToLocation = Specification::getListByLocation($this->locations, $this->all_location);
                $specification = Specification::find()
                    ->where(['in', 'id', ArrayHelper::getColumn($specificationToLocation, 'id')])
                    ->andWhere(['in', 'id', $this->specifications])
                    ->asArray()
                    ->all();
                BudgetSpecification::addSpecification($model->id, ArrayHelper::getColumn($specification, 'id'));
            }
            NotificationHelper::createdBudget($model, $this->locations, $this->cost_centers);
        }
        return $result;
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Назва',
            'sum' => 'Сума',
            'date_start' => 'Дата старту',
            'date_end' => 'Дата завершення',
            'budget_restriction' => 'Обмеження бюджетом',
            'all_cost' => 'Доступний у всіх кост-центрах',
            'all_location' => 'Доступний у всіх локаціях',
            'locations' => 'Локації'
        ];
    }

    /**
     * @return bool
     * @throws Throwable
     */
    public function update()
    {
        /** @var  $user User */
        $user = Yii::$app->user->getIdentity();
        $query = Budget::find()->where(['id' => $this->id])->andWhere(['company_id' => $user->company_id]);
//        if (!Yii::$app->user->can('updateBudget') && Yii::$app->user->can('updateBudgetOwn')) {
//            $query->andWhere(['user_id_responsible' => $user->id]);
//        }
//	    $logFile = "/var/www/salvi/www_api/log.txt";
//	    file_put_contents($logFile,print_r($this,true).PHP_EOL);
//	    die();
	    
        $model = $query->one();
        if ($model) {
            $model->setAttributes([
                'name' => $this->name,
                'sum' => $this->sum,
                'date_start' => $this->date_start,
                'date_end' => $this->date_end,
                'budget_restriction' => $this->budget_restriction,
                'all_cost' => $this->all_cost,
                'all_location' => $this->all_location,
                'user_id_responsible' => $user->id,
                'status' => $this->budgetStatus()
            ]);
            $result = $model->save();
            if ($result) {
                BudgetLocation::deleteAll(['budget_id' => $model->id]);
                if (!$this->all_location) {
                    BudgetLocation::addLocations($model->id, $this->locations);
                }
                BudgetCostCenter::deleteAll(['budget_id' => $model->id]);
                BudgetSpecification::deleteAll(['budget_id' => $model->id]);
                $specificationToLocation = Specification::getListByLocation($this->locations, $this->all_location);
                $specification = Specification::find()
                    ->where(['in', 'id', ArrayHelper::getColumn($specificationToLocation, 'id')])
                    ->andWhere(['in', 'id', $this->specifications])
                    ->asArray()
                    ->all();
                BudgetSpecification::addSpecification($model->id, ArrayHelper::getColumn($specification, 'id'));
                if (!$this->all_cost) {
                    if ($this->all_location) {
                        $locationList = Locations::find()
                            ->where(['company_id' => $user->company_id])
                            ->asArray()
                            ->all();
                        if ($locationList) {
                            $location = ArrayHelper::getColumn($locationList, 'id');
                        } else {
                            $location = [0];
                        }
                    } else {
                        $location = $this->locations;
                    }
                    $costs = CostCenter::find()
                        ->where(['in', 'id', $this->cost_centers])
                        ->andWhere(['in', 'location_id', $location])
                        ->all();
                    $costs = ArrayHelper::getColumn($costs, 'id');
                    BudgetCostCenter::addCost($model->id, $costs);
                }
            }
            return $result;
        }
        return false;
    }

    /**
     * @return int
     */
    public function budgetStatus()
    {
        $time = time();
        if ($time > $this->date_start && $time < $this->date_end + 86400) {
            return Budget::STATUS_ACTIVE;
        } else {
            return Budget::STATUS_INACTIVE;
        }
    }
}
