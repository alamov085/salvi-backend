<?php

namespace common\helpers;

/**
 * Class UserSecurity
 * @package common\helpers
 */
class UserSecurity
{
    const LEVEL_RED = 1;
    const LEVEL_YELLOW = 2;
    const LEVEL_GREEN = 3;


    /**
     * Калькулятор градации сложности пароля пользователя в целочисленном числе.
     * Сложность от 1 до 3.
     *
     * @param $pwd
     * @return integer
     */
    public static function getPassLevel($pwd)
    {
        $strenght = false;

        if (strlen($pwd) >= 8                       //Длинна больше 8ми символов
            && preg_match_all('/\\d/', $pwd) >= 1     //Есть цифры минимум 1 цифра
            && preg_match_all("#[A-Z]+#", $pwd) >= 1)  //Есть минимум 1 заглавная буква
        {
            $strenght = self::LEVEL_RED;
        }

        /*
         * Желтая зона
         */
        if (strlen($pwd) >= 12                       //Длинна больше 12ми символов
            && preg_match_all('/\\d/', $pwd) >= 2     //Есть цифры минимум 2 цифры
            && preg_match_all("#[A-Z]+#", $pwd) >= 1) //Есть минимум 1 заглавная буква
        {
            $strenght = self::LEVEL_YELLOW;
        }

        /*
         * Зеленая зона
         */
        if (strlen($pwd) >= 16                      //Длинна больше 16ми символов
            && preg_match_all('/\\d/', $pwd) >= 4     //Есть цифры минимум 4 цифры
            && preg_match_all("#[A-Z]+#", $pwd) >= 1) //Есть минимум 1 заглавная буква
        {
            $strenght = self::LEVEL_GREEN;
        }

        return $strenght;
    }

    /**
     * @param $level
     * @return string
     */
    public static function getPassLevelHtmlClass($level)
    {
        switch ((int)$level) {
            case 1:
                return '<div class="hint">Надёжность пароля: <span class=\'red\'>Слабый</span></div>';
            case 2:
                return '<div class="hint">Надёжность пароля: <span class=\'orange\'>Средний</span></div>';
            case 3:
                return '<div class="hint">Надёжность пароля: <span class=\'green\'>Высокий</span></div>';
        }
    }

    /**
     * @param $level
     * @return string
     */
    public static function getPassLevelTexting($level)
    {
        switch ((int)$level) {
            case 1:
                return 'Low';
            case 2:
                return 'Medium';
            case 3:
                return 'Hard';
        }
    }

}