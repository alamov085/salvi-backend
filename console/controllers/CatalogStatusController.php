<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 08.10.2019
 * Time: 16:21
 */

namespace console\controllers;

use api\helpers\SpecificationHelper;
use api\job\EndCronJob;
use common\components\EmptyLogger;
use common\helpers\CropImageHelper;
use common\helpers\xml\CatalogParse;
use common\helpers\xml\ProductParse;
use common\helpers\XmlParse;
use common\models\CatalogLimitation;
use common\models\Category;
use common\models\CategoryExclude;
use common\models\Company;
use common\models\Product;
use common\models\ProductPhoto;
use Yii;
use yii\console\Controller;

/**
 * Class SftpController
 * @package console\controllers 1с_b2b.xml
 */
class CatalogStatusController extends Controller
{
    public function actionIndex()
    {
        Yii::setLogger(new EmptyLogger());
        $mode = 'FTP_ASCII';
        $asynchronous = false;
        $file = Yii::$app->ftp->get('1C_b2b.xml', Yii::getAlias('@console') . '/xml/catalog_status.xml', $mode, $asynchronous);

        echo 'Start time: ' . date('d.m.Y H:i:s') . "\n";
        $executionStartTime = microtime(true);

        Product::updateAll(['delete'=>Product::STATUS_HIDDEN]);
        $a = new XmlParse($file, 'product', CatalogParse::class);
        $a->read();

        $executionEndTime = microtime(true);
        $seconds = $executionEndTime - $executionStartTime;
        echo 'End time: ' . date('d.m.Y H:i:s') . "\n";
        echo "This script took $seconds to execute.";
    }
}
