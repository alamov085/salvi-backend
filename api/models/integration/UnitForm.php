<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 05.02.2020
 * Time: 15:37
 */

namespace api\models\integration;

use common\models\Unit;
use yii\base\Model;
use yii\db\ActiveRecord;

class UnitForm extends Model
{
    public $uuid;
    public $name;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['uuid', 'name'], 'required'],
            [['uuid', 'name'], 'string']
        ];
    }

    /**
     * @return array|bool|Unit|ActiveRecord|null
     */
    public function save()
    {
        $model = Unit::find()->where(['uuid' => $this->uuid])->one();
        if (!$model) {
            $model = new Unit(['uuid' => $this->uuid]);
        }
        $model->name = $this->name;
        $result = $model->save();
        if ($result) {
            return $model;
        }
        return $result;
    }
}
