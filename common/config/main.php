<?php
return [
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm' => '@vendor/npm-asset',
    ],
    'bootstrap' => [
        'queue',
        'queueFile',
        'common\events\AppEvent',
    ],
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
            'cachePath' => dirname(dirname(__DIR__)) . '/common/runtime/cache',
        ],
        'authManager' => [
            'class' => 'common\components\RbacManager',
            //'cache' => 'cache'
        ],
        'queue' => [
            'class' => \yii\queue\redis\Queue::class,
            'redis' => 'redis',
            'channel' => 'queue',
        ],
        'queueFile' => [
            'class' => \yii\queue\file\Queue::class,
            'path' => '@runtime/queue',
        ],
        'redis' => [
            'class' => 'yii\redis\Connection',
            'hostname' => 'localhost',
            'port' => 6379,
            'database' => 0,
        ],
    ],
];
