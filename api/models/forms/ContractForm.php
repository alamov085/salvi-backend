<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 12.07.2019
 * Time: 14:43
 */

namespace api\models\forms;

use api\helpers\NotificationHelper;
use common\models\Contract;
use common\models\ContractCostCenter;
use common\models\ContractLocation;
use common\models\Locations;
use common\models\Specification;
use common\models\SpecificationLocation;
use common\models\User;
use yii\base\Model;
use yii\helpers\ArrayHelper;

/**
 * Class ContractForm
 * @package api\models\forms
 */
class ContractForm extends Model
{
    public $id;
    public $name;
    public $number;
    public $supplier_id;
    public $company_entity_id;
    public $date_start;
    public $date_end;
    public $locations;
    public $location_id;
    public $comment;
    public $costCenters;
    public $all_location;
    public $date_send_noty_end;

    public function rules()
    {
        return [
            [['name', 'number', 'supplier_id', 'company_entity_id',
                'date_start', 'date_end'], 'required'],
            [['supplier_id', 'company_entity_id', 'location_id'], 'integer'],
            [['comment'], 'string'],
            [['name', 'number'], 'string', 'max' => 255],
            [['date_start', 'date_end', 'id'], 'safe'],
            [['date_end', 'date_start'], 'filter', 'filter' => function ($value) {
                if (is_numeric($value)) {
                    return $value;
                }
                return strtotime($value);
            }],
            ['date_end', 'validateDateEnd'],
            [['all_location', 'costCenters', 'locations'], 'safe']
        ];
    }

    public function validateDateEnd($attribute, $params, $validator)
    {
        if ($this->date_end <= $this->date_start) {
            $this->addError('date_end', 'Дата завершення повинна бути більша дати старту');
        }
        if (!$this->all_location && empty($this->locations)) {
            $this->addError('locations', 'Необхідно вибрати хоча б 1 локацію');
        }
        if (!$this->all_location && empty($this->costCenters)) {
            $this->addError('costCenters', 'Необхідно вибрати хоча б 1 кост-центр');
        }
    }

    public function create()
    {
        /** @var  $user User */
        $user = \Yii::$app->user->getIdentity();
        $model = new Contract();
        $model->setAttributes([
            'name' => $this->name,
            'number' => $this->number,
            'supplier_id' => $this->supplier_id,
            'company_entity_id' => $this->company_entity_id,
            'date_start' => $this->date_start,
            'date_end' => $this->date_end,
            'comment' => $this->comment,
            'company_id' => $user->company_id,
            'status' => Contract::STATUS_ACTIVE,
            'all_location' => $this->all_location
        ]);
        $result = $model->save();
        if ($result) {
            ContractLocation::addLocation($model->id, $this->locations);
            ContractCostCenter::addCost($model->id, $this->costCenters);
            NotificationHelper::createdContract($model);
        }
        return $result;
    }

    public function attributeLabels()
    {
        return [
            'name' => 'Назва договору',
            'number' => 'Номер договору',
            'supplier_id' => 'Постачальник',
            'company_entity_id' => 'Юридична особа',
            'date_start' => 'Дата початку',
            'date_end' => 'Дата закінчення',
            'location_id' => 'Локація',
            'locations' => 'Локація',
            'costCenters' => 'Кост-центри',
            'comment' => 'Примітка',
        ];
    }

    public function update()
    {
        $model = Contract::find()->where(['id' => $this->id])->one();
        if ($model != false) {
            $notify = false;
            if ($model->date_end != $this->date_end) {
                $notify = true;
            }
            $model->name = $this->name;
            $model->number = $this->number;
            $model->supplier_id = $this->supplier_id;
            $model->company_entity_id = $this->company_entity_id;
            $model->date_start = $this->date_start;
            $model->date_end = $this->date_end;
            $model->comment = $this->comment;
            $model->all_location = $this->all_location;
            $result = $model->save();
            if ($result) {
                if ($notify) {
                    NotificationHelper::changeDateContract($model);
                }
                $oldLocations = ContractLocation::find()
                    ->where(['contract_id' => $model->id])
                    ->all();
                ContractLocation::deleteAll(['contract_id' => $model->id]);
                ContractLocation::addLocation($model->id, $this->locations);
                if ($oldLocations) {
                    if (!$this->locations) {
                        $this->locations = [];
                    }
                    $oldLocations = ArrayHelper::getColumn($oldLocations, 'location_id');
                    $locations = array_diff($oldLocations, $this->locations);
                    if (!empty($locations)) {
                        NotificationHelper::changeContractLocation($model, $locations);
                    }
                }
                $specification = Specification::find()
                    ->where(['contract_id' => $model->id])
                    ->andWhere(['all_location' => 1])
                    ->all();
                if ($specification) {
                    $locationsList = Locations::getListOfLocationsByContract($model->id);
                    foreach ($specification as $specification) {
                        SpecificationLocation::deleteAll(['specification_id' => $specification->id]);
                        SpecificationLocation::addLocations(
                            $specification->id,
                            ArrayHelper::getColumn($locationsList, 'id')
                        );
                    }
                }
                $oldCostCenters = ContractCostCenter::find()
                    ->where(['contract_id' => $model->id])
                    ->all();
                ContractCostCenter::deleteAll(['contract_id' => $model->id]);
                ContractCostCenter::addCost($model->id, $this->costCenters);
                if ($oldCostCenters) {
                    $oldCostCenters = ArrayHelper::getColumn($oldCostCenters, 'cost_center_id');
                    $costCenters = array_diff($oldCostCenters, $this->costCenters);
                    if (!empty($costCenters)) {
                        NotificationHelper::changeContractCostCenter($model, $costCenters);
                    }
                }
                NotificationHelper::updatedContract($model);
            }
            return $result;
        }
        return false;
    }
}
