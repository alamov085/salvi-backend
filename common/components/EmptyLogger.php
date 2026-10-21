<?php

namespace common\components;

use yii\log\Logger;

class EmptyLogger extends Logger
{
    public function log($message, $level, $category = 'application')
    {
        return false;
    }
}