<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 26.12.2019
 * Time: 10:46
 */

namespace api\schema\queries;

use api\schema\Types;
use Yii;

/**
 * Class OneSCheckQuery
 * @package api\schema\queries
 */
class OneSCheckQuery extends BasicQuery
{

    public function type()
    {
        return Types::oneSCheck();
    }

    public function args()
    {
        return [];
    }

    public function resolve()
    {
        return function ($root, $args) {
            $file = Yii::getAlias('@console') . '/xml/cron.lock';
            $exist = file_exists($file);
            return ['in_progress' => !$exist];
        };
    }
}
