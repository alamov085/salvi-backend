<?php

namespace common\models;

use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "company_entity".
 *
 * @property int $id
 * @property int $company_id
 * @property string $short_name
 * @property string $full_name
 * @property string $address
 * @property string $bank_number
 * @property string $bank_name
 * @property string $mfo
 * @property string $edrpou
 * @property string $inn
 * @property string $tax_payer
 * @property int $status
 * @property int $from_date
 * @property int $created_at
 * @property ActiveQuery|Company $company
 * @property int $updated_at
 * @property ActiveQuery|CompanyEntityLocation $companyEntityLocation
 * @property int $is_main
 * @property History|ActiveQuery $history
 * @property int $status_check
 * @property string $manager_email
 */
class CompanyEntity extends ActiveRecord
{
    const STATUS_DISMISSED = 0;
    const STATUS_ACTIVE = 1;

    const TAX_DEFAULT = 0;
    const TAX_EASY = 1;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'company_entity';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['company_id', 'status', 'created_at', 'updated_at', 'is_main', 'status_check'], 'integer'],
            [['short_name', 'full_name', 'address', 'edrpou', 'inn'], 'string', 'max' => 255],
            [['from_date', 'bank_number', 'bank_name', 'tax_payer', 'mfo'], 'safe']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'company_id' => 'Company ID',
            'short_name' => 'Скорочена назва',
            'full_name' => 'Повна назва',
            'address' => 'Юридична адреса',
            'bank_number' => 'Bank Number',
            'bank_name' => 'Bank Name',
            'mfo' => 'Mfo',
            'edrpou' => 'Код ЄДРПОУ',
            'inn' => 'ІПН',
            'tax_payer' => 'Tax Payer',
            'status' => 'Status',
            'from_date' => 'From Date',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public function save($runValidation = true, $attributeNames = null)
    {
        if ($this->isNewRecord) {
            $this->created_at = date('U');
            $this->updated_at = date('U');
        } else {
            $this->updated_at = date('U');
        }
        return parent::save($runValidation, $attributeNames);
    }

    /**
     * @return $this
     */
    public function activateCompany()
    {
        $this->status = self::STATUS_ACTIVE;
        return $this;
    }

    /**
     * @return ActiveQuery | Company
     */
    public function getCompany()
    {
        return $this->hasOne(Company::class, ['id' => 'company_id']);
    }

    /**
     * @return $this
     */
    public function dismissCompany()
    {
        $this->status = self::STATUS_DISMISSED;
        return $this;
    }

    /**
     * @return array
     */
    public static function statuses()
    {
        $result = [
            self::STATUS_DISMISSED => [
                'id' => self::STATUS_DISMISSED,
                'name' => 'Не активна',
                'class' => 'red'
            ],
            self::STATUS_ACTIVE => [
                'id' => self::STATUS_ACTIVE,
                'name' => 'Активна',
                'class' => 'green'
            ]
        ];
        return $result;
    }

    /**
     * @return bool
     */
    public function makeAsMain()
    {
        self::updateAll(['is_main' => 0], ['company_id' => $this->company_id]);
        $this->is_main = 1;
        return $this->save();
    }

    /**
     * @return ActiveQuery | CompanyEntityLocation
     */
    public function getCompanyEntityLocation()
    {
        return $this->hasMany(CompanyEntityLocation::class, ['company_id' => 'id']);
    }

    /**
     * @param $status
     * @return bool
     */
    public function changeStatus($status)
    {
        $statuses = ArrayHelper::getColumn(self::statuses(), 'id');
        if (in_array($status, $statuses)) {
            $this->status = $status;
            return $this->save();
        }
        return false;
    }

    public static function taxOption()
    {
        return [
            self::TAX_DEFAULT => [
                'id' => self::TAX_DEFAULT,
                'name' => 'На загальних підставах'
            ],
            self::TAX_EASY => [
                'id' => self::TAX_EASY,
                'name' => 'Спрощена система оподаткування'
            ]
        ];
    }

    public function afterSave($insert, $changedAttributes)
    {
        if ($insert) {
            History::saveHistory(History::COMPANY_ENTITY, $this->id);
        }
        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * @return ActiveQuery | History
     */
    public function getHistory()
    {
        return $this->hasOne(History::class, ['entity_id' => 'id'])
            ->andOnCondition(['entity' => History::COMPANY_ENTITY]);
    }
}
