<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 23.05.2019
 * Time: 15:52
 */

namespace api\controllers\v1;

use api\controllers\RestController;
use api\helpers\ColorHelper;
use api\helpers\DashboardHelper;
use api\helpers\NumberHelper;
use common\helpers\GoogleMapsHelper;
use common\models\Budget;
use common\models\BudgetLocation;
use common\models\Category;
use common\models\Company;
use common\models\CompanyEntity;
use common\models\Locations;
use common\models\Order;
use common\models\OrderProduct;
use common\models\ProductCategory;
use common\models\User;
use Yii;
use yii\data\ArrayDataProvider;
use yii\helpers\ArrayHelper;

/**
 * Class DashboardController
 * @package api\controllers\v1
 */
class DashboardController extends RestController
{
    public function actionIndex()
    {
        $helper = (new DashboardHelper())->init();
        return $this->successResponse(['dashboard' => $helper]);
    }

    public function actionGetOrders()
    {
        /** @var  $user  User */
        $user = Yii::$app->user->getIdentity();
        $labels = ArrayHelper::getColumn(Order::statuses(), 'name');
        $colors = ArrayHelper::getColumn(Order::statuses(), 'color');
        $statuses = Order::statuses();
        $data = [];
        $total = 0;
        foreach ($statuses as $k => $status) {
            $ordersQuery = Order::find()->where(['status' => $k]);
            if ($user->company_id) {
                $ordersQuery->andWhere(['company_id' => $user->company_id]);
            }
            $orders = $ordersQuery->count();
            $data[] = $orders;
            $total += $orders;
        }
        return $this->successResponse(['labels' => $labels, 'orders' => $data, 'total' => $total, 'colors' => $colors]);
    }

    public function actionGetUsers()
    {
        /** @var  $user  User */
        $user = Yii::$app->user->getIdentity();
        $labels = [];
        $statuses = User::statuses();
        $data = [];
        $total = 0;
        foreach ($statuses as $k => $status) {
            $ordersQuery = User::find()->where(['status' => $k]);
            if ($user->company_id) {
                $ordersQuery->andWhere(['company_id' => $user->company_id]);
            }
            $orders = $ordersQuery->count();
            $data[] = $orders;
            $total += $orders;
            $labels[] = $status['name'];
        }
        return $this->successResponse(['labels' => $labels, 'users' => $data, 'total' => $total]);
    }

    public function actionByCategory()
    {
        /** @var  $user  User */
        $user = Yii::$app->user->getIdentity();
        $categories = Category::find()
            ->where(['is', 'parent_id', null])
            ->all();
        $names = [];
        $values = [];
        $percent = [];
        $totalSum = 0;
        if ($categories) {
            foreach ($categories as $category) {
                $names[] = $category->name;
                $totalQuery = OrderProduct::find()
                    ->where(['parent_category_uuid' => $category->uuid]);
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
                if ($user->company_id) {
                    $ordersQuery->andWhere(['company_id' => $user->company_id]);
                }
                $orders = $ordersQuery->asArray()->all();
                $totalQuery->andWhere(['in', 'order_id', ArrayHelper::getColumn($orders, 'id')]);
                $total = $totalQuery->sum('total_sum');
                $totalSum += NumberHelper::convert($total);
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
        return $this->successResponse(['labels' => $names, 'data' => $values, 'percent' => $percent]);
    }

    public function actionGetBudget()
    {
        $location_id = ArrayHelper::getValue($this->post, 'location_id', 0);
        $all_budget = ArrayHelper::getValue($this->post, 'all_budget', false);
        /** @var  $user User */
        $user = Yii::$app->user->getIdentity();
        $budgetsQuery = Budget::find()
            ->joinWith(['orders']);
        if ($user->company_id) {
            $budgetsQuery->where(['budget.company_id' => $user->company_id]);
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
        $budgetsQuery->andWhere(['in', 'budget.status', [Budget::STATUS_ACTIVE, Budget::STATUS_INACTIVE]]);
        $budgetsQuery->orderBy('date_start ASC');
        $budgets = $budgetsQuery->all();
        $dataSets = [];
        if ($all_budget) {
            if ($budgets) {
                $set = [];
                $sqlFormat = '"%Y-%m-%d"';
                $orders = Order::find()
                    ->select([
                        'DATE_FORMAT(FROM_UNIXTIME(created_at),' . $sqlFormat . ') as date_string',
                        'count(1)',
                        'SUM(`total_sum`) as total_sum'
                    ])
                    ->where(['in', 'budget_id', ArrayHelper::getColumn($budgets, 'id')])
                    ->andWhere(['in', 'status', [
                        Order::STATUS_RECEIVED,
                        Order::STATUS_AT_SUPPLIER,
                        Order::STATUS_ON_APPROVE,
                        Order::STATUS_AT_MANAGER
                    ]])
                    ->groupBy('date_string')
                    ->asArray()
                    ->all();
                if ($orders) {
                    foreach ($orders as $order) {
                        $data = [
                            'x' => strtotime($order['date_string']) * 1000,
                            'y' => $order['total_sum'],
                            'type' => 2
                        ];
                        $set[] = $data;
                    }
                }
                $color = ColorHelper::generateHex();
                $dataSets[0] = [
                    'label' => 'Загальний бюджет',
                    'backgroundColor' => ColorHelper::hex2rgba($color, 0.5),
                    'borderColor' => $color,
                    'data' => [],
                    'fill' => true,
                ];
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
                }
                usort($set, function ($item1, $item2) {
                    return $item1['x'] <=> $item2['x'];
                });
                $raw = [];
                $sum = 0;
                $i = 0;
                $minus = 0;
                $true = 0;
                $totalCount = count($set);
                foreach ($set as $k => $item) {
                    $timestamp = $item['x'];  //Временная метка
                    $pointType = $item['type']; //Тип события, 1 - начало бюджета, 2 - заказ, 3 - конец бюджета
                    $pointBalance = $item['y']; //Цена конкретного собыия
                    if ($k > 0 && isset($set[$k - 1]['x'])) {
                        $prevTimestamp = $set[$k - 1]['x']; //Предыдущие событие
                    }

                    if ($pointType == 1) {
                        $sum += $pointBalance;
                        $true += $pointBalance;
                    } elseif ($pointType == 2) {
                        $sum -= $pointBalance;
                        $true -= $pointBalance;
                    } elseif ($pointType == 3) {
                        $minus += $pointBalance;
                        $true -= $pointBalance;
                        $sum -= $pointBalance;
                    }
                    if ($pointType == 1 && $k == 0) {
                        $raw[$timestamp - 1000] = [
                            'x' => $timestamp - 1000,
                            'y' => NumberHelper::convert(0),
                        ];
                    }

//                    if ($pointType == 1) {
//                        if (!isset($raw[$timestamp - 1000])) {
//                            if ($k > 1 && isset($prevTimestamp)) {
//                                if (isset($raw[$prevTimestamp + 1000])) {
//                                    $temp = $raw[$prevTimestamp + 1000]['y'];
//                                } elseif (isset($raw[$prevTimestamp - 1000])) {
//                                    $temp = $raw[$prevTimestamp - 1000]['y'];
//                                } elseif (isset($raw[$prevTimestamp])) {
//                                    $temp = $raw[$prevTimestamp]['y'];
//                                } else {
//                                    $temp = 0;
//                                }
//                            } else {
//                                $temp = 0;
//                            }
//                            $raw[$timestamp - 1000] = [
//                                'x' => $timestamp - 1000,
//                                'y' => NumberHelper::convert($temp),
//                            ];
//                        }
//                    }

//                    if ($pointType == 3) {
//                        if (isset($prevTimestamp) && isset($raw[$prevTimestamp + 1000]) &&
//                            $raw[$prevTimestamp + 1000]['x'] == $timestamp + 1000) {
//                            $raw[$prevTimestamp + 1000] = [
//                                'x' => $timestamp + 1000,
//                                'y' => NumberHelper::convert($true),
//                            ];
//                        } else {
//                            $raw[$timestamp + 1000] = [
//                                'x' => $timestamp + 1000,
//                                'y' => NumberHelper::convert($true),
//                            ];
//                        }
//                    }

//                    if (isset($prevTimestamp) && isset($raw[$prevTimestamp]) &&
//                        $timestamp > $raw[$prevTimestamp]['x']) {
//                        $sum -= $minus;
//                        $minus = 0;
//                    }

                    if (isset($prevTimestamp) && isset($raw[$prevTimestamp]) &&
                        $raw[$prevTimestamp]['x'] == $timestamp) {
                        $raw[$prevTimestamp] = [
                            'x' => $timestamp,
                            'y' => NumberHelper::convert($sum),
                        ];
                    } else {
                        $raw[$timestamp] = [
                            'x' => $timestamp,
                            'y' => NumberHelper::convert($sum),
                        ];
                        $i++;
                    }
                }
                usort($raw, function ($item1, $item2) {
                    return $item1['x'] <=> $item2['x'];
                });
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
        return $this->successResponse(['sets' => $dataSets]);
    }

    public function actionMap()
    {
        /** @var  $user User */
        $user = Yii::$app->user->getIdentity();

        $locationQuery = Locations::find()->joinWith(['orders']);
        if ($user->company_id) {
            $locationQuery->andWhere(['locations.company_id' => $user->company_id]);
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
        return $this->successResponse(['data' => $data]);
    }

    public function actionCounter()
    {
        $data = [];

        /** @var  $user  User */
        $user = Yii::$app->user->getIdentity();

        $activeOrderQuery = Order::find()
            ->where(['!=', 'status', Order::STATUS_RECEIVED]);
        if ($user->company_id) {
            $activeOrderQuery->andWhere(['company_id' => $user->company_id]);
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
        if ($user->company_id) {
            $avgOrdersQuery->andWhere(['company_id' => $user->company_id]);
        }
        $avgOrders = $avgOrdersQuery->average('avg_time');
        $avgOrders = (integer)$avgOrders;
        $data[1] = [
            'name' => 'Сер. час виконання',
            'tooltip' => 'Середній час, що пішов на період від створення замовлення до його отримання',
            'num' => [
                gmdate('H', $avgOrders),
                gmdate('i', $avgOrders),
                gmdate('s', $avgOrders),
            ],
            'date' => true,
            'percent' => false
        ];


        $avgProductQuery = Order::find()->joinWith(['product']);
        if ($user->company_id) {
            $avgProductQuery->andWhere(['order.company_id' => $user->company_id]);
        }
        $avgProduct = $avgProductQuery->all();
        $totalProduct = 0;
        if ($avgProduct) {
            $count = 0;
            $totalProduct = 0;
            foreach ($avgProduct as $item) {
                $count++;
                $totalProduct += count($item->product);
            }
            if ($count > 0 && $totalProduct > 0) {
                $totalProduct = $totalProduct / $count;
            }
        }
        $data[2] = [
            'name' => 'Сер. кільксть позицій',
            'tooltip' => 'Середня кількість унікальних товарів в замовленні',
            'num' => (integer)$totalProduct,
            'date' => false,
            'percent' => false
        ];


        $receivedOrderQuery = Order::find()->where(['status' => Order::STATUS_RECEIVED]);
        if ($user->company_id) {
            $avgProductQuery->andWhere(['order.company_id' => $user->company_id]);
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

        return $this->successResponse(['data' => $data]);
    }
}
