<?php

namespace api\helpers\UniSender;

use Unisender\ApiWrapper\UnisenderApi;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

/**
 * Class UniSenderFactory
 * @package common\helpers\UniSender
 */
class UniSenderFactory
{
    /** @var UnisenderApi|null */
    protected static $apiObject;

    /**
     * Такой себе синглтон.
     *
     * @return UnisenderApi
     */
    public static function factory()
    {
        if (self::$apiObject == null) {
            self::$apiObject = new UnisenderApi(self::getApiKey());
        }
        return self::$apiObject;
    }

    /**
     * @return string
     */
    public static function getEmailListId()
    {
        $unisender = self::factory();
        $lists = Json::decode($unisender->getLists());
        $lists = $lists['result'];
        $lists = ArrayHelper::map($lists, 'id', 'title');
        $listName = \Yii::$app->params['uniSenderListName'];
        $finded = array_search($listName, $lists);
        if ($finded == false) {
            //Нужный список не найден
            $createResult = Json::decode($unisender->createList([
                'title' => $listName,
            ]));
            $finded = $createResult['result']['id'];
        }
        return $finded;
    }

    /**
     * От чьего емейла происходит отправка письма. Этот емейл должен быть подтвержден
     * в личном кабинете
     * https://cp.unisender.com/ru/v5/user/info/confirmed/list
     *
     * Если емейл не подтвержден, то будем получать ошибку отправки.
     *
     * @return string
     */
    public static function getSenderEmail()
    {
        return \Yii::$app->params['uniSenderSenderEmail'];
    }

    /**
     * Ключ доступа к Unisender API. Расположен на странице
     * https://cp.unisender.com/ru/v5/user/info/api
     *
     * @return string
     */
    public static function getApiKey()
    {
        return \Yii::$app->params['uniSenderApiKey'];
    }

    /**
     * Имя отправителя. Произвольная строка с именем от кого отправляется.
     * Сайт/Система, etc.
     *
     * @return string
     */
    public static function getSenderName()
    {
        return \Yii::$app->params['uniSenderSenderName'];
    }

    public static function getSenderLogin()
    {
        return \Yii::$app->params['uniSenderLogin'];
    }
}
