<?php
namespace console\controllers;


use Yii;
use yii\console\Controller;


/**
 * Class ProductsController
 * @package console\controllers
 */
class ClearLogsController extends Controller
{
    public function actionIndex()
    {
        echo date('d.m.Y H:i:s') . PHP_EOL;


        Yii::$app->db->createCommand()
            ->delete('notification', [
                '<',
                'created_at',
                date('Y-m-d H:i:s', strtotime('-30 days')),
            ])
            ->execute();


        $file = '/var/www/salvi/www_api/api/runtime/logs/integration.log';

        $filesize = filesize($file);

        if($filesize > 400000000)
        {
            unlink($file);

            echo "Deleted: {$file}\n";
        }


        $dir = '/var/www/salvi/www_api/api/runtime/logs/products';

        $limitTimestamp = strtotime('-3 months');

        foreach (glob($dir . '/upload_*.log') as $file)
        {

            if (preg_match('/upload_([0-9\-]+)\.log$/', basename($file), $matches))
            {
                $timestamp = strtotime(
                    str_replace('_', ' ', $matches[1])
                );

                if ($timestamp < $limitTimestamp)
                {
                    unlink($file);

                    echo "Deleted: {$file}\n";
                }
            }
        }

        $dir = '/var/www/salvi/www_api/api/runtime/logs/specification';

        $limitTimestamp = strtotime('-3 months');

        foreach (glob($dir . '/upload_*.log') as $file)
        {
            if (preg_match('/upload_([0-9\_\-\:]+)\.log$/', basename($file), $matches))
            {
                $timestamp = strtotime(
                    str_replace('_', ' ', $matches[1])
                );

                if ($timestamp < $limitTimestamp)
                {
                    unlink($file);

                    echo "Deleted: {$file}\n";
                }
            }
        }

        $dir = '/var/www/salvi/www_api/console/xml/orders';

        $limitTimestamp = strtotime('-3 months');

        foreach (glob($dir . '/orders_*.json') as $file)
        {
            if (preg_match('/orders_(\d+)\.json$/', basename($file), $matches))
            {
                $timestamp = (int)$matches[1];

                if ($timestamp < $limitTimestamp)
                {
                    unlink($file);

                    echo "Deleted: {$file}\n";
                }
            }
        }

        foreach (glob($dir . '/orders_*.xml') as $file)
        {
            if (preg_match('/orders_(\d+)\.xml$/', basename($file), $matches))
            {
                $timestamp = (int)$matches[1];

                if ($timestamp < $limitTimestamp)
                {
                    unlink($file);

                    echo "Deleted: {$file}\n";
                }
            }
        }

        $dir = '/var/www/salvi/www_api/api/runtime/logs/products_photo';

        $limitTimestamp = strtotime('-3 weeks');

        foreach (glob($dir . '/*.log') as $file)
        {
            if (filemtime($file) < $limitTimestamp)
            {
                unlink($file);

                echo "Deleted: {$file}\n";
            }
        }


        echo "Done\n";
    }


}
