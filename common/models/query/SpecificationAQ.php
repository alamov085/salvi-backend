<?php

namespace common\models\query;

use common\models\Specification;
use yii\db\ActiveQuery;

/**
 * Created by PhpStorm.
 * User: tik
 * Date: 15.08.2019
 * Time: 10:43
 */

/**
 * Class SpecificationAQ
 * @package common\models\query
 */
class SpecificationAQ extends ActiveQuery
{

    public function active()
    {
        $date = date('d.m.Y');
        $time = strtotime($date);
        return $this
            ->andWhere(['specification.status' => Specification::STATUS_ACTIVE])
            ->andWhere(['<=', 'specification.date_start', $time])
            ->andWhere(['>=', 'specification.date_end', $time]);
    }
}
