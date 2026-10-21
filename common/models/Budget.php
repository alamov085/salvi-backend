<?php

namespace common\models;

use common\models\query\BudgetAQ;
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "budget".
 *
 * @property int $id
 * @property int $company_id
 * @property string $name
 * @property string $sum
 * @property int $date_start
 * @property int $date_end
 * @property int $budget_restriction
 * @property int $all_cost
 * @property int $user_id_responsible
 * @property int $status
 * @property int $created_at
 * @property BudgetCostCenter|ActiveQuery $costCenters
 * @property ActiveQuery|BudgetLocation $locations
 * @property ActiveQuery|User $responsibleUser
 * @property string $availableSum
 * @property string $usedSum
 * @property string $allSum
 * @property ActiveQuery|Locations $locationsData
 * @property ActiveQuery|Order $orders
 * @property History|ActiveQuery $history
 * @property ActiveQuery|BudgetSpecification $specifications
 * @property int $updated_at
 * @property int $all_location
 */
class Budget extends ActiveRecord
{
    const STATUS_DELETED = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 2;

    public $available;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'budget';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['company_id', 'date_start', 'date_end',
                'user_id_responsible', 'status', 'created_at', 'updated_at'], 'integer'],
            [['budget_restriction', 'all_cost', 'all_location'], 'boolean'],
            [['sum'], 'number'],
            [['name'], 'string', 'max' => 20],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'company_id' => 'Company ID',
            'name' => 'Name',
            'sum' => 'Sum',
            'date_start' => 'Date Start',
            'date_end' => 'Date End',
            'budget_restriction' => 'Budget Restriction',
            'all_cost' => 'All Cost',
            'user_id_responsible' => 'User Id Responsible',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return ActiveQuery | BudgetLocation
     */
    public function getLocations()
    {
        return $this->hasMany(BudgetLocation::class, ['budget_id' => 'id']);
    }

    /**
     * @return ActiveQuery | Locations
     * @throws InvalidConfigException
     */
    public function getLocationsData()
    {
        return $this->hasMany(Locations::class, ['id' => 'location_id'])
            ->viaTable(BudgetLocation::tableName(), ['budget_id' => 'id']);
    }

    /**
     * @return ActiveQuery | BudgetCostCenter
     */
    public function getCostCenters()
    {
        return $this->hasMany(BudgetCostCenter::class, ['budget_id' => 'id']);
    }

    /**
     * @param bool $runValidation
     * @param null $attributeNames
     * @return bool
     */
    public function save($runValidation = true, $attributeNames = null)
    {
        if ($this->isNewRecord) {
            $this->created_at = date('U');
        }
        $this->updated_at = date('U');
        return parent::save($runValidation, $attributeNames);
    }

    /**
     * @return ActiveQuery | User
     */
    public function getResponsibleUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id_responsible']);
    }

    /**
     * @return ActiveQuery | Order
     */
    public function getOrders()
    {
        return $this->hasMany(Order::class, ['budget_id' => 'id']);
    }

    /**
     * @return string
     */
    public function getAvailableSum()
    {
        $sum = 0;
        $orderStatus = [
            Order::STATUS_RECEIVED,
            Order::STATUS_AT_SUPPLIER,
            Order::STATUS_ON_APPROVE,
            Order::STATUS_AT_MANAGER
        ];
        if ($this->orders) {
            foreach ($this->orders as $order) {
                /** @var $order Order */
                if (in_array($order->status, $orderStatus)) {
                    $sum += $order->total_sum;
                }
            }
        }

        //$result = $this->sum - $sum;
        $result = $this->sum;
        return number_format($result, 2, '.', '');
    }

    public function getUsedSum()
    {
        $sum = 0;
        $orderStatus = [
            Order::STATUS_RECEIVED,
            Order::STATUS_AT_SUPPLIER,
            Order::STATUS_ON_APPROVE,
            Order::STATUS_AT_MANAGER
        ];
        if ($this->orders) {
            foreach ($this->orders as $order) {
                /** @var $order Order */
                if (in_array($order->status, $orderStatus)) {
                    $sum += $order->total_sum;
                }
            }
        }

        $result = $sum;
        return number_format($result, 2, '.', '');
    }

    /**
     * @param int $costCenterId
     * @param int $companyId
     * @param int $location_id
     * @return array|Budget[]|Contract[]|ActiveRecord[]
     * @throws Throwable
     */
    public static function findByCostCenter(int $costCenterId, int $companyId, int $location_id = 0)
    {
        /** @var  $user User */
        $user = Yii::$app->user->getIdentity();
        if ($location_id) {
            $budgetByLocation = BudgetLocation::find()
                ->select(['budget_id'])
                ->where(['location_id' => $location_id])
                ->asArray()
                ->all();
            $budgets = Budget::find()
                ->select(['id'])
                ->where(['company_id' => $user->company_id])
                ->andWhere(['all_location' => 1])
                ->all();
            if ($budgetByLocation && $budgets) {
                $budgetByLocationIds = ArrayHelper::merge(
                    ArrayHelper::getColumn($budgetByLocation, 'budget_id'),
                    ArrayHelper::getColumn($budgets, 'id')
                );
            } elseif ($budgetByLocation) {
                $budgetByLocationIds = ArrayHelper::getColumn($budgetByLocation, 'budget_id');
            } elseif ($budgets) {
                $budgetByLocationIds = ArrayHelper::getColumn($budgets, 'id');
            }
        }
        if (Yii::$app->user->can('budgetViewAll')) {
            $budget = Budget::find()
                ->where(['company_id' => $user->company_id])
                ->all();
        } elseif (Yii::$app->user->can('budgetViewLocation')) {
            $ids = BudgetLocation::find()
                ->where(['in', 'location_id', $user->allLocations])
                ->all();
            $budget = Budget::find()
                ->where(['company_id' => $user->company_id])
                ->andWhere(['in', 'id', ArrayHelper::getColumn($ids, 'budget_id')])
                ->orWhere([
                    'and',
                    ['company_id' => $user->company_id],
                    ['all_location' => 1]
                ])
                ->all();
        } elseif (Yii::$app->user->can('budgetViewCost')) {
            $costs = ArrayHelper::getValue($user, 'allCost', []);
            $locations = CostCenter::find()
                ->where(['in', 'id', $costs])
                ->all();
            if ($locations) {
                $ids = BudgetLocation::find()
                    ->where([
                        'in',
                        'location_id',
                        ArrayHelper::getColumn($locations, 'location_id')
                    ])
                    ->all();
                $budget = Budget::find()
                    ->where(['company_id' => $user->company_id])
                    ->andWhere(['in', 'id', ArrayHelper::getColumn($ids, 'budget_id')])
                    ->orWhere([
                        'and',
                        ['company_id' => $user->company_id],
                        ['all_location' => 1]
                    ])
                    ->all();

            } else {
                $budget = [];
            }
        } else {
            $budget = Budget::find()
                ->where(['company_id' => $user->company_id])
                ->andWhere(['user_id_responsible' => $user->id])
                ->all();
        }

        $query = self::find()
            ->joinWith(['costCenters', 'locations.location.costCenters'])
            ->where(['budget.company_id' => $companyId])
            ->andWhere([
                'or',
                ['budget_cost_center.cost_center_id' => $costCenterId],
                ['budget.all_cost' => 1],
            ])
            ->andWhere(['in', 'budget.id', ArrayHelper::getColumn($budget, 'id')]);
        if (isset($budgetByLocationIds) && $budgetByLocationIds) {
            $query->andWhere(['in', 'budget.id', $budgetByLocationIds]);
        }
        return $query->active()
            ->all();
    }

    /**
     * @return array|Budget[]|CostCenter[]|ActiveRecord[]
     */
    public function listOfCostCenters()
    {
        if ($this->all_cost) {
            if ($this->all_location) {
                $locations = Locations::find()
                    ->select(['id'])
                    ->where(['company_id' => $this->company_id])
                    ->asArray()
                    ->all();
                $locationsList = ArrayHelper::getColumn($locations, 'id');
            } else {
                $locationsList = ArrayHelper::getColumn($this->locations, 'location_id');
            }
            $costCenters = CostCenter::find()
                ->where(['in', 'location_id', $locationsList])
                ->all();
        } else {
            $listOfCost = ArrayHelper::getColumn($this->costCenters, 'cost_center_id');
            $costCenters = CostCenter::find()
                ->where(['in', 'id', $listOfCost])
                ->all();
        }
        return $costCenters;
    }

    public static function find()
    {
        return new BudgetAQ(get_called_class());
    }

    public function afterSave($insert, $changedAttributes)
    {
        if ($insert) {
            History::saveHistory(History::BUDGET, $this->id);
        }
        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * @return ActiveQuery | History
     */
    public function getHistory()
    {
        return $this->hasOne(History::class, ['entity_id' => 'id'])
            ->andOnCondition(['entity' => History::BUDGET]);
    }

    /**
     * @return ActiveQuery | BudgetSpecification
     */
    public function getSpecifications()
    {
        return $this->hasMany(BudgetSpecification::class, ['budget_id' => 'id']);
    }

    public static function budgetsInPeriod(int $from, int $to)
    {
        /** @var  $user User */
        $user = Yii::$app->user->getIdentity();
        $model = self::find()
            ->select(['id'])
            ->where([
                'or',
                [
                    'and',
                    ['<=', 'date_start', $from],
                    ['>=', 'date_end', $from]
                ],
                [
                    'and',
                    ['<=', 'date_start', $to],
                    ['>=', 'date_end', $to]
                ],
                [
                    'and',
                    ['>=', 'date_start', $from],
                    ['<=', 'date_end', $to]
                ],
                [
                    'and',
                    ['>=', 'date_start', $from],
                    ['>=', 'date_end', $to],
                    ['<=', 'date_start', $to]
                ]
            ])
            ->andWhere(['company_id' => $user->company_id])
            ->asArray()
            ->all();
        if ($model) {
            return ArrayHelper::getColumn($model, 'id');
        }
        return [];
    }
}
