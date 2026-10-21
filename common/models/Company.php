<?php

namespace common\models;

use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "company".
 *
 * @property int $id
 * @property int $type
 * @property string $short_name
 * @property string $full_name
 * @property string $address
 * @property string $edrpou
 * @property string $inn
 * @property int $created_at
 * @property int $updated_at
 * @property ActiveQuery|CompanyEntity $companyEntities
 * @property int $status
 * @property int $show_catalog
 * @property int $empty_string
 * @property int $budgets
 * @property int $non_budget_order
 * @property int $coordination
 * @property ActiveQuery|Locations $locations
 * @property int $currency_id
 * @property ActiveQuery|CatalogLimitation $categories
 * @property int $catalog_limitation
 * @property History|ActiveQuery $history
 * @property int $specification
 * @property int $new_category
 * @property int $max_category_id
 * @property int $po_number_required
 * @property string $manager_email
 */
class Company extends ActiveRecord
{
    const TYPE_FOP = 1;
    const TYPE_UR = 2;

    const STATUS_DISMISSED = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_WAITING = 2;
    const STATUS_APPROVED = 3;


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'company';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['type', 'created_at', 'updated_at', 'status', 'currency_id', 'catalog_limitation',
                'specification', 'max_category_id'], 'integer'],
            [['short_name', 'full_name', 'address', 'edrpou', 'inn', 'manager_email'], 'string', 'max' => 255],
            [['show_catalog', 'empty_string', 'budgets', 'non_budget_order', 'coordination', 'new_category',
                'po_number_required'], 'boolean']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'         => 'ID',
            'type'       => 'Type',
            'short_name' => 'Short Name',
            'full_name'  => 'Full Name',
            'address'    => 'Address',
            'edrpou'     => 'Edrpou',
            'inn'        => 'Inn',
            'status'     => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'manager_email' => 'Managers Email'
        ];
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class'      => 'yii\behaviors\TimestampBehavior',
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at', 'updated_at'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public static function getTypes()
    {
        return [
            0              => '',
            self::TYPE_FOP => 'Физическое лицо',
            self::TYPE_UR  => 'Юридическое лицо'
        ];
    }

    public function approveCompany()
    {
        $this->status = self::STATUS_APPROVED;
    }

    public function dismissCompany()
    {
        $this->status = self::STATUS_DISMISSED;
    }

    public function activateCompany()
    {
        $this->status = self::STATUS_ACTIVE;
    }

    /**
     * @return ActiveQuery | CompanyEntity
     */
    public function getCompanyEntities()
    {
        return $this->hasMany(CompanyEntity::class, ['company_id' => 'id']);
    }

    /**
     * @return array
     */
    public static function statuses()
    {
        $result = [
            self::STATUS_DISMISSED => [
                'id'    => self::STATUS_DISMISSED,
                'name'  => 'Деактивоване',
                'class' => 'red',
                'color' => '#F24949',
                'icon'  => 'error'
            ],
            self::STATUS_ACTIVE    => [
                'id'    => self::STATUS_ACTIVE,
                'name'  => 'Активне',
                'class' => 'green',
                'color' => '#64DD17',
                'icon'  => 'check_circle'
            ],
            self::STATUS_WAITING   => [
                'id'    => self::STATUS_WAITING,
                'name'  => 'Чекає активації',
                'class' => 'orange',
                'color' => '#FCBD00',
                'icon'  => 'update'
            ],
            self::STATUS_APPROVED  => [
                'id'    => self::STATUS_APPROVED,
                'name'  => 'Підтверджене',
                'class' => 'green',
                'color' => '#64DD17',
                'icon'  => 'done'
            ]
        ];
        return $result;
    }

    /**
     * Подтверждение предприятия.
     * Мы должны подтвердить предприятие, юр.лицо и пользователя которые создались при регистрации.
     * @return bool
     */
    public function approveRegistration()
    {
        $this->activateCompany();
        $companyEntity = CompanyEntity::find()
            ->where(['company_id' => $this->id])
            ->andWhere(['is_main' => 1])
            ->one();
        $result = false;
        if ($companyEntity != false) {
            $result = $companyEntity->activateCompany()->save();
        }
        $user = User::find()->where(['company_id' => $this->id])->andWhere(['manager' => 0])->one();
        if ($user != false) {
            if ($this->save() && $result && $user->approveUser()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Отклоняем заявку на регистрацию.
     * @param $reason
     * @return bool
     */
    public function refuseRegistration($reason)
    {
        $this->dismissCompany();
        $user = User::find()
            ->where(['company_id' => $this->id])
            ->andWhere(['!=', 'manager', 1])
            ->one();
        $companyEntity = CompanyEntity::find()
            ->where(['company_id' => $this->id])
            ->andWhere(['is_main' => 1])
            ->one();
        $result = false;
        if ($companyEntity != false) {
            $result = $companyEntity->dismissCompany()->save();
        }
        if ($user != false) {
            if ($user->refuseUser($reason)) {
                if ($this->save() && $result) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Отменить регистрацию.
     * Пользователей и юр.лица деактивируем.
     * @return bool
     */
    public function rollBackRegistration()
    {
        $this->status = self::STATUS_WAITING;
        $result = $this->save();
        if ($result) {
            CompanyEntity::updateAll(['status' => CompanyEntity::STATUS_DISMISSED], ['company_id' => $this->id]);
            User::updateAll(
                ['status' => User::STATUS_INACTIVE],
                ['and', ['company_id' => $this->id], ['manager' => 0]]
            );
        }
        return $result;
    }

    /**
     * @return ActiveQuery | Locations
     */
    public function getLocations()
    {
        return $this->hasMany(Locations::class, ['company_id' => 'id']);
    }

    /**
     * @return ActiveQuery | CatalogLimitation
     */
    public function getCategories()
    {
        return $this->hasMany(CatalogLimitation::class, ['company_id' => 'id'])
            ->onCondition(['catalog_limitation.specification' => 0]);
    }

    public function afterSave($insert, $changedAttributes)
    {
        if ($insert) {
            History::saveHistory(History::COMPANY, $this->id);
        }
        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * @return ActiveQuery | History
     */
    public function getHistory()
    {
        return $this->hasOne(History::class, ['entity_id' => 'id'])
            ->andOnCondition(['entity' => History::COMPANY]);
    }
}
