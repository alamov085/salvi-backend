<?php
namespace api\helpers;

class TypeWebsite
{
    public static function getType()
    {
        return file_get_contents(realpath(dirname(__FILE__) . "/../..") . '/init_build/.env');
    }
}