<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 08.07.2019
 * Time: 17:14
 */

namespace api\models\forms;

use api\helpers\NotificationHelper;
use common\models\AuthItem;
use common\models\AuthItemChild;
use common\models\AuthItemHistory;
use common\models\History;
use common\models\User;
use Yii;
use yii\base\Model;

/**
 * Class RolesForm
 * @package api\models\forms
 */
class RolesForm extends Model
{
    public $description;
    public $rules;
    public $name;

    public function rules()
    {
        return [
            [['description'], 'required'],
            [['rules', 'name', 'rules'], 'safe'],
            ['description', 'uniqueValue']
        ];
    }

    public function uniqueValue()
    {
        /** @var  $user User */
        $user = Yii::$app->user->getIdentity();
        $name = AuthItem::translate($this->description) . '_' . $user->company_id;
        if ($this->name == $name) {
            return true;
        }
        $result = AuthItem::find()->where(['name' => $name])->exists();
        if ($result) {
            return $this->addError('description', 'Вкажіть, будь ласка, іншу назву');
        }
        return true;
    }

    public function create()
    {
        /** @var  $user User */
        $user = Yii::$app->user->getIdentity();
        $auth = Yii::$app->authManager;
        $role = $auth->createRole(AuthItem::translate($this->description) . '_' . $user->company_id);
        $role->description = $this->description;
        $auth->add($role, $user->company_id);

        AuthItemHistory::saveHistory($role->name);

        if (!empty($this->rules)) {
            $data = [];
            foreach ($this->rules as $rule => $v) {
                $data[] = [
                    'parent' => $role->name,
                    'child' => $v
                ];
            }
            Yii::$app->db->createCommand()->batchInsert(
                AuthItemChild::tableName(),
                ['parent', 'child'],
                $data
            )->execute();
        }
        return true;
    }

    public function update()
    {
        /** @var  $user User */
        $user = Yii::$app->user->getIdentity();
        $auth = Yii::$app->authManager;
        $role = $auth->getRole($this->name);
        $roleOld = AuthItem::find()->where(['name' => $this->name])->one();
        if ($roleOld) {
            $userId = $roleOld->company_id;
        } else {
            $userId = $user->company_id;
        }
        $userIds = $auth->getUserIdsByRole($this->name);
        $auth->remove($role);
        $role = $auth->createRole($this->name);
        $role->description = $this->description;
        $auth->add($role, $userId);
        if (!empty($this->rules)) {
            $data = [];
            foreach ($this->rules as $rule => $v) {
                if ($v) {
                    $data[] = [
                        'parent' => $role->name,
                        'child' => $v
                    ];
                }
            }
            Yii::$app->db->createCommand()->batchInsert(
                AuthItemChild::tableName(),
                ['parent', 'child'],
                $data
            )->execute();
        }
        AuthItem::updateAll(['default_role' => 1], ['in', 'name', ['admin', 'managerUser', 'mainUser', 'defaultUser']]);
        if (count($userIds)) {
            foreach ($userIds as $id) {
                $auth->assign($role, $id);
            }
            NotificationHelper::updateRole($userIds);
        }
        if ($user->company_id) {
            NotificationHelper::changeUserRoleManager($this->description, $user->company_id);
        } else {
            NotificationHelper::changeManagerRoleManager($this->description, $userIds);
        }
        return true;
    }

    public function attributeLabels()
    {
        return [
            'description' => 'Назва'
        ];
    }
}
