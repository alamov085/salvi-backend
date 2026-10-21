<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 29.04.2020
 * Time: 9:47
 */

namespace api\job;

use common\models\Category;
use common\models\CategoryExclude;
use common\models\Company;
use common\models\Product;
use Yii;
use yii\base\BaseObject;
use yii\queue\JobInterface;
use yii\queue\Queue;

/**
 * Class EndCronJob
 * @package api\job
 */
class EndCronJob extends BaseObject implements JobInterface
{
    public $startTime = 0;

    /**
     * @inheritDoc
     */
    public function execute($queue)
    {
        $this->deleteProducts();
        $this->actionReloadCategory();
    }

    public function actionReloadCategory()
    {
        $companies = Company::find()
            ->where(['new_category' => 0])
            ->all();
        if ($companies) {
            $lastCategory = Category::find()
                ->orderBy('id DESC')
                ->one();
            foreach ($companies as $company) {
                $categories = Category::find()
                    ->select(['id', 'uuid'])
                    ->where(['>', 'id', $company->max_category_id])
                    ->all();
                $temp = [];
                if ($categories) {
                    foreach ($categories as $category) {
                        $temp[] = [
                            'company_id'    => $company->id,
                            'category_uuid' => $category['uuid'],
                            'category_id'   => $category['id']
                        ];
                    }
                }
                if (!empty($temp)) {
                    try {
                        Yii::$app->db->createCommand()->batchInsert(
                            CategoryExclude::tableName(),
                            [
                                'company_id',
                                'category_uuid',
                                'category_id'
                            ],
                            $temp
                        )->execute();
                    } catch (\Exception $e) {
                        Yii::error($e);
                    }
                }
                $company->max_category_id = $lastCategory->id;
                $company->save();
            }
        }
    }

    private function deleteProducts()
    {
        Product::updateAll(
            ['delete' => 1],
            [
                'and',
                ['<=', 'updated_at', $this->startTime],
                ['is_buffer' => 0]
            ]
        );
    }

}
