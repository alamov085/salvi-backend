<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 08.07.2019
 * Time: 14:01
 */

namespace api\models\forms;

use common\models\CompanySupplier;
use common\models\Contract;
use common\models\Supplier;
use yii\base\Model;
use yii\helpers\ArrayHelper;

/**
 * Class SupplierForm
 * @package api\models\forms
 */
class SupplierForm extends Model
{
    public $id;
    public $name;
    public $edrpou;
    public $inn;
    public $description;
    public $companies;

    public function rules()
    {
        return [
            [['name', 'inn', 'edrpou'], 'required'],
            [['description'], 'string'],
            [['name', 'edrpou', 'inn'], 'string', 'max' => 255],
            [['id', 'companies'], 'safe']
        ];
    }

    /**
     * @return bool
     */
    public function create()
    {
        $model = new Supplier();
        $model->setAttributes([
            'name' => $this->name,
            'edrpou' => $this->edrpou,
            'inn' => $this->inn,
            'description' => $this->description,
            'status' => Supplier::STATUS_ACTIVE
        ]);
        $result = $model->save();
        if ($result) {
            if (!empty($this->companies)) {
                CompanySupplier::addCompanies($model->id, $this->companies);
            }
        }
        return $result;
    }

    /**
     * @return bool
     */
    public function update()
    {
        $model = Supplier::find()->where(['id' => $this->id])->one();
        if ($model != false) {
            $model->name = $this->name;
            $model->description = $this->description;
            $model->edrpou = $this->edrpou;
            $model->inn = $this->inn;
            $result = $model->save();
            if ($result) {
                $companies = CompanySupplier::find()->where(['supplier_id' => $model->id])->all();
                if ($companies) {
                    $companies = ArrayHelper::getColumn($companies, 'company_id');
                    $diff = array_diff($companies, $this->companies);
                    if ($diff) {
                        Contract::updateAll(
                            ['status' => Contract::STATUS_NON_ACTIVE],
                            [
                                'and',
                                ['in', 'company_id', $diff],
                                ['supplier_id' => $model->id]
                            ]
                        );
                    }
                }
                CompanySupplier::deleteAll(['supplier_id' => $model->id]);
                if (!empty($this->companies)) {
                    CompanySupplier::addCompanies($model->id, $this->companies);
                }
            }
            return $result;
        }
        return false;
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'name' => 'Найменування',
            'inn' => 'ІПН',
            'edrpou' => 'Код ЄДРПОУ',
            'description' => 'Опис',
        ];
    }
}
