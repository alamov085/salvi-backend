<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 19.06.2019
 * Time: 15:56
 */

namespace api\models\forms;

use common\models\CostCenter;
use function MongoDB\BSON\toJSON;
use Yii;
use yii\base\Model;

/**
 * Class CreateCostCenterForm
 * @package api\models\forms
 */
class CreateCostCenterForm extends Model
{
    public $name;
    public $location_id;
    public $id;
    public $uuid_client;
    public $uuid_contract;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['name', 'location_id'], 'required'],
            ['location_id', 'integer'],
            ['name', 'string'],
            ['id', 'safe'],
            [['uuid_contract', 'uuid_client'], 'string']
        ];
    }

    /**
     * @return bool
     */
    public function createCostCenter()
    {
        $model = new CostCenter();
        $model->name = $this->name;
        if (Yii::$app->user->can('uuidCostCenter')) {
            $model->uuid_client = $this->uuid_client;
            $model->uuid_contract = $this->uuid_contract;
        }
        $model->location_id = $this->location_id;
        return $model->save();
    }

    /**
     * @return bool
     */
    public function updateCostCenter()
    {
        $model = CostCenter::find()->where(['id' => $this->id])->one();
        if ($model) {
            $model->name = $this->name;
            $model->location_id = $this->location_id;
            if (Yii::$app->user->can('uuidCostCenter')) {
                $model->uuid_client = $this->uuid_client;
                $model->uuid_contract = $this->uuid_contract;
            }
            return $model->save();
        }
        return false;
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'name' => 'Назва',
            'location_id' => 'Локація'
        ];
    }
}
