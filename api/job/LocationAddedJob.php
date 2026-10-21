<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 24.03.2020
 * Time: 10:50
 */

namespace api\job;

use common\models\Contract;
use common\models\Locations;
use common\models\Specification;
use common\models\SpecificationLocation;
use yii\base\BaseObject;
use yii\helpers\ArrayHelper;
use yii\queue\JobInterface;

class LocationAddedJob extends BaseObject implements JobInterface
{
    /** @var Locations */
    public $location;

    /**
     * @inheritDoc
     */
    public function execute($queue)
    {
        $contract = Contract::find()
            ->select(['id'])
            ->where(['company_id' => $this->location->company_id])
            ->andWhere(['all_location' => 1])
            ->asArray()
            ->all();
        if ($contract) {
            $specifications = Specification::find()
                ->where(['in', 'contract_id', ArrayHelper::getColumn($contract, 'id')])
                ->andWhere(['all_location' => 1])
                ->all();
            if ($specifications) {
                foreach ($specifications as $specification) {
                    $locations = Locations::getListOfLocationsByContractBase($specification->contract_id, $specification->company_id);
                    if ($locations) {
                        SpecificationLocation::deleteAll(['specification_id' => $specification->id]);
                        SpecificationLocation::addLocations(
                            $specification->id,
                            ArrayHelper::getColumn($locations, 'id')
                        );
                    }
                }
            }
        }
    }
}
