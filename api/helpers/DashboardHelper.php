<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 19.11.2019
 * Time: 17:11
 */

namespace api\helpers;

use common\helpers\GoogleMapsHelper;
use common\models\Budget;
use common\models\BudgetLocation;
use common\models\Category;
use common\models\Locations;
use common\models\Order;
use common\models\OrderProduct;
use common\models\User;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * Class DashboardHelper
 * @package api\helpers
 */
class DashboardHelper
{
    public $orders;
    public $users;
    public $categories;
    public $map = [];
    public $budgets = [];
    public $counter;

    /** @var  $user  User */
    private $user;

    public function init()
    {
        $this->user = Yii::$app->user->getIdentity();
        $this->ordersStat();
        $this->usersStat();
        $this->categoriesStat();
//        $this->mapStat();
//        $this->budgetStat();
        $this->counterStat();
        return $this;
    }

    private function ordersStat()
    {
        $labels = ArrayHelper::getColumn(Order::statuses(), 'name');
        $colors = ArrayHelper::getColumn(Order::statuses(), 'color');
        $statuses = Order::statuses();
        $data = [];
        $sum = [];
        $total = 0;
        foreach ($statuses as $k => $status) {
            $ordersQuery = Order::find()
                ->select(['SUM(total_sum) as sum', 'COUNT(*) as qty'])
                ->where(['status' => $k]);
            if ($this->user->company_id) {
                $ordersQuery->andWhere(['company_id' => $this->user->company_id]);
            }
            $orders = $ordersQuery->asArray()->one();
            $data[] = $orders['qty'];
            $sum[] = (float)$orders['sum'];
            $total += $orders['qty'];
        }
        $this->orders = ['labels' => $labels, 'orders' => $data, 'total' => $total, 'colors' => $colors, 'sum' => $sum];
    }

    private function usersStat()
    {
        $labels = [];
        $statuses = User::statuses();
        $data = [];
        $total = 0;
        foreach ($statuses as $k => $status) {
            $ordersQuery = User::find()->where(['status' => $k]);
            if ($this->user->company_id) {
                $ordersQuery->andWhere(['company_id' => $this->user->company_id]);
            }
            $orders = $ordersQuery->count();
            $data[] = $orders;
            $total += $orders;
            $labels[] = $status['name'];
        }
        $this->users = ['labels' => $labels, 'users' => $data, 'total' => $total];
    }

    private function categoriesStat()
    {
        $categories = Category::find()
            ->where(['is', 'parent_id', null])
            ->all();
        $names = [];
        $values = [];
        $percent = [];
        $totalSum = 0;
        if ($categories) {
            $ordersQuery = Order::find()
                ->where([
                    'in',
                    'status',
                    [
                        Order::STATUS_RECEIVED,
                        Order::STATUS_AT_SUPPLIER,
                        Order::STATUS_AT_MANAGER
                    ]
                ]);
            if ($this->user->company_id) {
                $ordersQuery->andWhere(['company_id' => $this->user->company_id]);
            }
            $orders = $ordersQuery->asArray()->all();
            foreach ($categories as $category) {
                $names[] = $category->name;
                $totalQuery = OrderProduct::find()
                    ->where(['parent_category_uuid' => $category->uuid]);
                $totalQuery->andWhere(['in', 'order_id', ArrayHelper::getColumn($orders, 'id')]);
                $total = $totalQuery->sum('total_sum');
                $total = NumberHelper::convert($total);
                $totalSum += $total;
                $values[] = ($total) ? $total : 0;
            }
        }
        $unset = [];
        foreach ($values as $k => $val) {
            if ($val) {
                $temp = ($val / $totalSum) * 100;
                $percent[] = number_format($temp, 2, '.', ',');
            } else {
                $percent[] = 0;
                $unset[] = $k;
            }
        }
        if (!empty($unset)) {
            foreach ($unset as $delete) {
                unset($names[$delete], $values[$delete], $percent[$delete]);
            }
        }
        if (!empty($names)) {
            $names = array_values($names);
            $values = array_values($values);
            $percent = array_values($percent);
        }
        $this->categories = [
            'labels' => $names,
            'data' => $values,
            'percent' => $percent,
            'totalSum' => NumberHelper::convert($totalSum)
        ];
    }

    private function mapStat()
    {
        $locationQuery = Locations::find()->joinWith(['orders']);
        if ($this->user->company_id) {
            $locationQuery->andWhere(['locations.company_id' => $this->user->company_id]);
        }
        $locations = $locationQuery->all();
        $data = [];
        if ($locations) {
            foreach ($locations as $location) {
                if ($location->lat) {
                    if ($location->orders) {
                        $sum = 0;
                        $count = 0;
                        foreach ($location->orders as $order) {
                            /** @var $order Order */
                            if (in_array($order->status, [Order::STATUS_AT_SUPPLIER, Order::STATUS_RECEIVED])) {
                                $count++;
                                $sum += $order->total_sum;
                            }
                        }
                        if ($count) {
                            $data[] = [
                                "co_loc_n" => ArrayHelper::getValue(GoogleMapsHelper::isoCode(), $location->region),
                                "MAIL_ST_PROV_C" => $location->region,
                                "LNGTD_I" => $location->lng,
                                "LATTD_I" => $location->lat,
                                "mail_city_n" => $location->city,
                                "count" => $sum,
                                "total_orders" => $count
                            ];
                        }
                    }
                }
            }
        }
        $this->map = ['data' => $data];
    }

    private function budgetStat()
    {
        $location_id = ArrayHelper::getValue($this->post, 'location_id', 0);
        $all_budget = ArrayHelper::getValue($this->post, 'all_budget', false);

        $budgetsQuery = Budget::find()
            ->joinWith(['orders']);
        if ($this->user->company_id) {
            $budgetsQuery->where(['budget.company_id' => $this->user->company_id]);
        }
        if ($location_id) {
            $locations = BudgetLocation::find()
                ->where(['location_id' => $location_id])
                ->all();
            if ($locations) {
                $budgetsQuery->andWhere(['in', 'budget.id', ArrayHelper::getColumn($locations, 'budget_id')]);
            } else {
                $budgetsQuery->andWhere(['budget.id' => 0]);
            }
        }
        $budgetsQuery->orderBy('date_start ASC');
        $budgets = $budgetsQuery->all();
        $dataSets = [];
        if ($all_budget) {
            if ($budgets) {
                $sum = 0;
                $color = ColorHelper::generateHex();
                $dataSets[0] = [
                    'label' => 'Загальний бюджет',
                    'backgroundColor' => ColorHelper::hex2rgba($color, 0.5),
                    'borderColor' => $color,
                    'data' => [],
                    'fill' => true,
                ];
                $set = [];
                foreach ($budgets as $budget) {
                    $set[] = [
                        'x' => $budget->date_start * 1000,
                        'y' => $budget->sum,
                        'name' => $budget->name,
                        'type' => 1
                    ];
                    $set[] = [
                        'x' => ($budget->date_end + 86399) * 1000,
                        'y' => $budget->availableSum,
                        'name' => $budget->name,
                        'type' => 3
                    ];
                    $sqlFormat = '"%Y-%m-%d"';
                    $orders = Order::find()
                        ->select([
                            'DATE_FORMAT(FROM_UNIXTIME(created_at),' . $sqlFormat . ') as date_string',
                            'count(1)',
                            'SUM(`total_sum`) as total_sum'
                        ])
                        ->where(['budget_id' => $budget->id])
                        ->andWhere(['in', 'status', [
                            Order::STATUS_RECEIVED,
                            Order::STATUS_AT_SUPPLIER,
                            Order::STATUS_ON_APPROVE,
                            Order::STATUS_AT_MANAGER
                        ]])
                        ->groupBy('date_string')
                        ->asArray()
                        ->all();
                    $sum = NumberHelper::convert($sum);
                    if ($orders) {
                        foreach ($orders as $order) {
                            $sum -= NumberHelper::convert($order['total_sum']);
                            $data = [
                                'x' => strtotime($order['date_string']) * 1000,
                                'y' => $order['total_sum'],
                                'type' => 2
                            ];
                            $set[] = $data;
                        }
                    }
                }
                usort($set, function ($item1, $item2) {
                    return $item1['x'] <=> $item2['x'];
                });
                $raw = [];
                $sum = 0;
                $i = 0;
                $totalSet = count($set);
                foreach ($set as $k => $item) {
                    if ($item['type'] == 1) {
                        $sum += $item['y'];
                    } elseif ($item['type'] == 2) {
                        $sum -= $item['y'];
                    } elseif ($item['type'] == 3) {
                        $sum = $raw[$i - 1]['y'];
                    }
                    if ($k >= 1 && $set[$k - 1]['type'] == 3) {
                        $sum -= $set[$k - 1]['y'];
                    }
                    if ($k + 1 == $totalSet && $item['type'] == 3) {
                        $sum -= $item['y'];
                    }
                    if (isset($raw[$i - 1]) && $raw[$i - 1]['x'] == $item['x'] &&
                        ($item['type'] == 1 || $item['type'] == 3)) {
                        $raw[$i - 1] = [
                            'x' => $item['x'],
                            'y' => NumberHelper::convert($sum),
                        ];
                    } else {
                        $raw[$i] = [
                            'x' => $item['x'],
                            'y' => NumberHelper::convert($sum),
                        ];
                        $i++;
                    }
                }
                $dataSets[0]['data'] = $raw;
            }
        } else {
            if ($budgets) {
                foreach ($budgets as $k => $budget) {
                    $color = ColorHelper::generateHex();
                    $dataSets[$k] = [
                        'label' => $budget->name,
                        'backgroundColor' => ColorHelper::hex2rgba($color, 0.5),
                        'borderColor' => $color,
                        'data' => [],
                        'fill' => true,
                    ];
                    $data = [
                        'x' => $budget->date_start * 1000,
                        'y' => NumberHelper::convert($budget->sum)
                    ];
                    $dataSets[$k]['data'][] = $data;
                    $orders = Order::find()
                        ->select([
                            'id',
                            'created_at',
                            'total_sum',
                        ])
                        ->where(['budget_id' => $budget->id])
                        ->andWhere(['in', 'status', [
                            Order::STATUS_RECEIVED,
                            Order::STATUS_AT_SUPPLIER,
                            Order::STATUS_ON_APPROVE,
                            Order::STATUS_AT_MANAGER
                        ]])
                        ->orderBy('id ASC')
                        ->asArray()
                        ->all();
                    $sum = NumberHelper::convert($budget->sum);
                    if ($orders) {
                        foreach ($orders as $order) {
                            $sum -= NumberHelper::convert($order['total_sum']);
                            $data = [
                                'x' => $order['created_at'] * 1000,
                                'y' => NumberHelper::convert($sum)
                            ];
                            $dataSets[$k]['data'][] = $data;
                        }
                    }
                    $data = [
                        'x' => $budget->date_end * 1000,
                        'y' => NumberHelper::convert($budget->availableSum)
                    ];
                    $dataSets[$k]['data'][] = $data;
                }
            }
        }
        $this->budgets = ['sets' => $dataSets];
    }

    public function counterStat()
    {
        $data = [];

        $activeOrderQuery = Order::find()
            ->where(['!=', 'status', Order::STATUS_RECEIVED]);
        if ($this->user->company_id) {
            $activeOrderQuery->andWhere(['company_id' => $this->user->company_id]);
        }
        $activeOrder = $activeOrderQuery->count();
        $data[0] = [
            'name' => 'Активних замовлень',
            'tooltip' => 'Кількість активних замовлень на поточний момент',
            'num' => $activeOrder,
            'date' => false,
            'percent' => false
        ];


        $avgOrdersQuery = Order::find()->where(['!=', 'avg_time', 0]);
        if ($this->user->company_id) {
            $avgOrdersQuery->andWhere(['company_id' => $this->user->company_id]);
        }
        $avgOrders = $avgOrdersQuery->average('avg_time');
        $avgOrders = (integer)$avgOrders;
        $data[1] = [
            'name' => 'Сер. час виконання',
            'tooltip' => 'Середній час, що знадобився на період від створення замовлення до його отримання',
            'num' => [
                gmdate('H', $avgOrders),
                gmdate('i', $avgOrders),
                gmdate('s', $avgOrders),
            ],
            'date' => true,
            'percent' => false
        ];


        $avgProductQuery = Order::find();
        if ($this->user->company_id) {
            $avgProductQuery->andWhere(['order.company_id' => $this->user->company_id]);
        }
        $orders = $avgProductQuery->all();
        $subQuery = OrderProduct::find()
            ->select('COUNT(id) as total')
            ->where(['in', 'order_id', ArrayHelper::getColumn($orders, 'id')])
            ->groupBy('order_id');
        $rows = (new \yii\db\Query())
            ->select(['AVG(total) as total'])
            ->from($subQuery)
            ->one();
        if (isset($rows['total'])) {
            $totalProduct = $rows['total'];
        } else {
            $totalProduct = 0;
        }
        $data[2] = [
            'name' => 'Сер. кількість позицій',
            'tooltip' => 'Середня кількість унікальних товарів в замовленні',
            'num' => (integer)$totalProduct,
            'date' => false,
            'percent' => false
        ];


        $receivedOrderQuery = Order::find()->where(['status' => Order::STATUS_RECEIVED]);
        if ($this->user->company_id) {
            $receivedOrderQuery->andWhere(['order.company_id' => $this->user->company_id]);
        }
        $receivedOrder = $receivedOrderQuery->count();
        $totalOrders = $receivedOrder + $activeOrder;
        $makeOrder = 0;
        if ($totalOrders > 0) {
            $makeOrder = ($receivedOrder / $totalOrders) * 100;
        }
        $data[3] = [
            'name' => 'Процент виконаних замовлень',
            'tooltip' => 'Частка виконаних замовлень з усіх існуючих на даний момент замовлень',
            'num' => number_format($makeOrder, 1, '.', ''),
            'date' => false,
            'percent' => true
        ];

        $this->counter = $data;
    }
}
