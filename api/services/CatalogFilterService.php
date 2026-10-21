<?php


namespace api\services;

use common\models\ProductFilter;
use Yii;
use yii\db\ActiveQuery;
use yii\db\Query;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use api\helpers\AppRequestHelper;

class CatalogFilterService
{
    protected $requestFilters = [];

    protected $productIdsQuery;

    protected $priceFrom = null;
    protected $priceTo = null;


    public function __construct (Query $productIdsQuery)
    {
        $this->productIdsQuery = clone $productIdsQuery;
        $this->requestFilters = $this->parseRequestFilters();
    }

    public function getFilters(): array
    {
        $filters = $this->buildFilters();

        $filters[] = $this->buildPriceFilter();

        return $filters;
    }

    public function applyToProductQuery (ActiveQuery $query): ActiveQuery
    {
        if ( !empty($this->requestFilters))
        {
            $this->appendSelectedFilters($query, $this->requestFilters);
        }

        $this->applyRangeFilters($query);

        return $query;
    }

    private function applyRangeFilters(ActiveQuery $query): void
    {
        if ($this->priceFrom > 0)
        {
            $query->andWhere(['>=', 'product.buy_price', $this->priceFrom]);
        }

        if ($this->priceTo > 0)
        {
            $query->andWhere(['<=', 'product.buy_price', $this->priceTo]);
        }
    }

    private function buildFilters (): array
    {
        $groups = $this->getGroups();

        $result = [];

        foreach ($groups as $group)
        {
            $filters = $this->requestFilters;
            unset($filters[$group['filter_code']]);

            $rows = $this->loadValues(
                $group['filter_code'],
                $filters
            );

            $result[] = [
                'code' => $group['filter_code'],
                'name' => $group['filter_name'],
                'values' => $rows
            ];
        }

        return $result;
    }

    private function buildPriceFilter(): array
    {
        $query = clone $this->productIdsQuery;

        if (!empty($this->requestFilters))
        {
            $query->from(['product' => $query]);

            foreach ($this->requestFilters as $code => $values)
            {
                $alias = 'pf_' . $code;

                $query->innerJoin(
                    ProductFilter::tableName() . " $alias",
                    "$alias.product_id=product.id"
                );

                $query->andWhere([
                    "$alias.filter_code" => $code
                ]);

                $query->andWhere([
                    'IN',
                    "$alias.filter_value_code",
                    $values
                ]);
            }
        }

        $query1 = clone $query;

        $query1->select([
            'product.id',
            'product.buy_price',
        ]);

        $row =  (new Query())
            ->from(['p' => $query1])
            ->select([
                'min' => 'MIN(p.buy_price)',
                'max' => 'MAX(p.buy_price)',
            ])
            ->one();

        return [
            'code' => 'price',
            'name' => 'Цена',
            'min' => (float)$row['min'],
            'max' => (float)$row['max'],
        ];
    }

    private function getGroups (): array
    {
        return ProductFilter::find()
            ->select([
                'filter_code',
                'filter_name'
            ])
            ->where([
                'product_id' => $this->productIdsQuery
            ])
            ->groupBy([
                'filter_code',
                'filter_name'
            ])
            ->orderBy([
                'filter_name' => SORT_ASC
            ])
            ->asArray()
            ->all();
    }

    private function loadValues (string $filterCode, array $filters): array
    {
        $query = ProductFilter::find()->alias('pf');

        $query->where([
            'pf.product_id' => $this->productIdsQuery
        ]);

        $this->appendFilterConditions($query, $filters);

        $query->andWhere([
            'pf.filter_code' => $filterCode
        ]);

        return $query
            ->select([
                'pf.filter_value_code as code',
                'pf.filter_value as value',
                'count' => 'COUNT(DISTINCT pf.product_id)'
            ])
            ->groupBy([
                'pf.filter_value_code',
                'pf.filter_value'
            ])
            ->orderBy([
                new Expression('CAST(REPLACE(pf.filter_value,",",".") AS DECIMAL(10,2))'),
                'pf.filter_value' => SORT_ASC
            ])
            ->asArray()
            ->all();
    }

    private function appendFilterConditions (ActiveQuery $query, array $filters): void
    {
        $i = 0;

        foreach ($filters as $code => $values)
        {
            $alias = 'f' . $i++;

            $query->innerJoin(
                ProductFilter::tableName() . ' ' . $alias,
                $alias . '.product_id=pf.product_id'
            );

            $query->andWhere([
                $alias . '.filter_code' => $code
            ]);

            $query->andWhere([
                'IN',
                $alias . '.filter_value_code',
                $values
            ]);
        }
    }

    private function appendSelectedFilters (ActiveQuery $query, array $filters): void
    {
        $i = 0;

        foreach ($filters as $filterCode => $values)
        {
            if (empty($values))
            {
                continue;
            }

            $alias = 'pf' . $i++;

            $query->innerJoin(
                ProductFilter::tableName() . ' ' . $alias,
                $alias . '.product_id=product.id'
            );

            $query->andWhere([
                $alias . '.filter_code' => $filterCode
            ]);

            $query->andWhere([
                'IN',
                $alias . '.filter_value_code',
                $values
            ]);
        }

        $query->addGroupBy('product.id');
    }

    private function parseRequestFilters (): array
    {
        $AppRequestHelper = new AppRequestHelper();

        $this->priceFrom = (float)$AppRequestHelper->getFromPost('price_from', 0);

        $this->priceTo = (float)$AppRequestHelper->getFromPost('price_to', 0);

        $filters = $AppRequestHelper->getFromPost('filters', []);

        if ( !is_array($filters))
        {
            $filters = [];
        }

        $result = [];

        foreach ($filters as $filterCode => $values)
        {
            if ( !is_array($values))
            {
                $values = explode(',', $values);
            }

            $values = array_filter(array_map('trim', $values));

            if ( !empty($values))
            {
                $result[$filterCode] = array_unique($values);
            }
        }

        return $result;
    }

    public static function applySpecificationFilter(ActiveQuery $query, array $specifications, array $args): ActiveQuery
    {
        if (empty($specifications))
        {
            if(!is_null($args['specification_only']))
            {
                if (($args['specification_only'] ?? 1) != 0)
                {
                    $query
                        ->innerJoin(
                            'specification_product sp_filter',
                            'sp_filter.product_id = product.id'
                        )
                        ->andWhere([
                            'sp_filter.specification_id' => 0
                        ]);
                }
            }

            return $query->andWhere([
                'product.additional_assortment' => 0
            ]);
        }


        $specIds = ArrayHelper::getColumn($specifications, 'id');

        // Только товары спецификации
        if (
            !Yii::$app->user->can('orderShowBtn')
            && !Yii::$app->user->can('catalogShowBtn')
            && Yii::$app->user->can('viewCatalog')
        )
        {
            return $query
                ->innerJoin(
                    'specification_product sp_filter',
                    'sp_filter.product_id = product.id'
                )
                ->andWhere([
                    'sp_filter.specification_id' => $specIds
                ]);
        }

        $condition = [
            'OR',
            [
                'exists',
                (new Query())
                    ->select(new Expression('1'))
                    ->from('specification_product sp')
                    ->where('sp.product_id = product.id')
                    ->andWhere([
                        'sp.specification_id' => $specIds
                    ])
            ]
        ];

        if (($args['specification_only'] ?? 0) != 1)
        {
            $condition[] = [
                'product.additional_assortment' => 0
            ];
        }

        return $query->andWhere($condition);
    }

    public static function loadSpecificationRelation (ActiveQuery $query, array $specifications): ActiveQuery
    {
        if (empty($specifications))
        {
            return $query->with([
                'specificationsProduct' => function ($query)
                {
                    $query->onCondition([
                        'specification_product.specification_id' => 0
                    ]);
                }
            ]);
        }

        $specIds = ArrayHelper::getColumn($specifications, 'id');

        return $query->with([
            'specificationsProduct' => function ($query) use ($specIds)
            {
                $query->onCondition([
                    'specification_product.specification_id' => $specIds
                ]);
            }
        ]);
    }

}