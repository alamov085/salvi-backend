<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 15.08.2019
 * Time: 11:34
 */

namespace common\models\query;

use common\models\Contract;
use yii\db\ActiveQuery;

/**
 * Class ContractAQ
 * @package common\models\query
 */
class ContractAQ extends ActiveQuery
{
    public function active()
    {
        $time = time();
        return $this
            ->andWhere(['status' => Contract::STATUS_ACTIVE])
            ->andWhere(['<=', 'date_start', $time])
            ->andWhere(['>=', 'date_end', $time - 86400]);
    }
}
