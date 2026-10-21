<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 25.07.2019
 * Time: 16:23
 */

namespace api\models\forms;

use api\helpers\NotificationHelper;
use common\models\CatalogLimitation;
use common\models\Category;
use common\models\CategoryExclude;
use common\models\Company;
use common\models\Config;
use Exception;
use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;

/**
 * Class CatalogLimitationForm
 * @package api\models\forms
 */
class CatalogLimitationForm extends Model
{
    public $companies;
    public $categories;
    public $new_category;
    public $markup;
    public $company;
    public $default_markup;

    private $categoryToDb;
    private $categoryParentIds;
    private $parentsOnlyForShow = [];
    private $allCategories;
    private $parentCategory = [];
    private $default = [];
    private $categoryParentIdsDefault;

    public function rules()
    {
        return [
            [['companies', 'company'], 'required'],
            [['categories', 'new_category', 'markup'], 'safe'],
            ['default_markup', 'safe']
        ];
    }

    public function create()
    {
        $this->makeCategoryArray();
        try {
            foreach ($this->companies as $company) {
                if (!empty($this->categoryToDb)) {
                    Yii::$app->db->createCommand()->batchInsert(
                        CatalogLimitation::tableName(),
                        [
                            'company_id',
                            'category_id',
                            'category_uuid',
                            'markup',
                            'for_show'
                        ],
                        $this->categoryToDb[$company]
                    )->execute();
                }
                NotificationHelper::changeCatalog($company);
            }
            $result = true;
            $categoryId = Category::find()->orderBy('id DESC')->one();
            if ($categoryId && !$this->new_category) {
                $maxId = $categoryId->id;
            } else {
                $maxId = 0;
            }
            Company::updateAll(
                [
                    'catalog_limitation' => 1,
                    'new_category' => $this->new_category,
                    'max_category_id' => $maxId
                ],
                ['in', 'id', $this->companies]
            );
        } catch (Exception $e) {
            Yii::error($e);
            $result = false;
        }
        return $result;
    }

    public function update()
    {
        if ($this->company) {
            $this->makeCategoryArray();
            CatalogLimitation::deleteAll([
                'and',
                ['specification' => 0],
                ['in', 'company_id', $this->companies]
            ]);
            try {
                foreach ($this->companies as $company) {
                    if (!empty($this->categoryToDb)) {
                        Yii::$app->db->createCommand()->batchInsert(
                            CatalogLimitation::tableName(),
                            [
                                'company_id',
                                'category_id',
                                'category_uuid',
                                'markup',
                                'for_show'
                            ],
                            $this->categoryToDb[$company]
                        )->execute();
                    }
                    NotificationHelper::changeCatalog($company);
                    CategoryExclude::deleteAll(['company_id' => $company]);
                    $this->excludeCategory($company);
                }
                $result = true;
                $categoryId = Category::find()->orderBy('id DESC')->one();
                if ($categoryId && !$this->new_category) {
                    $maxId = $categoryId->id;
                } else {
                    $maxId = 0;
                }
                Company::updateAll(
                    [
                        'catalog_limitation' => 1,
                        'new_category' => $this->new_category,
                        'max_category_id' => $maxId
                    ],
                    ['in', 'id', $this->companies]
                );
            } catch (Exception $e) {
                Yii::error($e);
                $result = false;
            }
            return $result;
        } else {
            Config::setValue(Config::MARKUP, $this->default_markup);
            return $this->defaultMarkup();
        }
    }

    private function makeCategoryArray()
    {
        if (!empty($this->markup)) {
            foreach ($this->markup as $category) {
                $model = Category::find()->where(['uuid' => $category['uuid']])->one();
                if ($model) {
                    foreach ($this->companies as $company) {
                        $this->categoryToArray(
                            $company,
                            $model,
                            ArrayHelper::getValue($category, 'markup', ''),
                            $category
                        );
                    }
                }
            }
        }
    }

    /**
     * @param $companyId
     * @return bool
     * @throws \yii\db\Exception
     */
    private function excludeCategory($companyId)
    {
        if ($this->categories) {
            $temp = [];
            foreach ($this->categories as $category) {
                $temp[] = [
                    'company_id' => $companyId,
                    'category_uuid' => (isset($category['category_uuid'])) ? $category['category_uuid'] : $category['uuid'],
                    'category_id' => (isset($category['category_id'])) ? $category['category_id'] : $category['id']
                ];
            }
            if (!empty($temp)) {
                Yii::$app->db->createCommand()->batchInsert(
                    CategoryExclude::tableName(),
                    [
                        'company_id',
                        'category_uuid',
                        'category_id'
                    ],
                    $temp
                )->execute();
            }
        }
        return true;
    }

    private function categoryToArray($companyId, Category $category, $markup, $cat, $forShow = 1)
    {
        if ($forShow || !isset($this->categoryToDb[$companyId][$category->uuid])) {
            if (!empty($markup)) {
                $this->categoryToDb[$companyId][$category->uuid] = [
                    'company_id' => $companyId,
                    'category_id' => $category->id,
                    'category_uuid' => $category->uuid,
                    'markup' => ($markup < 0) ? null : $markup,
                    'for_show' => $forShow
                ];
            }
        }
        if (isset($cat['children']) && count($cat['children'])) {
            foreach ($cat['children'] as $child) {
                $model = Category::find()->where(['uuid' => $child['uuid']])->one();
                $newMarkup = ArrayHelper::getValue($child, 'markup', '');
                if (!empty($newMarkup)) {
                    $markup1 = $newMarkup;
                } else {
                    $markup1 = $markup;
                }
                if ($model) {
                    $this->categoryToArray($companyId, $model, $markup1, $child);
                }
            }
        }
    }

//    private function categoryToArray($companyId, Category $category, $markup, $forShow = 1)
//    {
//        if ($forShow || !isset($this->categoryToDb[$companyId][$category->uuid])) {
//            if ($markup >= 0) {
//                $this->categoryToDb[$companyId][$category->uuid] = [
//                    'company_id' => $companyId,
//                    'category_id' => $category->id,
//                    'category_uuid' => $category->uuid,
//                    'markup' => ($markup < 0) ? null : $markup,
//                    'for_show' => $forShow
//                ];
//            }
//        }
//        if (!isset($this->categoryToDb[$companyId][$category->parent_uuid])) {
//            $categoryForShow = Category::find()->where(['uuid' => $category->parent_uuid])->one();
//            if ($categoryForShow) {
//                $this->categoryToArray($companyId, $categoryForShow, $markup, 0);
//            }
//        }
//        $this->categoryParentIds[$companyId][] = $category->parent_uuid;
//        if ($category->has_child && !in_array($category->uuid, $this->categoryParentIds[$companyId]) && $forShow) {
//            $categories = Category::find()->where(['parent_uuid' => $category->uuid])->all();
//            if ($categories) {
//                foreach ($categories as $category) {
//                    $this->categoryToArray($companyId, $category, $markup, $forShow);
//                }
//            }
//        }
//    }

    public function defaultMarkup()
    {
        if (!empty($this->markup)) {
            foreach ($this->markup as $category) {
                $model = Category::find()->where(['uuid' => $category['uuid']])->one();
                if ($model) {
                    $this->categoryToArrayDefault($model, ArrayHelper::getValue($category, 'markup', 0), $category);
                }
            }
        }
        if (!empty($this->default)) {
            try {
                $db = \Yii::$app->db;
                $sql = $db->queryBuilder->batchInsert(
                    Category::tableName(),
                    ['id', 'markup'],
                    $this->default
                );
                $db->createCommand(
                    $sql . ' ON DUPLICATE KEY UPDATE `markup`=VALUES(`markup`)'
                )->execute();
            } catch (Exception $e) {
                Yii::error($e);
            }
        }
        return true;
    }

    private function categoryToArrayDefault(Category $category, $markup, $cat)
    {
        if (!isset($this->default[$category->uuid])) {
            $this->default[$category->uuid] = [
                'id' => $category->id,
                'markup' => (empty($markup)) ? null : $markup,
            ];
        }
        if (isset($cat['children']) && count($cat['children'])) {
            foreach ($cat['children'] as $child) {
                $model = Category::find()->where(['uuid' => $child['uuid']])->one();
                $newMarkup = ArrayHelper::getValue($child, 'markup', '');
                if (!empty($newMarkup)) {
                    $markup1 = $newMarkup;
                } else {
                    $markup1 = $markup;
                }
                if ($model) {
                    $this->categoryToArrayDefault($model, $markup1, $child);
                }
            }
        }
    }
}
