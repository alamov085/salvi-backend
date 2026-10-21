<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 03.02.2020
 * Time: 10:14
 */

namespace api\models\forms;

use common\models\AdminEmail;
use Yii;
use yii\base\Model;

/**
 * Class AdminEmailForm
 * @package api\models\forms
 */
class AdminEmailForm extends Model
{
    public $id;
    public $email;

    public function rules()
    {
        return [
            ['id', 'safe'],
            ['email', 'required'],
            ['email', 'string', 'max' => 50],
            ['email', 'email'],
            [
                'email',
                'unique',
                'filter' => ['!=', 'id', Yii::$app->request->post('id')],
                'targetClass' => '\common\models\AdminEmail',
                'message' => 'Цей email вже зайнятий'
            ]
        ];
    }

    /**
     * @return bool
     */
    public function create()
    {
        $model = new AdminEmail([
            'email' => $this->email,
            'status' => AdminEmail::STATUS_ACTIVE
        ]);
        return $model->save();
    }

    /**
     * @return bool
     */
    public function update()
    {
        $model = AdminEmail::findOne(['id' => $this->id]);
        if ($model) {
            $model->email = $this->email;
            return $model->save();
        }
        return false;
    }
}
