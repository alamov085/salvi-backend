<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 26.06.2019
 * Time: 14:53
 */

namespace api\models\forms;

use common\models\Company;
use common\models\CompanyEntity;
use Yii;
use yii\base\Model;

/**
 * Class CompanyForm
 * @package api\models\forms
 */
class CompanyForm extends Model
{
    public $id;
    public $address;
    public $edrpou;
    public $full_name;
    public $inn;
    public $short_name;
    public $show_catalog;
    public $manager_email;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['address', 'edrpou', 'full_name', 'short_name', 'inn'], 'required'],
            [['short_name', 'full_name', 'address', 'edrpou', 'inn', 'manager_email'], 'string', 'max' => 255],
            ['show_catalog', 'boolean'],
            ['id', 'safe'],
            ['edrpou', 'validateUniq']
        ];
    }

    public function validateUniq()
    {
        $query = Company::find()
            ->where(['edrpou' => $this->edrpou]);
        if (!is_null($this->id)) {
            $query->andWhere(['!=', 'id', $this->id]);
        }
        $company = $query->exists();
        if ($company) {
            $this->addError('edrpou', 'Цей код ЄДРПОУ вже є в базі');
        }
    }

    /**
     * @return bool
     */
    public function update()
    {
        $model = Company::findOne(['id' => $this->id]);
        if ($model != false) {
            $model->setAttributes([
                'address' => $this->address,
                'edrpou' => $this->edrpou,
                'full_name' => $this->full_name,
                'short_name' => $this->short_name,
                'inn' => $this->inn,
                'show_catalog' => $this->show_catalog,
                'manager_email' => $this->manager_email,
            ]);

            return $model->save();
        }
        return false;
    }

    public function attributeLabels()
    {
        return [
            'short_name' => 'Скорочена назва',
            'full_name' => 'Повна назва',
            'address' => 'Юридична адреса',
            'edrpou' => 'Код ЄДРПОУ',
            'inn' => 'ІПН',
            'manager_email' => "E-mail менеджера"
        ];
    }

    public function create()
    {
        $model = new Company([
            'address' => $this->address,
            'edrpou' => $this->edrpou,
            'full_name' => $this->full_name,
            'short_name' => $this->short_name,
            'inn' => $this->inn,
            'show_catalog' => 1,
            'catalog_limitation' => 1,
            'status' => Company::STATUS_ACTIVE,
            'type' => Company::TYPE_UR,
            'manager_email' => $this->manager_email
        ]);
        $result = $model->save();
        $companyEntity = false;
        if ($result) {
            $companyEntity = $this->registerCompanyEntity($model);
        }
        return ($result && $companyEntity);
    }

    private function registerCompanyEntity(Company $company)
    {
        $model = new CompanyEntity();
        $model->setAttributes([
            'company_id' => $company->id,
            'short_name' => $company->short_name,
            'full_name' => $company->full_name,
            'address' => $company->address,
            'bank_number' => '',
            'bank_name' => '',
            'mfo' => '',
            'edrpou' => $company->edrpou,
            'inn' => $company->inn,
            'tax_payer' => 0,
            'status' => CompanyEntity::STATUS_ACTIVE,
            'from_date' => null,
            'is_main' => 1,
            'manager_email' => $this->manager_email,
        ]);
        return ($model->save()) ? $model : false;
    }
}
