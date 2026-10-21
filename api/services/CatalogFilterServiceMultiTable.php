<?php


namespace api\services;

use Yii;
use yii\db\ActiveQuery;
use yii\db\Query;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use api\helpers\AppRequestHelper;
use common\models\ProductFilters;
use common\models\ProductProperty;
use common\models\ProductPropertyValue;

class CatalogFilterServiceMultiTable
{
    protected $requestFilters = [];

    protected $productIdsQuery;

    protected $priceFrom = 0;

    protected $priceTo = 0;

    public function __construct ($productIdsQuery)
    {
        $this->productIdsQuery = clone $productIdsQuery;
        $this->requestFilters = $this->parseRequestFilters();
    }

    public function getFilters (): array
    {
        $filters = $this->buildFilters();

        $filters[] = $this->buildPriceFilter();

        return $filters;
    }

    private function parseRequestFilters (): array
    {
        $request = new AppRequestHelper();

        $this->priceFrom = (float)$request->getFromPost('price_from', 0);
        $this->priceTo = (float)$request->getFromPost('price_to', 0);

        $filters = $request->getFromPost('filters', []);

        if ( !is_array($filters))
        {
            return [];
        }

        $result = [];

        foreach ($filters as $propertyId => $values)
        {
            $propertyId = (int)$propertyId;

            if ($propertyId <= 0)
            {
                continue;
            }

            if ( !is_array($values))
            {
                $values = explode(',', $values);
            }

            $values = array_unique(array_filter(array_map('intval', $values)));

            if ( !empty($values))
            {
                $result[$propertyId] = $values;
            }
        }

        return $result;
    }

    private function buildFilters (): array
    {
        $groups = $this->getGroups();
        $result = [];

        foreach ($groups as $group)
        {
            $filters = $this->requestFilters;
            unset($filters[$group['id']]);

            $result[] = [
                'id' => (int)$group['id'],
                'slug' => $group['slug'],
                'name' => $group['name'],
                'values' => $this->loadValues((int)$group['id'], $filters)
            ];
        }

        return $result;
    }

    private function getGroups (): array
    {
        return (new Query())
            ->select([
                'pp.id',
                'pp.name',
                'pp.slug'
            ])
            ->distinct()
            ->from(['pp' => ProductProperty::tableName()])
            ->innerJoin(
                ['pf' => ProductFilters::tableName()],
                'pf.property_id=pp.id'
            )
            ->where([
                'pf.product_id' => $this->productIdsQuery
            ])
            ->orderBy([
                'pp.sort' => SORT_ASC,
                'pp.name' => SORT_ASC
            ])
            ->all();
    }

    private function loadValues (int $propertyId, array $filters): array
    {
        $query = (new Query())
            ->from(['pf' => ProductFilters::tableName()])
            ->innerJoin(
                ['pv' => ProductPropertyValue::tableName()],
                'pv.id=pf.value_id'
            )
            ->where([
                'pf.product_id' => $this->productIdsQuery,
                'pf.property_id' => $propertyId
            ]);

        $this->appendFilterConditions($query, $filters);

        $values = $query
            ->select([
                'id' => 'pv.id',
                'slug' => 'pv.slug',
                'value' => 'pv.name',
                'count' => new Expression('COUNT(DISTINCT pf.product_id)')
            ])
            ->groupBy([
                'pv.id',
                'pv.slug',
                'pv.name',
                'pv.sort'
            ])
            ->all();

        usort($values, function ($a, $b) {
            return strnatcmp(mb_strtolower($a['value'], 'UTF-8'), mb_strtolower($b['value'], 'UTF-8'));
        });

        return $values;
    }

    private function buildPriceFilter(): array
    {
        $productIdsQuery = clone $this->productIdsQuery;

        if (!empty($this->requestFilters))
        {
            $query = (new Query())
                ->from(['product' => $productIdsQuery]);

            $i = 0;

            foreach ($this->requestFilters as $propertyId => $valueIds)
            {
                if (empty($valueIds))
                {
                    continue;
                }

                $alias = 'pf' . $i++;

                $query->innerJoin(
                    ProductFilters::tableName() . ' ' . $alias,
                    $alias . '.product_id = product.id'
                );

                $query->andWhere([
                    $alias . '.property_id' => $propertyId
                ]);

                $query->andWhere([
                    'IN',
                    $alias . '.value_id',
                    $valueIds
                ]);
            }

            $productIdsQuery = $query;
        }

        $productIdsQuery->select([
            'product.id'
        ]);

        $row = (new Query())
            ->from(['ids' => $productIdsQuery])
            ->innerJoin(
                'product p',
                'p.id = ids.id'
            )
            ->select([
                'min' => new Expression('MIN(p.buy_price)'),
                'max' => new Expression('MAX(p.buy_price)')
            ])
            ->one();

        return [
            'code' => 'price',
            'name' => 'Цена',
            'min' => (float)$row['min'],
            'max' => (float)$row['max']
        ];
    }

    private function appendFilterConditions (Query $query, array $filters, string $productField = 'pf.product_id'): void
    {
        $i = 0;

        foreach ($filters as $propertyId => $valueIds)
        {
            if (empty($valueIds))
            {
                continue;
            }

            $alias = 'f' . $i++;

            $query->innerJoin(
                ProductFilters::tableName() . ' ' . $alias,
                $alias . '.product_id=' . $productField
            );

            $query->andWhere([
                $alias . '.property_id' => $propertyId
            ]);

            $query->andWhere([
                'IN',
                $alias . '.value_id',
                $valueIds
            ]);
        }
    }

    public function applyToProductQuery(ActiveQuery $query): ActiveQuery
    {
        if (!empty($this->requestFilters))
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

    private function appendSelectedFilters(ActiveQuery $query, array $filters): void
    {
        $i = 0;

        foreach ($filters as $propertyId => $valueIds)
        {
            if (empty($valueIds))
            {
                continue;
            }

            $alias = 'pf' . $i++;

            $query->innerJoin(
                ProductFilters::tableName() . ' ' . $alias,
                $alias . '.product_id = product.id'
            );

            $query->andWhere([
                $alias . '.property_id' => $propertyId
            ]);

            $query->andWhere([
                'IN',
                $alias . '.value_id',
                $valueIds
            ]);
        }

        $query->groupBy('product.id');
    }

    public static function applySpecificationFilter (ActiveQuery $query, array $specifications, array $args): ActiveQuery
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