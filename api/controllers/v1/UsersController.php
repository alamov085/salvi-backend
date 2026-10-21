<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 17.06.2019
 * Time: 12:50
 */

namespace api\controllers\v1;

use api\controllers\RestController;
use api\helpers\NotificationHelper;
use api\helpers\UserEmailHelper;
use api\models\forms\CreateUserForm;
use common\models\AuthAssignment;
use common\models\AuthItem;
use common\models\CompanyEntity;
use common\models\Coordination;
use common\models\Currency;
use common\models\Locations;
use common\models\User;
use common\models\CostCenter;
use common\models\UserCostCenter;
use common\models\UserLocation;
use Yii;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use yii\helpers\ArrayHelper;

/**
 * Class UsersController
 * @package api\controllers\v1
 */
class UsersController extends RestController
{
    public function actionCreateUser()
    {
        $model = new CreateUserForm();
        $model->scenario = 'create';
        if ($model->load(Yii::$app->getRequest()->getBodyParams(), '') && $model->validate() &&
            $model->createUser()) {
            return $this->successResponse();
        }
        $model->validate();
        return $model;
    }

    public function actionGetList()
    {
        /** @var  $user User */
        $user = Yii::$app->user->getIdentity();
        $select = [
            'user.id',
            'first_name',
            'last_name',
            'email',
            'phone_number',
            'position',
            'all_location',
            'auth_assignment.item_name as role_name',
            'company_entity.short_name as company_name'
        ];
        $select[] = 'user.status';
        $roleQuery = AuthItem::find()->where(['type' => 1]);
        $locationQuery = Locations::find();
        $query = User::find()
            ->select($select)
            ->joinWith(['locations', 'costCenters', 'role', 'companyEntity']);
        if ($user->mainCompanyId) {
            $query->where(['in', 'user.company_id', $user->companyList]);
            $roleQuery->andWhere(['company_id' => $user->mainCompanyId]);
            $locationQuery->where(['in', 'locations.company_id', $user->companyList]);
        }
        $roles = $roleQuery->all();
        $location = $locationQuery->all();
        $query->groupBy(['first_name']);
        $model = $query->offset($this->page * $this->perPage)
            ->limit($this->perPage)
            ->asArray()
            ->all();
        if (!empty($model)) {
            foreach ($model as $k => $value) {
                $model[$k]['all_location'] = (int)$value['all_location'];
                if (!empty($value['locations'])) {
                    $model[$k]['locations'] = [];
                    foreach ($value['locations'] as $loc) {
                        $model[$k]['locations'][] = (int)$loc['location_id'];
                    }
                }
                if (!empty($value['costCenters'])) {
                    $model[$k]['cost_centers'] = [];
                    foreach ($value['costCenters'] as $loc) {
                        $model[$k]['cost_centers'][] = (int)$loc['cost_center_id'];
                    }
                }
            }
        }
        $result = new ArrayDataProvider([
            'models' => $model,
            'pagination' => [
                'pageSize' => $this->perPage,
                'page' => $this->page,
                'totalCount' => $query->count('*')
            ],
            'sort' => [
                'attributes' => [
                    'id',
                    'email'
                ]
            ]
        ]);
        return $this->vueTableResponse($result, ['roles' => $roles, 'locations' => $location]);
    }

    public function actionUpdateUser()
    {
        $model = new CreateUserForm();
        if ($model->load(Yii::$app->getRequest()->getBodyParams(), '') && $model->validate() &&
            $model->updateUser()) {
            return $this->successResponse();
        }
        $model->validate();
        return $model;
    }

    public function actionDeactivate()
    {
        if (Yii::$app->user->can('statusUser')) {
            $model = User::find()->where(['id' => ArrayHelper::getValue($this->post, 'id', 0)])->one();
            if ($model != false) {
                $model->status = User::STATUS_INACTIVE;
                $model->generateAuthKey();
                if ($model->save()) {
                    $role = \Yii::$app->authManager->getRolesByUser($model->id);
                    $role = array_keys($role);
                    UserEmailHelper::deactivatedUser($model);
                    NotificationHelper::changeStatusUserManager($model, 0);
                    Coordination::checkCoordination(ArrayHelper::getValue($role, 0, ''));
                    return $this->successResponse();
                }
            }
            return $this->failResponse();
        }
        return $this->accessDenied();
    }

    public function actionActivate()
    {
        if (Yii::$app->user->can('statusUser')) {
            $model = User::find()->where(['id' => ArrayHelper::getValue($this->post, 'id', 0)])->one();
            if ($model != false) {
                $model->status = User::STATUS_ACTIVE;
                $model->generateAuthKey();
                if ($model->save()) {
                    UserEmailHelper::activatedUser($model);
                    NotificationHelper::changeStatusUserManager($model, 1);
                    return $this->successResponse();
                }
            }
            return $this->failResponse();
        }
        return $this->accessDenied();
    }

    public function actionGetUsersByRole()
    {
        /** @var  $user User */
        $user = Yii::$app->user->getIdentity();
        $roles = ArrayHelper::getValue($this->post, 'roles', []);
        $result = AuthAssignment::find()->where(['in', 'item_name', $roles])->all();
        $users = User::find()
            ->select(['id', 'concat(first_name," ", last_name) as name', 'status'])
            ->where(['in', 'id', ArrayHelper::getColumn($result, 'user_id')])
            ->andWhere(['company_id' => $user->company_id])
            ->andWhere(['status'=>User::STATUS_ACTIVE])
            ->asArray()
            ->all();
//        if ($users) {
//            foreach ($users as $k => $user) {
//                if ($user['status'] == User::STATUS_ACTIVE) {
//                    $users[$k]['disabled'] = false;
//                } else {
//                    $users[$k]['disabled'] = true;
//                }
//            }
//        }
        return $this->successResponse(['users' => $users]);
    }

    public function actionGeneratePassword() {
        $userId = ArrayHelper::getValue($this->post, 'id', null);

        if(!$userId) {
            return $this->failResponse(['errorMessage' => 'Користувача не знайдено!']);
        }

        $user = User::find()->where(['id' => $userId])->one();

        if(!$user) {
            return $this->failResponse(['errorMessage' => 'Користувача не знайдено!']);
        }

        $password = Yii::$app->security->generateRandomString(8);;
        $user->password = $password;

        if($user->save()) {
            UserEmailHelper::passwordChanged($user, $password);

            return $this->successResponse(['user' => $user, 'password' => $password]);
        } else {
            return $this->failResponse(['errorMessage' => "Не вдалося змінити пароль користувача."]);
        }


    }

    public function actionGetInfoPageUsers()
    {
        $result = [];

        $result['authItem'] = [];

        $requestParams = Yii::$app->getRequest()->get();

        $userFull = Yii::$app->user->getIdentity();

        $companyId = $userFull->company_id;

        $offset = 0;

        if(!isset($requestParams['order']))
        {
            $requestParams['order'] = 'desc';
        }

        if(!isset($requestParams['orderBy']))
        {
            $requestParams['orderBy'] = 'id';
        }

        if(!isset($requestParams['perPage']))
        {
            $requestParams['perPage'] = 60;
        }

        if(!isset($requestParams['page']))
        {
            $requestParams['page'] = 1;
        }

        if($requestParams['page'] != 1)
        {
            $offset = $requestParams['page'] * $requestParams['perPage'] - $requestParams['perPage'];
        }

        $currency = Currency::find()->all();

        $users = User::find()
            ->where(['manager' => 0])
            ->andWhere(['company_id' => $companyId])
            ->limit($requestParams['perPage'])
            ->offset($offset)
            ->orderBy([$requestParams['orderBy'] => $requestParams['order']])
            ->joinWith(['role'])
            ->all();

        $userIds = array_column($users,'id');

        $costCentersForUsers = [];

        $costCentersForUsersTmp = UserCostCenter::find()
            ->select(['*'])
            ->where(['user_id' => $userIds])
            ->all();

        foreach ($costCentersForUsersTmp as $item)
        {
            $costCentersForUsers[$item->user_id][] = $item;
        }

        $locationsForUsers = [];

        $locationsFoUsersTmp = UserLocation::find()
            ->select(['*'])
            ->where(['user_id' => $userIds])
            ->all();

        foreach ($locationsFoUsersTmp as $item)
        {
            $locationsForUsers[$item->user_id][] = $item;
        }

        foreach ($users as &$user)
        {
            $roleDescription = ArrayHelper::getValue($user, 'role.itemName.description', '');

            $role_name = '';

            if(!is_null($user->role) && !empty($user->role))
            {
                $role_name = $user->role->item_name;
            }

            $user = $user->toArray();

            $user['role_description'] = $roleDescription;

            $user['role_name'] = $role_name;

            $user['locations'] = [];

            $user['costCenters'] = [];

            if(isset($locationsForUsers[$user['id']]))
            {
                $user['locations'] = $locationsForUsers[$user['id']];
            }

            $user['currency'] = $currency;

            if(isset($costCentersForUsers[$user['id']]))
            {
                $user['costCenters'] = $costCentersForUsers[$user['id']];
            }
        }

        unset($user);



        $authItem = AuthItem::find()
            ->where(['company_id' => $companyId])
            ->orWhere(['name' => ['mainUser','defaultUser']])
            ->all();

        $result['authItem'] = $authItem;

        $result['user'] = [
            'items' => $users,
            'totalCount' => $users = User::find()
                ->where(['manager' => 0])
                ->andWhere(['company_id' => $companyId])
                ->count(),
        ];

        $locations = Locations::find()
            ->select(['id','name'])
            ->where(['company_id' => $companyId])
            ->andWhere(['isActive' => true])
            ->all();

        $result['location'] = [
            'items' => $locations
        ];

        $result['costCenter'] = [
            'items' => [],
            'totalCount' => 0,
        ];

        if(!empty($locations))
        {
            $costCenter = CostCenter::find()
                ->select(['id','name','location_id'])
                ->where(['location_id' => array_column($locations,'id')])
                ->all();

            $result['costCenter'] = [
                'items' => $costCenter,
                'totalCount' => count($costCenter),
            ];
        }

        return $this->successResponse([$result]);
    }
    
    public function actionUnsubscribeMails()
    {
    	$user = current(User::find()->where(['id' => Yii::$app->user->getId()])->all());
	
	    $user->is_sending_mail = 0;
	    
	    $user->save();
	
	    $result = [
	    	'status' => true
	    ];
	    
	    return $this->successResponse($result);
    }

    public function actionSpecialChangePassword()
    {
        $user = current(User::find()->where(['id' => Yii::$app->user->getId()])->all());

        $newPassword = Yii::$app->getRequest()->getBodyParam('new-password', "");

        $repeatNewPassword = Yii::$app->getRequest()->getBodyParam('repeat-new-password', "");

        if(empty($newPassword) || empty($repeatNewPassword))
        {
            $result = [
                'status' => false,
                'error' => 'Новий пароль або пароль підтвердження не заповнені'
            ];

            return $this->successResponse($result);
        }


        if($newPassword != $repeatNewPassword)
        {
            $result = [
                'status' => false,
                'error' => 'Новий пароль та пароль підтвердження не збігаються'
            ];

            return $this->successResponse($result);
        }

        if(strlen($newPassword) < 8)
        {
            $result = [
                'status' => false,
                'error' => 'Новий пароль повинен мати 8 і більше символів'
            ];

            return $this->successResponse($result);
        }

        $oldHash = $user->password_hash;

        if (Yii::$app->security->validatePassword($newPassword, $oldHash))
        {
            $result = [
                'status' => false,
                'error' => 'Новий пароль не повинен співпадати зі старим.'
            ];

            return $this->successResponse($result);
        }

        $user->is_need_change_password = 0;

        $user->setPassword($newPassword);

        $user->removePasswordResetToken();

        $user->save(false);

        $result = [
            'status' => true
        ];

        return $this->successResponse($result);
    }


}
