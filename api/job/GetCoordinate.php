<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 15.11.2019
 * Time: 16:15
 */

namespace api\job;


use common\helpers\GoogleMapsHelper;
use common\models\Locations;
use yii\base\BaseObject;
use yii\helpers\ArrayHelper;
use yii\queue\JobInterface;
use yii\queue\Queue;

/**
 * Class GetCoordinate
 * @package api\job
 */
class GetCoordinate extends BaseObject implements JobInterface
{
    /**
     * @var $location Locations
     */
    public $location;

    /**
     * @param Queue $queue which pushed and is handling the job
     * @return void|mixed result of the job execution
     */
    public function execute($queue)
    {
        $coordinates = GoogleMapsHelper::getCoordinate($this->location->fullAddress);
        if (!empty($coordinates)) {
            $this->location->lat = ArrayHelper::getValue($coordinates, 'lat');
            $this->location->lng = ArrayHelper::getValue($coordinates, 'lng');
            $this->location->region = ArrayHelper::getValue($coordinates, 'region');
            $this->location->save();
        }
    }
}
