<?php

namespace common\events;

use api\job\DeleteProductJob;
use api\job\LocationAddedJob;
use common\helpers\Socket;
use common\models\Config;
use common\models\Locations;
use common\models\Lot;
use common\models\LotBet;
use common\models\Message;
use common\models\MessageTheme;
use common\models\Notification;
use common\models\Product;
use common\models\UserBalanceHistory;
use Yii;
use yii\base\Application;
use yii\base\BootstrapInterface;
use yii\base\Event;
use yii\db\ActiveRecord;
use yii\db\AfterSaveEvent;

/**
 * Class SocketEvent
 * @package common\events
 */
class AppEvent implements BootstrapInterface
{
    /**
     * Bootstrap method to be called during application bootstrap stage.
     * @param Application $app the application currently running
     */
    public function bootstrap($app)
    {
        $this->location();
    }

    private function location()
    {
        Event::on(Locations::class, ActiveRecord::EVENT_AFTER_INSERT, function ($event) {
            Yii::$app->queue->push(new LocationAddedJob([
                'location' => $event->sender
            ]));
        });

        Event::on(Product::class, ActiveRecord::EVENT_AFTER_UPDATE, function (AfterSaveEvent $event) {
            if (isset($event->changedAttributes['delete'])) {
                /** @var Product $product */
                $product = $event->sender;
                Yii::$app->queue->push(new DeleteProductJob([
                    'id'        => $product->id,
                    'uuid'      => $product->uuid,
                    'is_delete' => $product->deleted
                ]));
            }
        });
    }
}
