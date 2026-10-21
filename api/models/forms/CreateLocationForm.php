<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 17.06.2019
 * Time: 14:26
 */

namespace api\models\forms;

use api\helpers\NotificationHelper;
use common\models\CompanyEntityLocation;
use common\models\CostCenter;
use common\models\Locations;
use common\models\User;
use common\models\UserLocation;
use Yii;
use yii\base\Model;

/**
 * Class CreateLocationForm
 * @package api\models\forms
 */
class CreateLocationForm extends Model
{
    public $name;
    public $company_id;
    public $zip_code;
    public $city;
    public $street;
    public $building;
    public $office;
    public $floor;
    public $phone_one;
    public $phone_two;
    public $isActive;
    public $activeDate;
    public $budgetLocationOnly;
    public $comment;
    public $costCenters = [];
    public $id;
    public $companyEntitiesIds = [];
    public $allCompanies;

    public function rules()
    {
        return [
            [['name', 'city', 'street', 'building', 'phone_one'], 'required'],
            [['company_id', 'zip_code', 'floor', 'phone_one', 'phone_two', 'activeDate',], 'integer'],
            [['isActive', 'budgetLocationOnly', 'allCompanies'], 'boolean'],
            [['company_id', 'zip_code', 'office', 'floor', 'phone_two',
                'isActive', 'activeDate', 'budgetLocationOnly', 'comment', 'costCenters', 'id',
                'companyEntitiesIds'], 'safe']
        ];
    }

    public function validate($attributeNames = null, $clearErrors = true)
    {
        if (!$this->allCompanies && empty($this->companyEntitiesIds)) {
            return $this->addError('companyEntitiesIds', 'Виберіть хоча б одну юр. особу');
        }
        return parent::validate($attributeNames, $clearErrors);
    }

    public function createLocation()
    {
        /** @var $user User */
        $user = Yii::$app->user->getIdentity();
        $model = new Locations();
        $model->name = $this->name;
        $model->company_id = $user->getMainCompanyId();
        $model->zip_code = $this->zip_code;
        $model->city = $this->city;
        $model->street = $this->street;
        $model->building = $this->building;
        $model->office = $this->office;
        $model->floor = $this->floor;
        $model->phone_two = $this->phone_two;
        $model->phone_one = $this->phone_one;
        $model->isActive = true;
        $model->allCompanies = $this->allCompanies;
        $model->user_id_responsible = $user->id;
        $result = $model->save();
        if ($result) {
            if (!empty($this->costCenters)) {
                CostCenter::addCostCenters($model->id, $this->costCenters);
            }
            if (!empty($this->companyEntitiesIds) && !$this->allCompanies) {
                CompanyEntityLocation::addLocations($model->id, $this->companyEntitiesIds);
            }

            $listUsers = User::find()->where(['company_id' => $user->getMainCompanyId(),'all_location' => 1])->all();

            foreach ($listUsers as $userDb)
            {
                $modelLocation = new UserLocation([
                    'user_id'     => $userDb->id,
                    'location_id' => $model->id
                ]);
                $modelLocation->save();
            }

            NotificationHelper::createdLocation($model);
        }
        return $result;
    }

    public function updateLocation()
    {
        /** @var $user User */
        $user = Yii::$app->user->getIdentity();
        $model = Locations::find()->where(['id' => $this->id])->one();
        if ($model != false) {
            $model->name = $this->name;
            $model->company_id = $user->getMainCompanyId();
            $model->zip_code = $this->zip_code;
            $model->city = $this->city;
            $model->street = $this->street;
            $model->building = $this->building;
            $model->office = $this->office;
            $model->floor = $this->floor;
            $model->phone_two = $this->phone_two;
            $model->phone_one = $this->phone_one;
//            $model->isActive = $this->isActive;
            $model->allCompanies = $this->allCompanies;
            $result = $model->save();
            if ($result) {
                CostCenter::updateCostCenters($model->id, $this->costCenters);
                CompanyEntityLocation::deleteAll(['location_id' => $this->id]);
                if (!empty($this->companyEntitiesIds) && !$this->allCompanies) {
                    CompanyEntityLocation::addLocations($model->id, $this->companyEntitiesIds);
                }
                NotificationHelper::updatedLocation($model);
            }
            return $result;
        }
        return false;
    }

    public function attributeLabels()
    {
        return [
            'name' => 'Назва локації',
            'zip_code' => 'Поштовий індекс',
            'city' => 'Місто',
            'street' => 'Вулиця',
            'building' => 'Номер будинку',
            'office' => 'Офіс',
            'floor' => 'Поверх',
            'phone_one' => 'Робочий номер телефону',
            'phone_two' => 'Додатковий номер телефону'
        ];
    }
}
