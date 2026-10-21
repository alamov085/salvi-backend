<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 05.02.2020
 * Time: 15:33
 */

namespace api\models\integration;

use common\models\Vendor;
use yii\base\Model;
use yii\db\ActiveRecord;

/**
 * Class VendorForm
 * @package api\models\integration
 */
class VendorForm extends Model
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
     * @return array|bool|Vendor|ActiveRecord|null
     */
    public function save()
    {
        $model = Vendor::find()->where(['uuid' => $this->uuid])->one();
        if (!$model) {
            $model = new Vendor(['uuid' => $this->uuid]);
        }
        $model->name = $this->name;
        $result = $model->save();
        if ($result) {
            return $model;
        }
        return $result;
    }
}
