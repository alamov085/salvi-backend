<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 12.07.2019
 * Time: 13:52
 */

namespace api\models\forms;

use common\models\Company;
use common\models\User;
use yii\base\Model;

/**
 * Class MyCompanyForm
 * @package api\models\forms
 */
class MyCompanyForm extends Model
{
    public $id;
    public $currency_id;
    public $empty_string;
    public $budgets;
    public $non_budget_order;
    public $coordination;
    public $po_number_required;

    public function rules()
    {
        return [
            [['id', 'budgets'], 'safe'],
            ['currency_id', 'integer'],
            [['empty_string', 'non_budget_order', 'coordination', 'po_number_required'], 'boolean']
        ];
    }

    public function update()
    {
        /** @var  $user User */
        $user = \Yii::$app->user->getIdentity();
        $model = Company::find()->where(['id' => $user->company_id])->one();
        if ($model != false) {
//            $model->budgets = $this->budgets;
//            $model->currency_id = $this->currency_id;
            $model->empty_string = $this->empty_string;
            $model->non_budget_order = $this->non_budget_order;
            $model->coordination = $this->coordination;
            $model->po_number_required = $this->po_number_required;
            return $model->save();
        }
        return false;
    }
}
