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
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'viewPath' => '@api/mail',
            'useFileTransport' => false,
            'transport' => [
                'class' => 'Swift_SmtpTransport',
                'host' => 'mx1.mirohost.net',
                'username' => 'no-reply@salvigroup.com.ua',
                'password' => '24w0bhxHy1Yi',
                'port' => '587',
                'encryption' => 'tls',
            ],
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
        'ftp' => [
            'class' => '\gftp\FtpComponent',
            'driverOptions' => [
                'class' => \gftp\FtpProtocol::valueOf('sftp')->driver,
                'user' => 'fileuploader',
                'pass' => 'rU3F%7%69B*+eCcj',
                'host' => '94.130.177.104',
                'port' => 22321,
                'timeout' => 120
            ]
        ]
    ],
];