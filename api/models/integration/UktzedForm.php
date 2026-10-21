<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 05.02.2020
 * Time: 15:29
 */

namespace api\models\integration;

use common\models\ProductUktzed;
use yii\base\Model;
use yii\db\ActiveRecord;

/**
 * Class UktzedForm
 * @package api\models\integration
 */
class UktzedForm extends Model
{
    public $uuid;
    public $short_name;
    public $full_name;
    public $type;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['uuid', 'short_name', 'full_name', 'type'], 'required'],
            [['uuid', 'short_name', 'full_name'], 'string'],
            ['type', 'integer']
        ];
    }

    /**
     * @return array|bool|ProductUktzed|ActiveRecord|null
     */
    public function save()
    {
        if (is_null($this->uuid)) {
            return false;
        }
        $model = ProductUktzed::find()
            ->where(['uuid' => $this->uuid])
            ->one();
        if (!$model) {
            $model = new ProductUktzed(['uuid' => $this->uuid]);
        }
        $model->short_name = $this->short_name;
        $model->full_name = $this->full_name;
        $model->type = $this->type;
        $result = $model->save();
        if ($result) {
            return $model;
        }
        return $result;
    }
}
