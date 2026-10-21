<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 05.02.2020
 * Time: 15:39
 */

namespace api\models\integration;

use common\models\Brand;
use yii\base\Model;
use yii\db\ActiveRecord;
use yii\helpers\Inflector;

/**
 * Class BrandForm
 * @package api\models\integration
 */
class BrandForm extends Model
{
    public $uuid;
    public $name;
    public $vendor_uuid;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['uuid', 'name', 'vendor_uuid,','code'], 'required'],
            [['uuid', 'name', 'vendor_uuid','code'], 'string'],
        ];
    }

    /**
     * @return array|bool|Brand|ActiveRecord|null
     */
    public function save()
    {
        $name = trim($this->name);
        $code = Inflector::slug($name);

        $model = Brand::find()->where(['name' => $name, 'vendor_uuid' => $this->vendor_uuid])->one();

        if (!$model)
        {
            $model = Brand::find()->where(['name' => $name])->one();

            if (!$model)
            {
                $model = new Brand(['uuid' => $this->uuid]);
            }
        }

        $baseCode = $code;
        $suffix = 2;

        while (Brand::find()->where(['code' => $code])->andFilterWhere(['<>', 'id', $model->id])->exists())
        {
            $code = $baseCode . '-' . $suffix;
            $suffix++;
        }

        $model->name = $name;
        $model->vendor_uuid = $this->vendor_uuid;
        $model->code = $code;

        if(!isset($model->id))
        {
            $model->created_at = time();
        }

        $model->updated_at = time();

        $result = $model->save();

        if ($result) {
            return $model;
        }
        return $result;
    }
}
