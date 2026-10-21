<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 15.08.2019
 * Time: 11:41
 */

namespace common\models\query;

use common\models\Budget;
use common\models\Contract;
use yii\db\ActiveQuery;

class BudgetAQ extends ActiveQuery
{
    public function active()
    {
        $time = time();
        return $this
            ->andWhere(['status' => Budget::STATUS_ACTIVE])
            ->andWhere(['<=', 'date_start', $time])
            ->andWhere(['>=', 'date_end', $time - 86400]);
    }
}
