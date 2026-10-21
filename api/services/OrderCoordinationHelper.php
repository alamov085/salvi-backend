<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 03.03.2020
 * Time: 12:31
 */

namespace api\services;

use api\helpers\NotificationHelper;
use common\models\AuthAssignment;
use common\models\Coordination;
use common\models\Order;
use common\models\OrderCoordination;
use common\models\OrderProduct;
use common\models\User;
use yii\helpers\ArrayHelper;

/**
 * Class OrderCoordinationHelper
 * @package api\services
 */
class OrderCoordinationHelper
{
    /** @var Order */
    private $order;

    /**
     * OrderCoordinationService constructor.
     * @param Order $order
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * @return array|OrderCoordination
     */
    private function getCurrentCoordination()
    {
        return OrderCoordination::find()
            ->where(['order_id' => $this->order->id])
            ->andWhere(['status' => 0])
            ->orderBy('order ASC')
            ->one();
    }

    /**
     * @return OrderProduct[]
     */
    private function getProducts()
    {
        return OrderProduct::find()
            ->where(['order_id' => $this->order->id])
            ->andWhere(['catalog' => 1])
            ->all();
    }

    /**
     * @return int
     */
    private function getAvailableBudgetSum()
    {
        $availableSum = 0;
        if ($this->order->budget) {
            $percent = 0;
            if ($this->order->budget->sum > 0) {
                $percent = ($this->order->budget->availableSum - $this->order->total_sum) / $this->order->budget->sum;
            }
            if ($this->order->status === Order::STATUS_OPEN) {
                $availableSum = $this->order->budget->availableSum - $this->order->total_sum;
            } elseif ($this->order->status === Order::STATUS_ON_APPROVE) {
                $availableSum = $this->order->budget->availableSum;
            }
            if ($percent <= 0.2) {
                NotificationHelper::soonEndBudget($this->order->budget);
                NotificationHelper::soonEndBudgetOwner($this->order->budget);
            }
        }
        return +$availableSum;
    }

    /**
     * @param $roles
     * @return array|AuthAssignment[]
     */
    private function getUsersByRole($roles)
    {
        return AuthAssignment::find()
            ->where(['in', 'item_name', ArrayHelper::getColumn($roles, 'role')])
            ->all();
    }

    public function goToNextStep(): void
    {
        $coordination = $this->getCurrentCoordination();
        $availableSum = $this->getAvailableBudgetSum();
        $product = $this->getProducts();
        if ($coordination) {
            if ($coordination->all_roles) {
                $usersIsset = $this->userIsset($this->getUsersByRole($coordination->roles));
            } else {
                $usersIsset = $this->userIsset($coordination->users);
            }

            if (($coordination->additional_type == Coordination::ADD_TYPE_WITHOUT_BUDGET && !$this->order->non_budget_order) ||
                ($coordination->additional_type == Coordination::ADD_TYPE_WITHOUT_BUDGET && $this->order->non_budget_order &&
                    $this->order->budget)) {
                $this->approve($coordination);
            } elseif (($coordination->additional_type == Coordination::ADD_TYPE_HIGHER_THAN_BUDGET && !$this->order->budget) ||
                ($coordination->additional_type == Coordination::ADD_TYPE_HIGHER_THAN_BUDGET && $this->order->budget &&
                    $availableSum >= 0)) {
                $this->approve($coordination);
            } elseif ($coordination->additional_type == Coordination::ADD_TYPE_CATALOG_PRODUCT && !$product) {
                $this->approve($coordination);
            } elseif (!$usersIsset) {
                $this->approve($coordination);
            } elseif ($coordination->type == Coordination::TYPE_BIGGER && $coordination->sum > $this->order->total_sum) {
                $this->approve($coordination);
            }
        }
    }

    /**
     * @param OrderCoordination $coordination
     * @param int $user_id
     */
    private function approve(OrderCoordination $coordination, int $user_id = 0)
    {
        if (!$coordination->approveStatus($user_id)) {
            $this->order->changeStatusOnAtManager();
        }
    }

    /**
     * @param $users
     * @return bool
     */
    private function userIsset($users)
    {
        if ($users) {
            $activeUsers = User::find()->where(['status' => User::STATUS_ACTIVE])
                ->andWhere(['in', 'id', ArrayHelper::getColumn($users, 'user_id')])
                ->all();
            if ($activeUsers) {
                return true;
            }
        }
        return false;
    }
}
