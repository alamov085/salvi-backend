<?php


namespace api\controllers\v2;


use api\controllers\RestController;
use api\helpers\CategoryTree;
use common\models\Category;
use yii\base\Module;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

class CategoriesController extends RestController {

    public function __construct(string $id, Module $module, array $config = [])
    {
        parent::__construct($id, $module, $config);

        $this->setBreadcrumbs('Каталог', '/catalog');
    }


    /**
     * Получаем дерево категорий
     * @param $category
     * @param array $tree
     * @return array
     */
    private function getTree($category, &$tree = []) {
        if(!empty($category->parent_id)) {
            $item = Category::find()->where(['id' => $category->parent_id, 'delete' => 0])->one();

            if(!empty($item)) {
                $tree[] = $item;

                if(!empty($item->parent_id)) {
                    $tree[] = $this->getParentItem($item);
                }
            }
        }

        return $tree;
    }

    /**
     * Получаем родительский элемент
     * @param $category
     * @return array|ActiveRecord|null
     */
    private function getParentItem($category) {
        if(!empty($category->parent_id)) {
            $parent = Category::find()->with()->where(['id' => $category->parent_id, 'delete' => 0])->one();

            if(!empty($parent)) {
                return $parent;
            }
        }

        return null;
    }

    public function actionCategoryData() {
        $data = [];

        try {
//            $uuid = ArrayHelper::getValue($this->post, 'uuid', null);
            $route = ArrayHelper::getValue($this->post, 'uuid', null);

            // удаляем /сatalog из урла
            $route = preg_replace("#\/catalog\/#", "", $route);
            // разбиваем урл на сегменты  перевовариваем его
            $route = array_reverse(explode('/', $route));

            if(empty($route)) {
                throw new \Exception("Undefined identifier!");
            }

            $code = array_shift($route);

            if(!$code) {
               throw new \Exception("Undefined identifier!");
            }

            $data['category'] = Category::find()->where(['code' => $code])->one();

            if(!$data['category']) {
                throw new \Exception("Category not found!");
            }

            if(!$this->checkCategoryOnExists($data['category'], $route)) {
                throw new \Exception("Category not found! 2");
            }


            // добавляем родительские категории в ХК
            $tree = $this->getTree($data['category']);
            $fullCategories = "/catalog";

            if(!empty($tree)) {
                $tree = array_reverse($tree);
                foreach ($tree as $item) {
                    $this->setBreadcrumbs($item->name, $fullCategories."/".$item->code);
                    $fullCategories .= "/{$item->code}";
                }
            }

            $this->setBreadcrumbs($data['category']->name, $fullCategories."/{$data['category']->code}");

            $data['breadcrumbs'] = $this->getBreadcrumbs();

            return $this->successResponse($data);
        } catch (\Exception $exception) {
            return $this->failResponse([
                'message' => $exception->getMessage(),
            ]);
        }

    }

    private function checkCategoryOnExists($category, array $categories) {
        if(!empty($categories)) {
            foreach ($categories as $routeCategory) {
                $item = Category::find()->where(['code' => $routeCategory, 'delete' => 0])->one();

                if(!$item) {
                    return false;
                }

                if($item->uuid != $category->parent_uuid) {
                    return false;
                }

                if(!empty($item->parent_uuid)) {
                    array_shift($categories);
                    return $this->checkCategoryOnExists($item, $categories);
                }
            }
        }


        return true;

//        if(!empty($category->parent_id)) {
//            $parent =
//
//            if(!$parent) {
//                return false;
//            }
//        }
//
//        return true;
    }

}
