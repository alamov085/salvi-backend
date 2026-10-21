<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 26.06.2019
 * Time: 17:07
 */

namespace api\models\forms;

use common\models\CompanyEntity;
use yii\base\Model;

/**
 * Class CompanyEntityForm
 * @package api\models\forms
 */
class CompanyEntityForm extends Model
{
    public $id;
    public $address;
    public $bank_name;
    public $bank_number;
    public $company_id;
    public $edrpou;
    public $from_date;
    public $full_name;
    public $inn;
    public $mfo;
    public $short_name;
    public $tax_payer;
    public $status_check;

    public function rules()
    {
        return [
            ['short_name', 'required'],
            [['company_id', 'status_check'], 'integer'],
            [['short_name', 'full_name', 'address', 'edrpou', 'inn'], 'string', 'max' => 255],
            [['from_date', 'bank_number', 'bank_name', 'tax_payer', 'id'], 'safe'],
            [['from_date'], 'filter', 'filter' => function ($value) {
                if (is_numeric($value)) {
                    return $value;
                }
                return strtotime($value);
            }],
            ['mfo', 'integer'],
            ['edrpou', 'validateUniq']
        ];
    }

    /**
     * @return bool
     * @throws \Throwable
     */
    public function create()
    {
        $model = new CompanyEntity();
        $model->setAttributes([
            'short_name' => $this->short_name,
            'full_name' => $this->full_name,
            'address' => $this->address,
            'edrpou' => $this->edrpou,
            'inn' => $this->inn,
            'company_id' => \Yii::$app->user->getIdentity()->company_id,
            'from_date' => $this->from_date,
            'bank_number' => $this->bank_number,
            'bank_name' => $this->bank_name,
            'tax_payer' => $this->tax_payer,
            'mfo' => $this->mfo,
            'status' => CompanyEntity::STATUS_ACTIVE,
            'status_check' => $this->status_check
        ]);
        return $model->save();
    }

    /**
     * @return bool
     */
    public function update()
    {
        $model = CompanyEntity::findOne(['id' => $this->id]);
        if ($model != false) {
            $model->setAttributes([
                'short_name' => $this->short_name,
                'full_name' => $this->full_name,
                'address' => $this->address,
                'edrpou' => $this->edrpou,
                'inn' => $this->inn,
//                'company_id' => $this->company_id,
                'from_date' => $this->from_date,
                'bank_number' => $this->bank_number,
                'bank_name' => $this->bank_name,
                'tax_payer' => $this->tax_payer,
                'mfo' => $this->mfo,
                'status_check' => $this->status_check
            ]);
            if ($this->status_check) {
                $model->status = CompanyEntity::STATUS_ACTIVE;
            }
            return $model->save();
        }
        return false;
    }

    public function validateUniq()
    {
        $query = CompanyEntity::find()
            ->where(['edrpou' => $this->edrpou]);
        if (!is_null($this->id)) {
            $query->andWhere(['!=', 'id', $this->id]);
        }
        $company = $query->exists();
        if ($company) {
            $this->addError('edrpou', 'Цей код ЄДРПОУ вже є в базі');
        }
    }

    public function attributeLabels()
    {
        return [
            'address' => 'Юридична адреса',
            'bank_name' => 'Банк',
            'bank_number' => 'Розрахунковий рахунок',
            'edrpou' => 'ЄДРПОУ',
            'full_name' => 'Юридична особа',
            'inn' => 'ІПН',
            'mfo' => 'МФО',
            'short_name' => 'Юридична особа',
            'tax_payer' => 'Статус платника податків на прибуток',
        ];
    }
}
