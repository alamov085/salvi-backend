<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 12.08.2019
 * Time: 10:46
 */

namespace api\helpers;

use common\models\CatalogLimitation;
use common\models\Category;
use common\models\Company;
use common\models\ProductCategory;
use common\models\Specification;
use common\models\SpecificationProduct;
use common\models\User;
use Exception;
use Yii;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;

/**
 * Class SpecificationHelper
 * @package api\helpers
 */
class SpecificationHelper
{
    private $companyId;
    private $specifications = false;
    private $toDb = [];
    private $records = [];

    public function __construct($companyId = 0)
    {
        if ($companyId) {
            $this->companyId = $companyId;
        } else {
            /** @var  $user User */
            $user = Yii::$app->user->getIdentity();
            $this->companyId = $user->company_id;
        }
        if ($this->companyId) {
            $this->specifications = Specification::find()
                ->where(['company_id' => $this->companyId])
                ->active()
                ->all();
        }
    }

    public function init()
    {
        $cache = Yii::$app->cache;
        $key = 'categoryTree_catalog_' . $this->companyId;
        $key2 = 'allCategoryTree_catalog_' . $this->companyId;
        $this->checkCategories();
        //$this->calculateProduct();
        $cache->delete($key);
        $cache->delete($key2);
    }

    /**
     * Когда добавляем товар в спецификацию, категория товара должна отобразится в списке доступных категорий.
     * Здесь делаем это действие
     */
    public function checkCategories()
    {
        CatalogLimitation::deleteAll(['company_id' => $this->companyId, 'specification' => 1]);
        if ($this->specifications) {
            $productSpecification = SpecificationProduct::find()
                ->where(['in', 'specification_id', ArrayHelper::getColumn($this->specifications, 'id')])
                ->groupBy('product_uuid')
                ->all();
            if ($productSpecification) {
                $productCategories = ProductCategory::find()
                    ->where(['in', 'product_uuid', ArrayHelper::getColumn($productSpecification, 'product_uuid')])
                    ->groupBy('category_uuid')
                    ->all();
                if ($productCategories) {
                    $data = [];
                    foreach ($productCategories as $category) {
                        $data[] = [
                            'company_id' => $this->companyId,
                            'category_id' => $category->category_id,
                            'category_uuid' => $category->category_uuid,
                            'markup' => 0,
                            'for_show' => 0,
                            'total_product' => 0,
                            'specification' => 1
                        ];
                    }
                    if (!empty($data)) {
                        try {
                            Yii::$app->db->createCommand()->batchInsert(
                                CatalogLimitation::tableName(),
                                [
                                    'company_id',
                                    'category_id',
                                    'category_uuid',
                                    'markup',
                                    'for_show',
                                    'total_product',
                                    'specification'
                                ],
                                $data
                            )->execute();
                        } catch (Exception $e) {
                            Yii::error($e);
                        }
                    }
                }
            }
        }
    }

    private function calculateProduct()
    {
        $company = Company::find()->where(['id' => $this->companyId])->one();
        if ($company) {
            $this->calculateProductSpecificationOnly();
            $this->calculateProductAllCatalog();
        }
        $this->totalCalculate($company);
    }

    /** Просчет количества товаров для ограничения по спецификации */
    private function calculateProductSpecificationOnly()
    {
        CatalogLimitation::updateAll(['total_product' => 0], ['company_id' => $this->companyId, 'specification' => 1]);
        $categories = CatalogLimitation::find()
            ->where(['company_id' => $this->companyId])
            ->andWhere(['specification' => 1])
            ->all();
        if ($categories) {
            $categories = ArrayHelper::index($categories, 'category_uuid');
            $products = SpecificationProduct::find()
                ->where(['in', 'specification_id', ArrayHelper::getColumn($this->specifications, 'id')])
                ->groupBy('product_uuid')
                ->all();
            $productCategories = ProductCategory::find()
                ->where(['in', 'product_uuid', ArrayHelper::getColumn($products, 'product_uuid')])
                ->all();
            if ($productCategories) {
                foreach ($productCategories as $product) {
                    $categories[$product->category_uuid]['total_product'] += 1;
                }
            }
            $data = [];
            foreach ($categories as $category) {
                $data[] = [
                    'id' => $category['id'],
                    'total_product' => $category['total_product'],
                ];
            }
            if (!empty($data)) {
                try {
                    $db = Yii::$app->db;
                    $sql = $db->queryBuilder->batchInsert(
                        CatalogLimitation::tableName(),
                        ['id', 'total_product'],
                        $data
                    );
                    $db->createCommand(
                        $sql . ' ON DUPLICATE KEY UPDATE `total_product`=VALUES(`total_product`)'
                    )->execute();
                } catch (Exception $e) {
                    Yii::error($e);
                }
            }
        }
    }

    /** Просчет количества товаров для ограничения по категории */
    private function calculateProductAllCatalog()
    {
        CatalogLimitation::updateAll(['total_product' => 0], ['company_id' => $this->companyId, 'specification' => 0]);
        $categories = CatalogLimitation::find()
            ->where(['company_id' => $this->companyId])
            ->andWhere(['specification' => 0])
            ->asArray()
            ->all();
        if ($categories) {
            $categories = ArrayHelper::index($categories, 'category_uuid');
            $productCategories = ProductCategory::find()
                ->where(['in', 'category_uuid', ArrayHelper::getColumn($categories, 'category_uuid')])
                ->asArray()
                ->all();
            if ($productCategories) {
                foreach ($productCategories as $product) {
                    $categories[$product['category_uuid']]['total_product'] += 1;
                }
            }
            $data = [];
            foreach ($categories as $category) {
                $data[] = [
                    'id' => $category['id'],
                    'total_product' => $category['total_product'],
                ];
            }
            if (!empty($data)) {
                try {
                    $db = Yii::$app->db;
                    $sql = $db->queryBuilder->batchInsert(
                        CatalogLimitation::tableName(),
                        ['id', 'total_product'],
                        $data
                    );
                    $db->createCommand(
                        $sql . ' ON DUPLICATE KEY UPDATE `total_product`=VALUES(`total_product`)'
                    )->execute();
                } catch (Exception $e) {
                    Yii::error($e);
                }
            }
        }
    }

    public function totalCalculate(Company $company)
    {
        $limitationQuery = CatalogLimitation::find()->where(['company_id' => $this->companyId]);
        if ($company->specification) {
            $limitationQuery->andWhere(['specification' => 1]);
        } else {
            $limitationQuery->andWhere(['specification' => 0]);
        }
        $limitationQuery->orderBy('specification ASC');
        $limitation = $limitationQuery->asArray()->all();
        $query = Category::find()
            ->select([
                'category.uuid',
                'category.parent_uuid',
                'category.name',
                'category.parent_id',
                'category.id',
                'catalog_limitation.total_product',
                'category.has_child',
                'catalog_limitation.id as cid'
            ]);
        if ($company->specification) {
            $query->joinWith(['limitation' => function (ActiveQuery $query) {
                return $query
                    ->andWhere(['=', 'catalog_limitation.specification', 1]);
            }]);
        } else {
            $query->joinWith(['limitation' => function (ActiveQuery $query) {
                return $query
                    ->andWhere(['=', 'catalog_limitation.specification', 0]);
            }]);
        }
        $query->andWhere(['in', 'uuid', ArrayHelper::getColumn($limitation, 'category_uuid')]);
        $query->orderBy('category.id ASC');

        $this->records = $query->asArray()->all();
        $this->records = ArrayHelper::index($this->records, 'uuid');
        if (!$company->specification) {
            $limitation = CatalogLimitation::find()->where(['company_id' => $this->companyId])
                ->andWhere(['specification' => 1])->all();
            $query = Category::find()
                ->joinWith(['limitation' => function (ActiveQuery $query) {
                    return $query
                        ->andWhere(['=', 'catalog_limitation.specification', 1]);
                }])
                ->select([
                    'category.uuid',
                    'category.parent_uuid',
                    'category.name',
                    'category.parent_id',
                    'category.id',
                    'catalog_limitation.total_product',
                    'category.has_child',
                    'catalog_limitation.id as cid'
                ]);
            $query->andWhere(['in', 'category.uuid', ArrayHelper::getColumn($limitation, 'category_uuid')]);
            $query->orderBy('category.id ASC');
            $secondResult = $query->asArray()->all();
            if ($secondResult) {
                $secondResult = ArrayHelper::index($secondResult, 'uuid');
                $firstResult = ArrayHelper::index($this->records, 'uuid');
                $this->records = array_merge($secondResult, $firstResult);
            }
        }
        $countArray = [];
        if (!empty($this->records)) {
            foreach ($this->records as $k => $record) {
                if (!$record['has_child']) {
                    $this->toDb[$record['uuid']] = [
                        'id' => $this->records[$k]['cid'],
                        'total_product' => $record['total_product']
                    ];
                    if (isset($countArray[$record['parent_uuid']])) {
                        $countArray[$record['parent_uuid']]['total'] += $record['total_product'];
                    } else {
                        $countArray[$record['parent_uuid']]['total'] = $record['total_product'];
                    }
                    //unset($this->records[$k]);
                }
            }
            $this->recursiveCategory($countArray);
        }
        if (!empty($this->toDb)) {
            try {
                $db = Yii::$app->db;
                $sql = $db->queryBuilder->batchInsert(
                    CatalogLimitation::tableName(),
                    ['id', 'total_product'],
                    $this->toDb
                );
                $db->createCommand(
                    $sql . ' ON DUPLICATE KEY UPDATE `total_product`=VALUES(`total_product`)'
                )->execute();
            } catch (Exception $e) {
                Yii::error($e);
            }
        }
    }

    private function recursiveCategory($countArray)
    {
        if (!empty($countArray)) {
            $countNew = [];
            foreach ($countArray as $k => $item) {
                if (!isset($this->toDb[$k])) {
                    $this->toDb[$k] = [
                        'id' => $this->records[$k]['cid'],
                        'total_product' => $item['total']
                    ];
                } else {
                    $this->toDb[$k]['total_product'] += $item['total'];
                }
                if (!empty($this->records[$k]['parent_uuid'])) {
                    if (isset($countNew[$this->records[$k]['parent_uuid']])) {
                        $countNew[$this->records[$k]['parent_uuid']]['total'] += $item['total'];
                    } else {
                        $countNew[$this->records[$k]['parent_uuid']]['total'] = $item['total'];
                    }
                }
               // unset($this->records[$k]);
            }
            if (!empty($countNew)) {
                $this->recursiveCategory($countNew);
            }
        } else {
            foreach ($countArray as $k => $item) {
                if (isset($this->toDb[$k])) {
                    $this->toDb[$k]['total_product'] += $item['total'];
                }
            }
        }
    }
}
