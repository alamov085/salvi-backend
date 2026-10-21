<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 28.08.2019
 * Time: 10:00
 */

namespace api\helpers;

use common\models\Budget;
use common\models\ChatUser;
use common\models\Company;
use common\models\CompanyEntity;
use common\models\Contract;
use common\models\Coordination;
use common\models\CostCenter;
use common\models\Locations;
use common\models\Notification;
use common\models\Order;
use common\models\OrderCoordination;
use common\models\OrderProduct;
use common\models\Specification;
use common\models\User;
use Exception;
use Redis;
use Throwable;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

/**
 * Class NotificationHelper
 * @package api\helpers
 */
class NotificationHelper
{
    /**
     * Масовое добавление уведомлений
     * @param array $users
     * @param string $message
     * @return bool
     * @throws Throwable
     */
    private static function batchInsert(array $users, string $message): bool
    {
        $data = [];
        if (!empty($users)) {
            /** @var  $currentUser User */
            $currentUser = Yii::$app->user->getIdentity();
            foreach ($users as $id) {
                if ($currentUser && $id == $currentUser->id) {
                    //Оставляем возможность инициатору уведомления добавить его
                } else {
                    $data[] = [
                        'status'     => Notification::STATUS_SHOW,
                        'user_id'    => $id,
                        'is_read'    => 0,
                        'message'    => $message,
                        'created_at' => date('U')
                    ];
                }
            }
            if (!empty($data)) {
                Yii::beginProfile('notification');
                try {
                    Yii::$app->db->createCommand()->batchInsert(
                        Notification::tableName(),
                        ['status', 'user_id', 'is_read', 'message', 'created_at'],
                        $data
                    )->execute();
                    $result = true;
                } catch (Exception $e) {
                    Yii::error($e);
                    $result = false;
                }
                if ($result) {
                    Yii::beginProfile('socket');
                    try {
                        self::sendNotificationToSocket($data);
                    } catch (Exception $e) {
                        //websocket is die
                    }
                    Yii::endProfile('socket');
                }
                Yii::endProfile('notification');
                return $result;
            }
        }
        return false;
    }

    /**
     * @param User $users
     * @param string $function
     * @param array $data
     * @throws Throwable
     */
    private static function sendMsg($users, string $function, array $data)
    {
        /** @var  $currentUser User */
        $currentUser = Yii::$app->user->getIdentity();
        if ($users) {

            foreach ($users as $user) {
                if ($currentUser && $user->id == $currentUser->id) {
                    //Оставляем возможность инициатору уведомления добавить его
                } elseif($user->is_sending_mail == 1) {
                    $data['user'] = $user;
                    call_user_func(__NAMESPACE__ . '\UserEmailHelper::' . $function, $data);
                }
            }
        }
    }

    /**
     * @param $users
     * @param string $function
     * @param array $data
     * @throws Throwable
     */
    private static function sendMsgConsole($users, string $function, array $data)
    {
        if ($users)
        {
            foreach ($users as $user)
            {
                if($user->is_sending_mail == 1)
                {
                    $data['user'] = $user;

                    call_user_func(__NAMESPACE__ . '\UserEmailHelper::' . $function, $data);
                }
            }
        }
    }

    /**
     * @param $users
     * @param string $view
     * @param array $data
     * @throws Throwable
     */
    private static function sendMsgDefault($users, string $view, array $data)
    {
    	
        /** @var  $currentUser User */
        $currentUser = Yii::$app->user->getIdentity();
        if ($users) {
            foreach ($users as $user) {
                if ($currentUser && $user->id == $currentUser->id) {
                    //Оставляем возможность инициатору уведомления добавить его
                } elseif($user->is_sending_mail == 1) {
                    $data['user'] = $user;
                    $data['view'] = $view;
                    UserEmailHelper::defaultMessage($data);
                }
            }
        }
    }

    /**
     * Список пользователей по праву доступа и предприятию
     * @param $permission
     * @param $company_id
     * @return array
     */
    private static function getUserList(string $permission, int $company_id): array
    {
        $data = ['ids' => [], 'model' => []];
        $users = RbacHelper::getUserListByPermission($permission);
        $model = User::find()
            ->where(['company_id' => $company_id])
            ->andWhere(['in', 'id', $users])
            ->andWhere(['manager' => 0])
            ->andWhere(['status' => User::STATUS_ACTIVE])
            ->all();
        $data['model'] = $model;
        if (!empty($model)) {
            $data['ids'] = ArrayHelper::getColumn($model, 'id');
        }
        return $data;
    }

    private static function sendNotificationToSocket($data)
    {
        try {
            $redis = Yii::$app->redis;
            if ($data) {
                foreach ($data as $user) {
                    $redis->publish(
                        'notification_' . ArrayHelper::getValue($user, 'user_id', 0),
                        'new notification'
                    );
                }
            }
            $redis->close();
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
    }

    /**
     * @param string $permission
     * @param int $company_id
     * @param bool $one_company
     * @return array
     */
    private static function getManagers(string $permission, int $company_id, bool $one_company = true, int $location_id = 0): array
    {
        $users = RbacHelper::getUserListByPermission($permission);
        $models = User::find()
            ->joinWith(['userCompany', 'userLocation ul'])
            ->andWhere(['in', 'user.id', $users])
            ->andWhere(['manager' => 1]);

        if(!empty($location_id)) {
            $models->andWhere(['ul.location_id' => $location_id]);
        }

        $models = $models->all();

        if ($models) {
            $data = [];
            foreach ($models as $model) {
                if ($one_company) {
                    if (in_array($company_id, $model->managerCompanies)) {
                        $data[] = $model->id;
                    }
                } else {
                    $data[] = $model->id;
                }
            }
            return $data;
        }
        return [];
    }

    /**
     * @param $chat_id
     * @return bool
     */
    public static function chatWasClosed(int $chat_id): bool
    {
        $users = ChatUser::find()->where(['chat_id' => $chat_id])->asArray()->all();
        $users = ArrayHelper::getColumn($users, 'user_id');
        $message = 'Діалог був завершений співрозмовником.';
        return self::batchInsert($users, $message);
    }

    /**
     * @param Contract $model
     * @return bool
     */
    public static function createdContract(Contract $model): bool
    {
        $users = self::getUserList('controlContract', $model->company_id);
        $companyEntity = CompanyEntity::find()->where(['id' => $model->company_entity_id])->one();
        $message = 'Був створений договір "' . $model->name . '" під юридичну особу "' .
            ArrayHelper::getValue($companyEntity, 'short_name') . '"';
        self::sendMsg($users['model'], 'contractCreated', ['model' => $model, 'companyEntity' => $companyEntity]);
        self::createdContractManager($model, $companyEntity);
        return self::batchInsert($users['ids'], $message);
    }

    /**
     * @param Contract $model
     * @param CompanyEntity $companyEntity
     * @return bool
     */
    public static function createdContractManager(Contract $model, CompanyEntity $companyEntity): bool
    {
        $users = self::getManagers('controlContract', $model->company_id);
        $company = Company::find()->where(['id' => $model->company_id])->one();
        $message = 'На підприємство "' . $company->short_name . '" було створено договір "' .
            $model->name . '" під юридичну особу "' . ArrayHelper::getValue($companyEntity, 'short_name') . '"';
        return self::batchInsert($users, $message);
    }

    /**
     * @param Contract $model
     * @return bool
     */
    public static function updatedContract(Contract $model): bool
    {
        $users = self::getUserList('controlContract', $model->company_id);
        $message = 'Договір "' . $model->name . '" був відредагований';
        self::updatedContractManager($model);
        return self::batchInsert($users['ids'], $message);
    }

    /**
     * @param Contract $model
     * @return bool
     */
    public static function updatedContractManager(Contract $model): bool
    {
        $users = self::getManagers('controlContract', $model->company_id);
        $company = Company::find()->where(['id' => $model->company_id])->one();
        $message = 'У підприємства "' . $company->short_name . '" було відредаговано договір "' .
            $model->name . '".';
        return self::batchInsert($users, $message);
    }

    /**
     * @param Contract $model
     * @param string $days
     * @return bool
     */
    public static function soonEndContractCompany(Contract $model, string $days = '1 день'): bool
    {
        $companyDb = Company::find()->where(['=','id',$model->company_id])->one();

        $defaultMail = ['roman@salvigroup.com.ua','sales10@salvigroup.com.ua'];

//        $userForSend = null;
//
//
//        if(!empty($companyDb->manager_email))
//        {
//            $managerEmail = $companyDb->manager_email;
//
//            $manager = User::find()->where(['=','email',$managerEmail])->one();
//
//            if($manager && $manager->status == 20)
//            {
//                $userForSend = $manager;
//            }
//        }
//
//        if(is_null($userForSend))
//        {
//            $userForSend = User::find()->where(['=','email',$defaultMail])->one();
//        }

        foreach ($defaultMail as $mail)
        {
            $userForSend = User::find()->where(['=','email',$mail])->one();

            $message = 'У клієнта '. $companyDb->full_name .' за 7 днів закінчується строк дії договору ' . $model->number;

            self::sendMsgConsole([$userForSend], 'soonEndContractNew', ['message' => $message]);
        }

        return true;
    }

    /**
     * @param Contract $model
     * @param string $days
     * @return bool
     */
    public static function soonEndContract(Contract $model, string $days = '1 день'): bool
    {
        $users = self::getUserList('controlContract', $model->company_id);
        $message = 'Дія договору "' . $model->name . '" закінчується через ' . $days;
        self::sendMsg($users['model'], 'soonEndContract', ['model' => $model, 'days' => $days]);
        self::soonEndContractManager($model, $days);
        return self::batchInsert($users['ids'], $message);
    }

    /**
     * @param Contract $model
     * @param string $days
     * @return bool
     */
    public static function soonEndContractManager(Contract $model, string $days): bool
    {
        $users = self::getManagers('controlContract', $model->company_id);
        $company = Company::find()->where(['id' => $model->company_id])->one();
        $message = 'Дія договору "' . $model->name . '" на підприємстві "' .
            $company->short_name . '" закінчується через ' . $days;
        return self::batchInsert($users, $message);
    }

    /**
     * @param Contract $model
     * @return bool
     */
    public static function endedContract(Contract $model): bool
    {
        $users = self::getUserList('controlContract', $model->company_id);
        $message = 'Строк дії договору "' . $model->name . '" закінчився. 
        Створити нові замовлення за цим договором тепер неможливо.';
        $message .= '<br><a href="https://salvi.ua/unsubscribe-mails" target="_blank">Відписатися від розсилки листів</a>';
        self::sendMsg($users['model'], 'endedContract', ['model' => $model]);
        self::endedContractManager($model);
        return self::batchInsert($users['ids'], $message);
    }

    /**
     * @param Contract $model
     * @return bool
     */
    public static function endedContractManager(Contract $model): bool
    {
        $users = self::getManagers('controlContract', $model->company_id);
        $company = Company::find()->where(['id' => $model->company_id])->one();
        $message = 'Строк дії договору "' . $model->name . '" на підприємстві "' .
            $company->short_name . '" припинив свою дію.';
        return self::batchInsert($users, $message);
    }

    /**
     * @param CompanyEntity $model
     * @return bool
     */
    public static function activatedCompanyEntity(CompanyEntity $model): bool
    {
        $users = self::getUserList('controlCompanyEntity', $model->company_id);
        $message = 'Для Вашого підприємства активовано юридичну особу "' . $model->short_name . '"';
        self::sendMsgDefault($users['model'], __FUNCTION__, ['model' => $model]);
        self::activatedCompanyEntityManager($model);
        return self::batchInsert($users['ids'], $message);
    }

    /**
     * @param CompanyEntity $model
     * @return bool
     */
    public static function activatedCompanyEntityManager(CompanyEntity $model): bool
    {
        $users = self::getManagers('controlCompanyEntity', $model->company_id);
        $company = Company::find()->where(['id' => $model->company_id])->one();
        $message = 'Статус юридичної особи "' . $model->short_name . '" підприємства "' .
            $company->short_name . '" змінено на активний.';
        return self::batchInsert($users, $message);
    }


    /**
     * @param CompanyEntity $model
     * @return bool
     */
    public static function deactivatedCompanyEntity(CompanyEntity $model): bool
    {
        $users = self::getUserList('controlCompanyEntity', $model->company_id);
        $message = 'Юридичну особу "' . $model->short_name . '" деактивовано. Створити нові замовлення на "' .
            $model->short_name . '" неможливо.';
        $message .= '<br><a href="https://salvi.ua/unsubscribe-mails" target="_blank">Відписатися від розсилки листів</a>';
        self::deactivatedCompanyEntityManager($model);
        return self::batchInsert($users['ids'], $message);
    }

    /**
     * @param CompanyEntity $model
     * @return bool
     */
    public static function deactivatedCompanyEntityManager(CompanyEntity $model): bool
    {
        $users = self::getManagers('controlCompanyEntity', $model->company_id);
        $company = Company::find()->where(['id' => $model->company_id])->one();
        $message = 'Статус юридичної особи "' . $model->short_name . '" підприємства "' .
            $company->short_name . '" змінено на неактивний.';
        return self::batchInsert($users, $message);
    }

    /**
     * @param Locations $model
     * @return bool
     * @throws Throwable
     */
    public static function createdLocation(Locations $model): bool
    {
        $users = self::getUserList('controlLocation', $model->company_id);
        $message = 'Локація "' . $model->name . '" створена';
        self::createdLocationManager($model);
        return self::batchInsert($users['ids'], $message);
    }

    /**
     * @param Locations $model
     * @return bool
     * @throws Throwable
     */
    public static function createdLocationManager(Locations $model): bool
    {
        $users = self::getManagers('controlLocation', $model->company_id);
        /** @var  $user User */
        $user = Yii::$app->user->getIdentity();
        $company = Company::find()->where(['id' => $model->company_id])->one();
        $message = 'Користувач ' . $user->first_name . ' ' . $user->last_name . ' підприємства "' .
            $company->short_name . '" створив локацію "' . $model->name . '"';
        return self::batchInsert($users, $message);
    }

    /**
     * @param Locations $model
     * @return bool
     * @throws Throwable
     */
    public static function updatedLocation(Locations $model): bool
    {
        $users = self::getUserList('controlLocation', $model->company_id);
        $message = 'В локацію "' . $model->name . '" було внесено зміни';
        self::updatedLocationManager($model);
        return self::batchInsert($users['ids'], $message);
    }

    /**
     * @param Locations $model
     * @return bool
     * @throws Throwable
     */
    public static function updatedLocationManager(Locations $model): bool
    {
        $users = self::getManagers('controlLocation', $model->company_id);
        /** @var  $user User */
        $user = Yii::$app->user->getIdentity();
        $company = Company::find()->where(['id' => $model->company_id])->one();
        $message = 'Користувач ' . $user->first_name . ' ' . $user->last_name . ' підприємства "' .
            $company->short_name . '" відредагував локацію "' . $model->name . '"';
        return self::batchInsert($users, $message);
    }

    /**
     * @param Locations $model
     * @param int $status
     * @return bool
     * @throws Throwable
     */
    public static function changeStatusLocation(Locations $model, int $status): bool
    {
        $users = self::getUserList('controlLocation', $model->company_id);
        $status = ($status) ? 'активна.' : 'неативна.';
        $message = 'Статус локації "' . $model->name . '" було змінено на ' . $status;
        self::sendMsgDefault($users['model'], __FUNCTION__, ['model' => $model, 'status' => $status]);
        self::changeStatusLocationManager($model, $status);
        return self::batchInsert($users['ids'], $message);
    }

    /**
     * @param Locations $model
     * @param string $status
     * @return bool
     * @throws Throwable
     */
    public static function changeStatusLocationManager(Locations $model, string $status): bool
    {
        $users = self::getManagers('controlLocation', $model->company_id);
        /** @var  $user User */
        $user = Yii::$app->user->getIdentity();
        $company = Company::find()->where(['id' => $model->company_id])->one();
        $message = 'Користувачем ' . $user->first_name . ' ' . $user->last_name . ' підприємства "' .
            $company->short_name . '" було змінено статус локації "' . $model->name . '" на ' . $status;
        return self::batchInsert($users, $message);
    }

    /**
     * @param array $users
     * @return bool
     */
    public static function updateRole(array $users): bool
    {
        $usersModel = User::find()->where(['in', 'id', $users])->all();
        $message = 'Ваші права доступу було відредаговано';
        //self::sendMsgDefault($usersModel, __FUNCTION__, []);
        return self::batchInsert($users, $message);
    }

    /**
     * @param array $users
     * @return bool
     */
    public static function deactivateRole(array $users): bool
    {
        $usersModel = User::find()->where(['in', 'id', $users])->all();
        $message = 'Ваші права доступу було заблоковано.';
        //self::sendMsgDefault($usersModel, __FUNCTION__, []);
        return self::batchInsert($users, $message);
    }

    public static function activateRole(array $users): bool
    {
        $usersModel = User::find()->where(['in', 'id', $users])->all();
        $message = 'Ваші права доступу було активовано.';
        //self::sendMsgDefault($usersModel, __FUNCTION__, []);
        return self::batchInsert($users, $message);
    }

    /**
     * @param int $companyId
     * @return bool
     */
    public static function changeCoordinationOrder(int $companyId): bool
    {
        $users = self::getUserList('controlCoordination', $companyId);
        $message = 'Для Вашого підприємства було змінено алгоритм погоджень. 
        Усі поточні замовлення будуть виконуватися відповідно до старого алгоритму погодження.';
        self::sendMsgDefault($users['model'], __FUNCTION__, []);
        self::changeCoordinationOrderManager($companyId);
        return self::batchInsert($users['ids'], $message);
    }

    /**
     * @param int $companyId
     * @return bool
     */
    public static function changeCoordinationOrderManager(int $companyId): bool
    {
        $users = self::getManagers('controlCoordination', $companyId);
        $company = Company::findOne(['id' => $companyId]);
        $message = 'Для підприємства "' . $company->short_name . '" було змінено алгоритм погодження.';
        return self::batchInsert($users, $message);
    }

    /**
     * @param array $users
     * @return bool
     */
    public static function youAreAtCoordination(array $users): bool
    {
        $usersModel = User::find()->where(['in', 'id', $users])->all();
        $message = 'Вас призначили учасником погодження замовлень';
        self::sendMsgDefault($usersModel, __FUNCTION__, []);
        return self::batchInsert(ArrayHelper::getColumn($usersModel, 'id'), $message);
    }

    /**
     * @param Order $model
     * @return bool
     */
    public static function atManagerOrder(Order $model): bool
    {
        $users = self::getUserList('controlOrder', $model->company_id);
        if (($key = array_search($model->created_by_user, $users)) !== false) {
            unset($users[$key]);
        }
        $message = 'Замовлення №' . $model->id . ' погоджено і воно було відправлено
         на підтвердження менеджеру постачальника.';
        return self::batchInsert($users['ids'], $message);
    }

    /**
     * @param Order $model
     * @return bool
     */
    public static function atManagerOrderOwner(Order $model): bool
    {
        $users = self::getUserList('controlMyOrder', $model->company_id);
        if (in_array($model->created_by_user, $users['ids'])) {
            $users['ids'] = [$model->created_by_user];
            $message = 'Створене Вами замовлення №' . $model->id . ' пройшло погодження й було відправлено 
            менеджеру постачальника на погодження.';
            return self::batchInsert($users['ids'], $message);
        }
        return false;
    }

    public static function atSupplierOrder(Order $model): bool
    {
        $users = self::getUserList('controlOrder', $model->company_id);
        if (($key = array_search($model->created_by_user, $users)) !== false) {
            unset($users[$key]);
        }
        $message = 'Замовлення №' . $model->id . ' підтверджено й відправлено на виконання.';
        self::sendMsgDefault($users['model'], __FUNCTION__, ['model' => $model]);
        return self::batchInsert($users['ids'], $message);
    }

    /**
     * @param Order $model
     * @return bool
     */
    public static function atSupplierOrderOwner(Order $model): bool
    {
        $users = self::getUserList('controlMyOrder', $model->company_id);
        if (in_array($model->created_by_user, $users['ids'])) {
            $users['ids'] = [$model->created_by_user];
            $users['model'] = [$model->createdBy];
            $message = 'Створене Вами замовлення №' . $model->id . ' підтверджено менеджером 
            постачальника й відправлено на виконання.';
            self::sendMsgDefault($users['model'], __FUNCTION__, ['model' => $model]);
            return self::batchInsert($users['ids'], $message);
        }
        return false;
    }

    /**
     * @param Order $model
     * @param User $user
     * @return bool
     */
    public static function declineByManagerOrder(Order $model, User $user): bool
    {
        $users = self::getUserList('controlOrder', $model->company_id);
        if (($key = array_search($model->created_by_user, $users)) !== false) {
            unset($users[$key]);
        }
        $message = 'Замовлення №' . $model->id . ' було відхилено на стадії погодження менеджером ' .
            $user->first_name . ' ' . $user->last_name . '.';
        return self::batchInsert($users['ids'], $message);
    }

    /**
     * @param Order $model
     * @param User $user
     * @return bool
     */
    public static function declineByManagerOrderOwner(Order $model, User $user): bool
    {
        $users = self::getUserList('controlMyOrder', $model->company_id);
        //Yii::error($users);
        if (in_array($model->created_by_user, $users['ids'])) {
            $users['ids'] = [$model->created_by_user];
            $users['model'] = [$model->createdBy];
            $message = 'Замовлення №' . $model->id . ', створене Вами, було відхилено на стадії погодження 
            менеджером ' . $user->first_name . ' ' . $user->last_name . '.';
            self::sendMsgDefault($users['model'], __FUNCTION__, ['model' => $model, 'cUser' => $user]);
            return self::batchInsert($users['ids'], $message);
        }
        return false;
    }

    /**
     * @param Order $model
     * @param User $user
     * @param OrderCoordination $coordination
     * @return bool
     */
    public static function declineByUserOrder(Order $model, User $user, OrderCoordination $coordination): bool
    {
        $users = self::getUserList('controlOrder', $model->company_id);
        if (($key = array_search($model->created_by_user, $users)) !== false) {
            unset($users[$key]);
        }
        $message = 'Замовлення №' . $model->id . ' було відхилено на стадії погодження на шазі ' .
            $coordination->name . ' користувачем ' . $user->first_name . ' ' . $user->last_name . '.';
        return self::batchInsert($users['ids'], $message);
    }

    /**
     * @param Order $model
     * @param User $user
     * @param OrderCoordination $coordination
     * @return bool
     */
    public static function declineByUserOrderOwner(Order $model, User $user, OrderCoordination $coordination): bool
    {
        $users = self::getUserList('controlMyOrder', $model->company_id);
        //Yii::error($users);
        if (in_array($model->created_by_user, $users['ids'])) {
            $users['ids'] = [$model->created_by_user];
            $message = 'Замовлення №' . $model->id . ', створене Вами, було відхилено на стадії погодження на шазі ' .
                $coordination->name . ' користувачем ' . $user->first_name . ' ' . $user->last_name . '.';
            $message .= '<br><a href="https://salvi.ua/unsubscribe-mails" target="_blank">Відписатися від розсилки листів</a>';
            return self::batchInsert($users['ids'], $message);
        }
        return false;
    }

    /**
     * @param Order $model
     * @param User $user
     * @param OrderProduct $product
     * @return bool
     */
    public static function deleteProductFromOrder(Order $model, User $user, OrderProduct $product): bool
    {
        $users = self::getUserList('controlOrder', $model->company_id);
        if (($key = array_search($model->created_by_user, $users)) !== false) {
            unset($users[$key]);
        }
        $message = 'Користувач ' . $user->first_name . ' ' . $user->last_name . ' видалив товар "' .
            $product->name . '" з замовлення №' . $model->id;
        $message .= '<br><a href="https://salvi.ua/unsubscribe-mails" target="_blank">Відписатися від розсилки листів</a>';
        return self::batchInsert($users['ids'], $message);
    }

    /**
     * @param Order $model
     * @param User $user
     * @param OrderProduct $product
     * @return bool
     */
    public static function deleteProductFromOrderOwner(Order $model, User $user, OrderProduct $product): bool
    {
        $users = self::getUserList('controlMyOrder', $model->company_id);
        if (in_array($model->created_by_user, $users['ids'])) {
            $users['ids'] = [$model->created_by_user];
            $message = 'Користувач ' . $user->first_name . ' ' . $user->last_name . ' видалив товар "' .
                $product->name . '" з замовлення №' . $model->id . ' створеного Вами.';
	        $message .= '<br><a href="https://salvi.ua/unsubscribe-mails" target="_blank">Відписатися від розсилки листів</a>';
            return self::batchInsert($users['ids'], $message);
        }
        return false;
    }

    /**
     * @param Order $model
     * @param User $user
     * @return bool
     */
    public static function commentToOrder(Order $model, User $user): bool
    {
        $users = self::getUserList('controlOrder', $model->company_id);
        if (($key = array_search($model->created_by_user, $users)) !== false) {
            unset($users[$key]);
        }
        $message = 'Користувач ' . $user->first_name . ' ' . $user->last_name . ' залишив коментар до 
        замовлення №' . $model->id;
	    $message .= '<br><a href="https://salvi.ua/unsubscribe-mails" target="_blank">Відписатися від розсилки листів</a>';
        self::sendMsgDefault($users['model'], __FUNCTION__, ['model' => $model, 'cUser' => $user]);
        return self::batchInsert($users['ids'], $message);
    }

    /**
     * @param Order $model
     * @param User $user
     * @return bool
     */
    public static function commentToOrderOwner(Order $model, User $user): bool
    {
        $users = self::getUserList('controlMyOrder', $model->company_id);
        if (in_array($model->created_by_user, $users['ids'])) {
            $users['ids'] = [$model->created_by_user];
            $message = 'Користувач ' . $user->first_name . ' ' . $user->last_name . ' залишив коментар до 
            замовлення №' . $model->id . ' створеного Вами.';
	        $message .= '<br><a href="https://salvi.ua/unsubscribe-mails" target="_blank">Відписатися від розсилки листів</a>';
            return self::batchInsert($users['ids'], $message);
        }
        return false;
    }

    /**
     * @param Order $model
     * @param User $user
     * @param array $products
     * @return bool
     */
    public static function addProductsToOrder(Order $model, User $user, array $products): bool
    {
        $users = self::getUserList('controlOrder', $model->company_id);
        if (($key = array_search($model->created_by_user, $users)) !== false) {
            unset($users[$key]);
        }
        if ($count = count($products) > 1) {
            $end = ' товары: ';
            foreach ($products as $k => $product) {
                if ($k == ($count - 1)) {
                    $end .= '"' . $product . '", ';
                } else {
                    $end .= '"' . $product . '".';
                }
            }
        } else {
            $end = ' товар: "' . $products[0] . '".';
        }
        $message = 'Користувач ' . $user->first_name . ' ' . $user->last_name . ' додав до 
        замовлення №' . $model->id . $end;
	    $message .= '<br><a href="https://salvi.ua/unsubscribe-mails" target="_blank">Відписатися від розсилки листів</a>';
        return self::batchInsert($users['ids'], $message);
    }

    /**
     * @param Order $model
     * @param User $user
     * @param array $products
     * @return bool
     */
    public static function addProductsToOrderOwner(Order $model, User $user, array $products): bool
    {
        $users = self::getUserList('controlMyOrder', $model->company_id);
        if (in_array($model->created_by_user, $users['ids'])) {
            $users['ids'] = [$model->created_by_user];
            if ($count = count($products) > 1) {
                $end = ' товары: ';
                foreach ($products as $k => $product) {
                    if ($k == ($count - 1)) {
                        $end .= '"' . $product . '", ';
                    } else {
                        $end .= '"' . $product . '".';
                    }
                }
            } else {
                $end = ' товар: "' . $products[0] . '".';
            }
            $message = 'Користувач ' . $user->first_name . ' ' . $user->last_name . ' додав до створеного Вами 
            замовлення №' . $model->id . $end;
	        $message .= '<br><a href="https://salvi.ua/unsubscribe-mails" target="_blank">Відписатися від розсилки листів</a>';
            return self::batchInsert($users['ids'], $message);
        }
        return false;
    }

    /**
     * @param Order $model
     * @param User $user
     * @param string $filename
     * @return bool
     */
    public static function addFileToOrder(Order $model, User $user, string $filename): bool
    {
        $users = self::getUserList('controlOrder', $model->company_id);
        if (($key = array_search($model->created_by_user, $users)) !== false) {
            unset($users[$key]);
        }
        $message = 'Користувач ' . $user->first_name . ' ' . $user->last_name . ' прикріпив файл "' .
            $filename . '" до замовлення №' . $model->id;
	    $message .= '<br><a href="https://salvi.ua/unsubscribe-mails" target="_blank">Відписатися від розсилки листів</a>';
        return self::batchInsert($users['ids'], $message);
    }

    /**
     * @param Order $model
     * @param User $user
     * @param string $filename
     * @return bool
     */
    public static function addFileToOrderOwner(Order $model, User $user, string $filename): bool
    {
        $users = self::getUserList('controlMyOrder', $model->company_id);
        if (in_array($model->created_by_user, $users['ids'])) {
            $users['ids'] = [$model->created_by_user];
            $message = 'Користувач ' . $user->first_name . ' ' . $user->last_name . ' прикріпив файл "' .
                $filename . '" до замовлення №' . $model->id . ' створеного Вами.';
	        $message .= '<br><a href="https://salvi.ua/unsubscribe-mails" target="_blank">Відписатися від розсилки листів</a>';
            return self::batchInsert($users['ids'], $message);
        }
        return false;
    }

    /**
     * @param Order $model
     * @param User $user
     * @param string $filename
     * @return bool
     */
    public static function deleteFileFromOrder(Order $model, User $user, string $filename): bool
    {
        $users = self::getUserList('controlOrder', $model->company_id);
        if (($key = array_search($model->created_by_user, $users)) !== false) {
            unset($users[$key]);
        }
        $message = 'Користувач ' . $user->first_name . ' ' . $user->last_name . ' видалив файл "' .
            $filename . '" з замовлення №' . $model->id;
	    $message .= '<br><a href="https://salvi.ua/unsubscribe-mails" target="_blank">Відписатися від розсилки листів</a>';
        return self::batchInsert($users['ids'], $message);
    }

    /**
     * @param Order $model
     * @param User $user
     * @param string $filename
     * @return bool
     */
    public static function deleteFileFromOrderOwner(Order $model, User $user, string $filename): bool
    {
        $users = self::getUserList('controlMyOrder', $model->company_id);
        if (in_array($model->created_by_user, $users['ids'])) {
            $users['ids'] = [$model->created_by_user];
            $message = 'Користувач ' . $user->first_name . ' ' . $user->last_name . ' видалив файл "' .
                $filename . '" з замовлення №' . $model->id . ' створеного Вами.';
	        $message .= '<br><a href="https://salvi.ua/unsubscribe-mails" target="_blank">Відписатися від розсилки листів</a>';
            return self::batchInsert($users['ids'], $message);
        }
        return false;
    }

    /**
     * @param Budget $model
     * @return bool
     */
    public static function soonEndBudget(Budget $model): bool
    {
        $users = self::getUserList('controlBudget', $model->company_id);
        if (($key = array_search($model->user_id_responsible, $users)) !== false) {
            unset($users[$key]);
        }
        $message = 'Бюджет "' . $model->name . '" використано більш ніж на 80%';
	    $message .= '<br><a href="https://salvi.ua/unsubscribe-mails" target="_blank">Відписатися від розсилки листів</a>';
        self::sendMsgDefault($users['model'], __FUNCTION__, ['model' => $model]);
        self::soonEndBudgetManager($model);
        return self::batchInsert($users['ids'], $message);
    }

    /**
     * @param Budget $model
     * @return bool
     */
    public static function soonEndBudgetOwner(Budget $model): bool
    {
        $users = self::getUserList('controlMyBudget', $model->company_id);
        if (in_array($model->user_id_responsible, $users['ids'])) {
            $users['ids'] = [$model->user_id_responsible];
            $users['model'] = [$model->responsibleUser];
            $message = 'Бюджет "' . $model->name . '", створений Вами, використано більш ніж на 80%';
	        $message .= '<br><a href="https://salvi.ua/unsubscribe-mails" target="_blank">Відписатися від розсилки листів</a>';
            self::sendMsgDefault($users['model'], __FUNCTION__, ['model' => $model]);
            return self::batchInsert($users['ids'], $message);
        }
        return false;
    }

    /**
     * @param int $companyId
     * @return bool
     */
    public static function changeCatalog(int $companyId): bool
    {
        $users = self::getUserList('controlCatalog', $companyId);
        self::changeCatalogManager($companyId);
        $message = 'Для Вашого підприємства був змінений список товарів.';
	    $message .= '<br><a href="https://salvi.ua/unsubscribe-mails" target="_blank">Відписатися від розсилки листів</a>';
        return self::batchInsert($users['ids'], $message);
    }

    /**
     * @param int $companyId
     * @return bool
     */
    public static function changeCatalogManager(int $companyId): bool
    {
        $users = self::getManagers('controlCatalog', $companyId);
        $company = Company::findOne(['id' => $companyId]);
        $message = 'Для підприємства "' . $company->short_name . '" був змінений список товарів.';
	    $message .= '<br><a href="https://salvi.ua/unsubscribe-mails" target="_blank">Відписатися від розсилки листів</a>';
        return self::batchInsert($users, $message);
    }

    /**
     * @param Company $model
     * @param User $user
     * @return bool
     */
    public static function registerCompany(Company $model, User $user): bool
    {
        $users = self::getManagers('controlCompany', $model->id);
        $message = 'Користувачем ' . $user->first_name . '' . $user->last_name .
            ' створена заявка на реєстрацію підприємства "' . $model->short_name . '"';
        return self::batchInsert($users, $message);
    }

    /**
     * @param User $model
     * @return bool
     */
    public static function createUserManager(User $model): bool
    {
        $users = self::getManagers('controlUser', $model->company_id);
        $company = Company::find()->where(['id' => $model->company_id])->one();
        $message = 'Користувачеві ' . $model->first_name . ' ' . $model->last_name . ' з підприємства "' .
            $company->short_name . '" було створено аккаунт.';
        return self::batchInsert($users, $message);
    }

    /**
     * @param User $model
     * @param int $status
     * @return bool
     */
    public static function changeStatusUserManager(User $model, int $status): bool
    {
        $users = self::getManagers('controlUser', $model->company_id);
        $company = Company::find()->where(['id' => $model->company_id])->one();
        $status = ($status) ? 'активний.' : 'не активний.';
        if ($model->manager) {
            $message = 'Менеджера ' . $model->first_name . ' ' . $model->last_name .
                ' було переведено в статус ' . $status;
        } else {
            $message = 'Користувача ' . $model->first_name . ' ' . $model->last_name . ' з підприємства "' .
                $company->short_name . '" було переведено в статус ' . $status;
        }
        return self::batchInsert($users, $message);
    }

    /**
     * @param string $name
     * @return bool
     * @throws Throwable
     */
    public static function changeManagerRoleManager(string $name, array $userIds): bool
    {
        $users = self::getManagers('controlGeneralRole', 0, false);
        $message = 'Роль "' . $name . '" менеджера була відредагована.';
        $result = array_diff($users, $userIds);
        return self::batchInsert($result, $message);
    }

    /**
     * @param string $name
     * @param int $status
     * @param array $userIds
     * @return bool
     * @throws Throwable
     */
    public static function changeStatusManagerRoleManager(string $name, int $status, array $userIds): bool
    {
        $users = self::getManagers('controlGeneralRole', 0, false);
        $status = ($status) ? 'активовано.' : 'деактивовано.';
        $message = 'Роль "' . $name . '" менеджера було ' . $status;
        $result = array_diff($users, $userIds);
        return self::batchInsert($result, $message);
    }

    /**
     * @param string $name
     * @param int $company_id
     * @return bool
     */
    public static function changeUserRoleManager(string $name, int $company_id): bool
    {
        $users = self::getManagers('controlUserRole', $company_id);
        $company = Company::findOne(['id' => $company_id]);
        $message = 'Роль "' . $name . '" підприємства "' . $company->short_name . '" була відредагована.';
        return self::batchInsert($users, $message);
    }

    /**
     * @param string $name
     * @param int $status
     * @param int $company_id
     * @return bool
     */
    public static function changeStatusUserRoleManager(string $name, int $status, int $company_id): bool
    {
        $users = self::getManagers('controlUserRole', $company_id);
        $company = Company::findOne(['id' => $company_id]);
        $status = ($status) ? 'активовано.' : 'деактивовано.';
        $message = 'Роль "' . $name . '" підприємства "' . $company->short_name . '" було ' . $status;
        return self::batchInsert($users, $message);
    }

    /**
     * @param Order $model
     * @return bool
     */
    public static function onApproveOrderManager(Order $model): bool
    {
        $users = self::getManagers('controlOrder', $model->company_id, true, $model->location_id);
        $company = Company::findOne(['id' => $model->company_id]);
        $message = 'У підприємства "' . $company->short_name . '" була створена заявка на погодження.';
        return self::batchInsert($users, $message);
    }

    /**
     * @param Order $model
     * @return bool
     */
    public static function atManagerOrderManager(Order $model): bool
    {
        $users = self::getManagers('controlOrder', $model->company_id);
        $company = Company::findOne(['id' => $model->company_id]);
        $message = 'У підприємства "' . $company->short_name . '" була повністю погоджена заявка і 
        переведена в замовлення №' . $model->id . '.';
        return self::batchInsert($users, $message);
    }

    /**
     * @param Budget $model
     * @return bool
     */
    public static function soonEndBudgetManager(Budget $model): bool
    {
        $users = self::getManagers('controlBudget', $model->company_id);
        $company = Company::findOne(['id' => $model->company_id]);
        $message = 'Бюджет "' . $model->name . '" підприємства "' .
            $company->short_name . '" використано більш ніж на 80%';
        return self::batchInsert($users, $message);
    }

    /**
     * @param Contract $model
     * @return bool
     */
    public static function changeDateContract(Contract $model): bool
    {
        $users = self::getUserList('controlContract', $model->company_id);
        $message = 'Дію договора "' . $model->name . '" змінено до ' . date('d.m.Y', $model->date_end);
        self::sendMsgDefault($users['model'], __FUNCTION__, ['model' => $model]);
        self::changeDateContractManager($model);
        return self::batchInsert($users['ids'], $message);
    }

    /**
     * @param Contract $model
     * @return bool
     */
    public static function changeDateContractManager(Contract $model): bool
    {
        $users = self::getManagers('controlBudget', $model->company_id);
        $company = Company::findOne(['id' => $model->company_id]);
        $message = 'Дію договора "' . $model->name . '" підприємства "' .
            $company->short_name . '" змінено до ' . date('d.m.Y', $model->date_end);
        return self::batchInsert($users, $message);
    }

    /**
     * @param Contract $model
     * @param array $locations
     * @return bool
     */
    public static function changeContractLocation(Contract $model, array $locations): bool
    {
        $users = self::getUserList('controlContract', $model->company_id);
        $locations = Locations::find()->where(['in', 'id', $locations])->all();
        if ($locations) {
            foreach ($locations as $location) {
                $message = 'У локації "' . $location->name . '" було змінено договір.';
                self::batchInsert($users['ids'], $message);
            }
        }
        return true;
    }

    /**
     * @param int $location_id
     * @param array $costs
     * @return bool
     */
    public static function deletedCostCenter(int $location_id, array $costs): bool
    {
        $location = Locations::findOne(['id' => $location_id]);
        $users = self::getUserList('controlLocation', $location->company_id);
        if ($costs) {
            foreach ($costs as $cost) {
                /** @var  $cost CostCenter */
                $message = 'Кост-центр "' . $cost->name . '" було видалено з локації "' . $location->name . '".';
                self::batchInsert($users['ids'], $message);
            }
        }
        return true;
    }

    /**
     * @param Contract $model
     * @param array $costs
     * @return bool
     */
    public static function changeContractCostCenter(Contract $model, array $costs): bool
    {
        $users = self::getUserList('controlContract', $model->company_id);
        $costs = CostCenter::find()->where(['in', 'id', $costs])->all();
        if ($costs) {
            foreach ($costs as $cost) {
                $message = 'Договір для кост-центру "' . $cost->name . '" було відредаговано. 
                Оформлювати нові замовлення за даним договором неможливо.';
                self::batchInsert($users['ids'], $message);
            }
        }
        return true;
    }

    /**
     * @param OrderCoordination $model
     * @param array $listUsers
     * @return bool
     * @throws Throwable
     */
    public static function coordinationOrder(OrderCoordination $model, array $listUsers): bool
    {
        $users = self::getUserList('controlMyCoordination', $model->company_id);
        $allUsers = [];
        if (!empty($listUsers)) {
            $allUsers = User::find()
                ->where(['in', 'id', $users['ids']])
                ->andWhere(['in', 'id', ArrayHelper::getColumn($listUsers, 'id')])
                ->all();
            if ($allUsers) {
                $allUsers = ArrayHelper::getColumn($allUsers, 'id');
            }
        }
        $message = 'Вам або другим участникам необхідно погодити замовлення №' . $model->order_id;
        return self::batchInsert($allUsers, $message);
    }

    public static function coordinationOrderWithoutRbac(int $orderId, $listUsers)
    {
        return self::sendMsg(
            $listUsers,
            'coordinationNeedApprove',
            ['order_id' => $orderId]
        );
    }

    /**
     * @param Coordination $model
     * @return bool
     * @throws Throwable
     */
    public static function coordinationNoActiveUserOrder(Coordination $model): bool
    {
        $users = self::getUserList('controlCoordination', $model->company_id);
        $message = 'Статус користувачів, які відносились до кроку "' .
            $model->name . '" погодження, було змінено на неактивні. Даний крок погодження буде пропущений 
            в загальному алгоритмі погоджень.';
        return self::batchInsert($users['ids'], $message);
    }

    public static function createdBudget(Budget $model, array $locations, array $costs): bool
    {
        $users = self::getUserList('controlBudget', $model->company_id);
        $locations = Locations::find()->where(['in', 'id', $locations])->all();
        if (!$model->all_cost) {
            $costs = CostCenter::find()->where(['in', 'id', $costs])->all();
        }
        if ($locations) {
            if (count($locations) > 1) {
                $text = 'локації: ';
                foreach ($locations as $k => $location) {
                    if ($k) {
                        $text .= ', ' . $location->name;
                    } else {
                        $text .= $location->name;
                    }
                }
            } else {
                $text = 'локацію ' . ArrayHelper::getValue($locations, '0.name');
            }
            if ($model->all_cost) {
                $costText = ' і всі кост-центри';
            } else {
                if ($costs) {
                    if (count($costs) > 1) {
                        $costText = 'кост-центри: ';
                        foreach ($costs as $k => $cost) {
                            if ($k) {
                                $costText .= ', ' . $cost->name;
                            } else {
                                $costText .= $cost->name;
                            }
                        }
                    } else {
                        $costText = 'кост-центр ' . ArrayHelper::getValue($costs, '0.name');
                    }
                } else {
                    $costText = '';
                }
            }
            $message = 'На ' . $text . ' ' . $costText . ' було створено бюджет "' . $model->name
                . '" для Вашого підприємства';
            self::sendMsgDefault($users['model'], __FUNCTION__, [
                'model'     => $model,
                'costs'     => $costs,
                'locations' => $locations
            ]);
            self::createdBudgetManager($model, $text, $costText);
            self::createdBudgetOwnLocation($model, $locations, $costs);
            return self::batchInsert($users['ids'], $message);
        }
        return false;
    }

    public static function createdBudgetOwnLocation(Budget $model, array $locations, array $costs): bool
    {
        $users = self::getUserList('controlMyBudget', $model->company_id);
        $locations = Locations::find()
            ->where(['in', 'id', $locations])
            ->andWhere(['in', 'user_id_responsible', $users['ids']])
            ->all();
        if ($locations) {
            foreach ($locations as $location) {
                $users['ids'] = [$location->user_id_responsible];
                $message = 'На Вашу локацію ' . $location->name . ' було створено бюджет ' . $model->name;
                self::batchInsert($users['ids'], $message);
                if ($users['model']) {
                    foreach ($users['model'] as $user) {
                        $users['model'] = false;
                        if ($location->user_id_responsible == $user->id) {
                            $users['model'][] = $user;
                        }
                    }
                    self::sendMsgDefault($users['model'], __FUNCTION__, [
                        'model'    => $model,
                        'location' => $location
                    ]);
                }
            }
            return true;
        }
        return false;
    }

    public static function changeUserManager(User $model): bool
    {
        $users = self::getManagers('controlUserRole', $model->company_id);
        $company = Company::findOne(['id' => $model->company_id]);
        $message = 'Користувачу "' . $model->first_name . ' ' . $model->last_name . '" підприємства 
        "' . $company->short_name . '" було змінено права доступу через зміну ролі.';
        return self::batchInsert($users, $message);
    }

    /**
     * @param Specification $model
     * @return bool
     */
    public static function changeSpecificationStatusManager(Specification $model): bool
    {
        $users = self::getManagers('controlSpecification', $model->company_id);
        if ($model->status) {
            $status = 'активовано';
        } else {
            $status = 'деактивовано';
        }
        $company = Company::findOne(['id' => $model->company_id]);
        $message = 'Для підприємства "' . $company->short_name . '" було ' . $status . ' спецификацію "' .
            $model->name . '".';
        return self::batchInsert($users, $message);
    }

    public static function createdBudgetManager(Budget $model, string $location, string $cost)
    {
        $users = self::getManagers('controlBudget', $model->company_id);
        $company = Company::findOne(['id' => $model->company_id]);
        $message = 'На ' . $location . ' ' . $cost . ' було створено бюджет "' . $model->name
            . '" для підприємства "' . $company->short_name . '".';
        return self::batchInsert($users, $message);
    }

    public static function orderMoreThanBudgetManager(Order $model)
    {
        $users = self::getManagers('controlOrder', $model->company_id);
        $company = Company::findOne(['id' => $model->company_id]);
        $message = 'У підприємства "' . $company->short_name . '" була створена заявка №' . $model->id
            . ' на погодження, що перевищує бюджет.';
        return self::batchInsert($users, $message);
    }

    /**
     * @param Order $model
     * @return bool
     * @throws Throwable
     */
    public static function orderNeedModerationManager(Order $model)
    {
        $users = self::getManagers('controlOrder', $model->company_id);
        $company = Company::findOne(['id' => $model->company_id]);
        $message = 'Користувач ' . $model->createdBy->first_name . ' ' . $model->createdBy->last_name .
            ' підприємства "' . $company->short_name . '" створив замовлення №' .
            $model->id . 'з недоступними товарами. Необхідно провести модерацію';
        return self::batchInsert($users, $message);
    }

    public static function statusModeratedOrderOwner(Order $model): bool
    {
        $users = self::getUserList('controlMyOrder', $model->company_id);
        if (in_array($model->created_by_user, $users['ids'])) {
            $users['ids'] = [$model->created_by_user];
            $message = 'Створене Вами замовлення №' . $model->id . ' пройшло модерацію.
             Перевірте і запустіть на узгодження замовленн.';
            return self::batchInsert($users['ids'], $message);
        }
        return false;
    }

    public static function statusModeratedOrder(Order $model): bool
    {
        $users = self::getUserList('controlOrder', $model->company_id);
        if (($key = array_search($model->created_by_user, $users['ids'])) !== false) {
            unset($users[$key]);
        }
        $message = 'Замовлення №' . $model->id . ' пройшло модерацію.
             Перевірте і запустіть на узгодження замовлення.';
        return self::batchInsert($users['ids'], $message);
    }

    public static function statusRejectedOrderOwner(Order $model): bool
    {
        $users = self::getUserList('controlMyOrder', $model->company_id);
        if (in_array($model->created_by_user, $users['ids'])) {
            $users['ids'] = [$model->created_by_user];
            $message = 'Ваше замовлення №' . $model->id . ' з запитом на доступ до товарів під локацію "' .
                $model->location->name . '" був відхилений модератором.';
            return self::batchInsert($users['ids'], $message);
        }
        return false;
    }

    public static function statusRejectedOrder(Order $model): bool
    {
        $users = self::getUserList('controlOrder', $model->company_id);
        if (($key = array_search($model->created_by_user, $users['ids'])) !== false) {
            unset($users[$key]);
        }
        $message = 'Замовлення №' . $model->id . ' з запитом на доступ до товарів під локацію "' .
            $model->location->name . '" був відхилений модератором.';
        return self::batchInsert($users['ids'], $message);
    }

    public static function atManagerOrderChanged(Order $model, $products): bool
    {
        $users = self::getUserList('controlOrder', $model->company_id);
        if (($key = array_search($model->created_by_user, $users['ids'])) !== false) {
            unset($users['ids'][$key]);
        }
        $message = 'Замовлення №' . $model->id . ' було відредаговано під час узгодження. Товари:';
        $productMessage = '';
        foreach ($products as $product) {

            /** @var $product OrderProduct */
            $type = 'додано';
            $quantity = abs($product->on_approve_quantity - $product->quantity);
            if ($product->on_approve_quantity > $product->quantity) {
                $type = 'видалено';
            }

            if(!is_null($product->product))
            {
                $productMessage .= ' ' . $product->product->name . " - " . $type . ' ' .
                    $quantity . ' ' . $product->product->unit->name;
            }
            else
            {
                $productMessage .= ' ' . $product->name . " - " . $type . ' ' .
                    $quantity;
            }
        }
        $message .= $productMessage . ' Загальна сума замовлення стала ' . $model->total_sum . ' грн';
        self::atManagerOrderChangedOwner($model, $productMessage);
        return self::batchInsert($users['ids'], $message);
    }

    public static function atManagerOrderChangedOwner(Order $model, $text): bool
    {
        $users = self::getUserList('controlMyOrder', $model->company_id);
        if (in_array($model->created_by_user, $users['ids'])) {
            $users['ids'] = [$model->created_by_user];
            $message = 'Ваше замовлення №' . $model->id . ' було відредаговано під час узгодження. Товари:';
            $message .= $text . ' Загальна сума замовлення стала ' . $model->total_sum . ' грн';
            return self::batchInsert($users['ids'], $message);
        }
        return false;
    }
}
