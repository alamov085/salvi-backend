<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 18.06.2019
 * Time: 10:18
 */

namespace console\controllers;

use common\models\AuthItem;
use common\models\AuthItemParent;
use common\models\User;
use Yii;
use yii\console\Controller;

/**
 * Class RbacController
 * @package console\controllers
 */
class RbacController extends Controller
{
    public function actionInit()
    {
        $auth = Yii::$app->authManager;
        Yii::$app->db->createCommand('SET foreign_key_checks = 0;')->execute();
        Yii::$app->db->createCommand()->truncateTable('auth_assignment')->execute();
        Yii::$app->db->createCommand()->truncateTable('auth_item')->execute();
        Yii::$app->db->createCommand()->truncateTable('auth_item_child')->execute();
        Yii::$app->db->createCommand()->truncateTable('auth_item_parent')->execute();
        Yii::$app->db->createCommand('SET foreign_key_checks = 1;')->execute();

        /**
         * 1) доска
         * - просмотр заказов
         * - просмотр открытой статистики
         * - просмотр закрытой статистики
         */
        $dashboard = AuthItemParent::addGroup('dashboard', 'Дошка');

        $viewDashboardOrder = $auth->createPermission('viewDashboardOrder');
        $viewDashboardOrder->description = 'Перегляд замовлень';
        $auth->add($viewDashboardOrder, 0, $dashboard);

        $viewDashboardOpenStatistic = $auth->createPermission('viewDashboardOpenStatistic');
        $viewDashboardOpenStatistic->description = 'Перегляд відкритої статистики';
        $auth->add($viewDashboardOpenStatistic, 0, $dashboard);

        $viewDashboardCloseStatistic = $auth->createPermission('viewDashboardCloseStatistic');
        $viewDashboardCloseStatistic->description = 'Перегляд закритої статистики';
        $auth->add($viewDashboardCloseStatistic, 0, $dashboard);

        /**
         * 2) основная информация
         * - просмотр основной информации
         * - редактирование основной информации
         */
        $profile = AuthItemParent::addGroup('profile', 'Основна інформація');

        $updateProfileAddFields = $auth->createPermission('updateProfileAddFields');
        $updateProfileAddFields->description = 'Редагування спец. полів профілю';
        $auth->add($updateProfileAddFields, 0, $profile);

        /**
         * 3) договоры
         * - просмотр списка договоров (доступ к разделу)
         * - активация/деактивация договоров
         * - создание договора
         * - редактирование договоров
         * - просмотр карточки договора
         */
        $contract = AuthItemParent::addGroup('contract', 'Договори');

        $viewContract = $auth->createPermission('viewContract');
        $viewContract->description = 'Перегляд списку договорів (доступ до розділу)';
        $auth->add($viewContract, 0, $contract);

        $statusContract = $auth->createPermission('statusContract');
        $statusContract->description = 'Активація / деактивація договорів';
        $auth->add($statusContract, 0, $contract);

        $createContract = $auth->createPermission('createContract');
        $createContract->description = 'Створення договорів';
        $auth->add($createContract, 0, $contract);

        $updateContract = $auth->createPermission('updateContract');
        $updateContract->description = 'Редагування договорів';
        $auth->add($updateContract, 0, $contract);

        $viewOnlyContract = $auth->createPermission('viewOnlyContract');
        $viewOnlyContract->description = 'Перегляд картки договору';
        $auth->add($viewOnlyContract, 0, $contract);

        /**
         * 4) справочник юридических лиц
         * - просмотр списка юр лиц (доступ к разделу)
         * - активация / деактивация конкретного юридического лица
         * - просмотр деталей конкретного юридического лица
         * - редактирование конкретного юридического лица
         * - создание юридического лица
         */
        $companyEntityModel = AuthItemParent::addGroup('companyEntity', 'Довідник юридичних осіб');

        $viewCompanyEntity = $auth->createPermission('viewCompanyEntity');
        $viewCompanyEntity->description = 'Перегляд списку юр. осіб (доступ до розділу)';
        $auth->add($viewCompanyEntity, 0, $companyEntityModel);

        $statusCompanyEntity = $auth->createPermission('statusCompanyEntity');
        $statusCompanyEntity->description = 'Активація / деактивація юр. особи';
        $auth->add($statusCompanyEntity, 0, $companyEntityModel);

        $viewOnlyCompanyEntity = $auth->createPermission('viewOnlyCompanyEntity');
        $viewOnlyCompanyEntity->description = 'Перегляд деталей юр. особи';
        $auth->add($viewOnlyCompanyEntity, 0, $companyEntityModel);

        $updateCompanyEntity = $auth->createPermission('updateCompanyEntity');
        $updateCompanyEntity->description = 'Редагування юр. особи';
        $auth->add($updateCompanyEntity, 0, $companyEntityModel);

        $createCompanyEntity = $auth->createPermission('createCompanyEntity');
        $createCompanyEntity->description = 'Створення юр. особи';
        $auth->add($createCompanyEntity, 0, $companyEntityModel);

        /**
         * 5) Наценки
         * - просмотр списка наценок (доступ к разделу)
         * - создание наценки (ограничений)
         * - редактирование наценок (ограничений)
         * - просмотр деталей наценок (органичений)
         */
        $catalogLimitation = AuthItemParent::addGroup('catalogLimitation', 'Націнки');

        $viewLimitation = $auth->createPermission('viewLimitation');
        $viewLimitation->description = 'Перегляд списку націнок (доступ до розділу)';
        $auth->add($viewLimitation, 0, $catalogLimitation);

        $catalogLimitationCreate = $auth->createPermission('catalogLimitationCreate');
        $catalogLimitationCreate->description = 'Створення націнки (обмежень)';
        $auth->add($catalogLimitationCreate, 0, $catalogLimitation);

        $catalogLimitationUpdate = $auth->createPermission('catalogLimitationUpdate');
        $catalogLimitationUpdate->description = 'Редагування націнок (обмежень)';
        $auth->add($catalogLimitationUpdate, 0, $catalogLimitation);

        $catalogLimitationViewDetail = $auth->createPermission('catalogLimitationViewDetail');
        $catalogLimitationViewDetail->description = 'Перегляд деталей націнок (органичений)';
        $auth->add($catalogLimitationViewDetail, 0, $catalogLimitation);

        /**
         * 6) Спецификации
         * - просмотр списка спецификаций (доступ к разделу)
         * - создание спецификаций (первичное добавление товаров в спецификацию)
         * - изменение настроек видимости каталога номенклатуры
         * - просмотр карточки спецификации
         * - редактирование карточки спецификации (включая добавление / удаление товаров; без учета изменения статуса)
         * - изменение статуса спецификации
         */
        $specification = AuthItemParent::addGroup('specification', 'Специфікації');

        $viewSpecification = $auth->createPermission('viewSpecification');
        $viewSpecification->description = 'Перегляд списку специфікацій (доступ до розділу)';
        $auth->add($viewSpecification, 0, $specification);

        $createSpecification = $auth->createPermission('createSpecification');
        $createSpecification->description = 'Створення специфікацій';
        $auth->add($createSpecification, 0, $specification);

        $viewSpecificationDetail = $auth->createPermission('viewSpecificationDetail');
        $viewSpecificationDetail->description = 'Перегляд картки специфікації';
        $auth->add($viewSpecificationDetail, 0, $specification);

        $updateSpecification = $auth->createPermission('updateSpecification');
        $updateSpecification->description = 'Редагування картки специфікації';
        $auth->add($updateSpecification, 0, $specification);

        $changeStatusSpecification = $auth->createPermission('changeStatusSpecification');
        $changeStatusSpecification->description = 'Зміна статусу специфікації';
        $auth->add($changeStatusSpecification, 0, $specification);

        /**
         * 7) Локации:
         * - просмотр списка локаций (доступ к разделу)
         * - создание локации
         * - активация/деактивация локации
         * - редактирование локации
         * - просмотр локации
         */
        $locationModel = AuthItemParent::addGroup('location', 'Локації');

        $viewLocations = $auth->createPermission('viewLocations');
        $viewLocations->description = 'Перегляд списку локацій (доступ до розділу)';
        $auth->add($viewLocations, 0, $locationModel);

        $createLocations = $auth->createPermission('createLocations');
        $createLocations->description = 'Створення локації';
        $auth->add($createLocations, 0, $locationModel);

        $statusLocations = $auth->createPermission('statusLocations');
        $statusLocations->description = 'Активація / деактивація локації';
        $auth->add($statusLocations, 0, $locationModel);

        $updateLocations = $auth->createPermission('updateLocations');
        $updateLocations->description = 'Редагування локації';
        $auth->add($updateLocations, 0, $locationModel);

        $viewOnlyLocations = $auth->createPermission('viewOnlyLocations');
        $viewOnlyLocations->description = 'Перегляд локації';
        $auth->add($viewOnlyLocations, 0, $locationModel);

        /**
         * 8) дерево локаций
         * - просмотр дерева локаций (доступ к разделу)
         */
        $locationTreeModel = AuthItemParent::addGroup('locationTree', 'Дерево локацій');

        $viewLocationTree = $auth->createPermission('viewLocationTree');
        $viewLocationTree->description = 'Перегляд дерева локацій (доступ до розділу)';
        $auth->add($viewLocationTree, 0, $locationTreeModel);

        /**
         * 9) Кост-центры:
         * - просмотр списка кост-центров (доступ к разделу)
         * - редактирование кост-центра
         * - создание кост-центров
         */
        $costCenterModel = AuthItemParent::addGroup('costCenter', 'Кост-центри');

        $viewCostCenter = $auth->createPermission('viewCostCenter');
        $viewCostCenter->description = 'Перегляд списку кост-центрів (доступ до розділу)';
        $auth->add($viewCostCenter, 0, $costCenterModel);

        $updateCostCenter = $auth->createPermission('updateCostCenter');
        $updateCostCenter->description = 'Редагування кост-центру';
        $auth->add($updateCostCenter, 0, $costCenterModel);

        $createCostCenter = $auth->createPermission('createCostCenter');
        $createCostCenter->description = 'Створення кост-центрів';
        $auth->add($createCostCenter, 0, $costCenterModel);

        /**
         * 10) Список пользователей
         * - просмотр списка пользователей (доступ к разделу)
         * - создание пользователя
         * - редактирование пользователя
         * - активация/деактивация пользователя
         */
        $userModel = AuthItemParent::addGroup('user', 'Список користувачів');

        $viewUser = $auth->createPermission('viewUser');
        $viewUser->description = 'Перегляд списку користувачів (доступ до розділу)';
        $auth->add($viewUser, 0, $userModel);

        $createUser = $auth->createPermission('createUser');
        $createUser->description = 'Створення користувача';
        $auth->add($createUser, 0, $userModel);

        $updateUser = $auth->createPermission('updateUser');
        $updateUser->description = 'Редагування користувача';
        $auth->add($updateUser, 0, $userModel);

        $statusUser = $auth->createPermission('statusUser');
        $statusUser->description = 'Активація / деактивація користувача';
        $auth->add($statusUser, 0, $userModel);

        /**
         * 11) Список менеджеров
         * - просмотр списка менеджеров (доступ к разделу)
         * - создание менеджера
         * - редактирование менеджера
         * - активация/деактивация менеджера
         * - просмотр карточки менеджера
         */
        $manager = AuthItemParent::addGroup('manager', 'Список менеджерів');

        $viewManager = $auth->createPermission('viewManager');
        $viewManager->description = 'Перегляд списку менеджерів (доступ до розділу)';
        $auth->add($viewManager, 0, $manager);

        $createManager = $auth->createPermission('createManager');
        $createManager->description = 'Створення менеджера';
        $auth->add($createManager, 0, $manager);

        $updateManager = $auth->createPermission('updateManager');
        $updateManager->description = 'Редагування менеджера';
        $auth->add($updateManager, 0, $manager);

        $updateManagerStatus = $auth->createPermission('updateManagerStatus');
        $updateManagerStatus->description = 'Активація / деактивація менеджера';
        $auth->add($updateManagerStatus, 0, $manager);

        $viewOnlyManager = $auth->createPermission('viewOnlyManager');
        $viewOnlyManager->description = 'Перегляд картки менеджера';
        $auth->add($viewOnlyManager, 0, $manager);

        /**
         * 12) список ролей:
         * - просмотр списка ролей (доступ к разделу)
         * - создание роли
         * - просмотр конкретной роли
         * - редактирование роли
         * - активация / деактивация роли
         * - активация / деактивация роли менеджера
         * - редактирование роли менеджера
         */
        $roles = AuthItemParent::addGroup('roles', 'Список ролей');

        $viewRoles = $auth->createPermission('viewRoles');
        $viewRoles->description = 'Перегляд списку ролей (доступ до розділу)';
        $auth->add($viewRoles, 0, $roles);

        $createRoles = $auth->createPermission('createRoles');
        $createRoles->description = 'Створення ролі';
        $auth->add($createRoles, 0, $roles);

        $viewOnlyRoles = $auth->createPermission('viewOnlyRoles');
        $viewOnlyRoles->description = 'Перегляд конкретної ролі';
        $auth->add($viewOnlyRoles, 0, $roles);

        $updateRoles = $auth->createPermission('updateRoles');
        $updateRoles->description = 'Редагування ролі';
        $auth->add($updateRoles, 0, $roles);

        $updateManagerRoles = $auth->createPermission('updateManagerRoles');
        $updateManagerRoles->description = 'Редагування ролі менеджера';
        $auth->add($updateManagerRoles, 0, $roles);

        $changeStatusRoles = $auth->createPermission('changeStatusRoles');
        $changeStatusRoles->description = 'Активація / деактивація ролі';
        $auth->add($changeStatusRoles, 0, $roles);

        $changeStatusManagerRoles = $auth->createPermission('changeStatusManagerRoles');
        $changeStatusManagerRoles->description = 'Активація / деактивація ролі менеджера';
        $auth->add($changeStatusManagerRoles, 0, $roles);

        /**
         * 13) Поставщики
         * - просмотр списка поставщиков (доступ к разделу)
         * - редактирование поставщика
         * - создание поставщика
         */
        $supplier = AuthItemParent::addGroup('supplier', 'Постачальники');

        $viewSupplier = $auth->createPermission('viewSupplier');
        $viewSupplier->description = 'Перегляд списку постачальників (доступ до розділу)';
        $auth->add($viewSupplier, 0, $supplier);

        $updateSupplier = $auth->createPermission('updateSupplier');
        $updateSupplier->description = 'Редагування постачальника';
        $auth->add($updateSupplier, 0, $supplier);

        $createSupplier = $auth->createPermission('createSupplier');
        $createSupplier->description = 'Створення постачальника';
        $auth->add($createSupplier, 0, $supplier);

        /**
         * 14) Согласования
         * - просмотр очередности согласований (доступ к разделу)
         * - создание шага согласования
         * - редактирование шага согласования
         * - просмотр карточки шага согласования
         * - назначение очередности согласований
         * - активация/деактивация шага согласования
         * - удаление шага согласования
         */
        $coordination = AuthItemParent::addGroup('coordination', 'Узгодження');

        $viewCoordination = $auth->createPermission('viewCoordination');
        $viewCoordination->description = 'Перегляд черговості погоджень (доступ до розділу)';
        $auth->add($viewCoordination, 0, $coordination);

        $createCoordination = $auth->createPermission('createCoordination');
        $createCoordination->description = 'Створення кроку узгодження';
        $auth->add($createCoordination, 0, $coordination);

        $updateCoordination = $auth->createPermission('updateCoordination');
        $updateCoordination->description = 'Редагування кроку узгодження';
        $auth->add($updateCoordination, 0, $coordination);

        $viewOnlyCoordination = $auth->createPermission('viewOnlyCoordination');
        $viewOnlyCoordination->description = 'Перегляд картки кроку узгодження';
        $auth->add($viewOnlyCoordination, 0, $coordination);

        $changeOrderCoordination = $auth->createPermission('changeOrderCoordination');
        $changeOrderCoordination->description = 'Призначення черговості погоджень';
        $auth->add($changeOrderCoordination, 0, $coordination);

        $changeStatusCoordination = $auth->createPermission('changeStatusCoordination');
        $changeStatusCoordination->description = 'Активація / деактивація кроку узгодження';
        $auth->add($changeStatusCoordination, 0, $coordination);

        $deleteCoordination = $auth->createPermission('deleteCoordination');
        $deleteCoordination->description = 'Видалення кроку узгодження';
        $auth->add($deleteCoordination, 0, $coordination);

        /**
         * 15) Заказы:
         * - просмотр списка заказов (доступ к разделу)
         * - просмотр каталога (доступ к разделу)
         * - создание заказа
         * - просмотр деталей заказа
         * - редактирование заказа (включая прикрепление файлов и комментирование заказа)
         * - согласование любого шага согласования
         * - финальное подтверждение создания заказа
         */

        $order = AuthItemParent::addGroup('order', 'Замовлення');

        $viewOrder = $auth->createPermission('viewOrder');
        $viewOrder->description = 'Перегляд списку замовлень (доступ до розділу)';
        $auth->add($viewOrder, 0, $order);

        $viewProduct = $auth->createPermission('viewProduct');
        $viewProduct->description = 'Перегляд каталогу';
        $auth->add($viewProduct, 0, $order);

        $createOrder = $auth->createPermission('createOrder');
        $createOrder->description = 'Створення замовлення';
        $auth->add($createOrder, 0, $order);

        $viewOrderDetail = $auth->createPermission('viewOrderDetail');
        $viewOrderDetail->description = 'Перегляд деталей замовлення';
        $auth->add($viewOrderDetail, 0, $order);

        $updateOrder = $auth->createPermission('updateOrder');
        $updateOrder->description = 'Редагування замовлення';
        $auth->add($updateOrder, 0, $order);

        $coordinationApproveOrder = $auth->createPermission('coordinationApproveOrder');
        $coordinationApproveOrder->description = 'Зміна будь-якого кроку узгодження';
        $auth->add($coordinationApproveOrder, 0, $order);

        $changeStatusOrder = $auth->createPermission('changeStatusOrder');
        $changeStatusOrder->description = 'Початок/відміна узгодження';
        $auth->add($changeStatusOrder, 0, $order);

        $changeAnyStatusOrder = $auth->createPermission('changeAnyStatusOrder');
        $changeAnyStatusOrder->description = 'Зміна будь-якого статусу замовлення';
        $auth->add($changeAnyStatusOrder, 0, $order);

        $viewAndCreateCostOrder = $auth->createPermission('viewAndCreateCostOrder');
        $viewAndCreateCostOrder->description = 'Доступ до замовлень своїх кост центрів';
        $auth->add($viewAndCreateCostOrder, 0, $order);

        $viewAndCreateLocationOrder = $auth->createPermission('viewAndCreateLocationOrder');
        $viewAndCreateLocationOrder->description = 'Доступ до замовлень своїх локацій';
        $auth->add($viewAndCreateLocationOrder, 0, $order);

        $viewAndCreateAllOrder = $auth->createPermission('viewAndCreateAllOrder');
        $viewAndCreateAllOrder->description = 'Доступ до замовлень всього підприємства';
        $auth->add($viewAndCreateAllOrder, 0, $order);

        /**
         * 16) Бюджеты:
         * - просмотр списка бюджетов (доступ к разделу)
         * - создание бюджета
         * - редактироаание бюджета
         * - просмотр бюджета
         * - удаление бюджета
         */
        $budget = AuthItemParent::addGroup('budget', 'Бюджети');

        $viewBudgets = $auth->createPermission('viewBudgets');
        $viewBudgets->description = 'Перегляд списку бюджетів (доступ до розділу)';
        $auth->add($viewBudgets, 0, $budget);

        $createBudget = $auth->createPermission('createBudget');
        $createBudget->description = 'Створення бюджету';
        $auth->add($createBudget, 0, $budget);

        $updateBudget = $auth->createPermission('updateBudget');
        $updateBudget->description = 'Редагування бюджету';
        $auth->add($updateBudget, 0, $budget);

        $detailBudget = $auth->createPermission('detailBudget');
        $detailBudget->description = 'Перегляд бюджету';
        $auth->add($detailBudget, 0, $budget);

        $deleteBudget = $auth->createPermission('deleteBudget');
        $deleteBudget->description = 'Видалення бюджету';
        $auth->add($deleteBudget, 0, $budget);

        /**
         * 17) Настройки предприятия
         * - просмотр настроек предприятия
         * - редактирование настроек предприятия
         */
        $company = AuthItemParent::addGroup('company', 'Налаштування підприємства');

        $settingsCompany = $auth->createPermission('settingsCompany');
        $settingsCompany->description = 'Редагування налаштувань підприємства';
        $auth->add($settingsCompany, 0, $company);

        $administrationChangeCompany = $auth->createPermission('administrationChangeCompany');
        $administrationChangeCompany->description = 'Доступ до сторінки списку підприємств';
        $auth->add($administrationChangeCompany, 0, $company);

        $createCompany = $auth->createPermission('createCompany');
        $createCompany->description = 'Створення підприємств';
        $auth->add($createCompany, 0, $company);

        /**
         * 18) Поддержка
         * - прием и обработка обращений
         * - завершение обращений
         */
        $support = AuthItemParent::addGroup('support', 'Підтримка');

        $receiveMessagesSupport = $auth->createPermission('receiveMessagesSupport');
        $receiveMessagesSupport->description = 'Прийом і обробка звернень';
        $auth->add($receiveMessagesSupport, 0, $support);

        /**
         * 19) Сповіщення
         */
        $notification = AuthItemParent::addGroup('notification', 'Сповіщення');

        $controlContract = $auth->createPermission('controlContract');
        $controlContract->description = 'Контроль договорів';
        $auth->add($controlContract, 0, $notification);

        $controlCompanyEntity = $auth->createPermission('controlCompanyEntity');
        $controlCompanyEntity->description = 'Контроль юр.осіб';
        $auth->add($controlCompanyEntity, 0, $notification);

        $controlLocation = $auth->createPermission('controlLocation');
        $controlLocation->description = 'Контроль локацій';
        $auth->add($controlLocation, 0, $notification);

        $controlCatalog = $auth->createPermission('controlCatalog');
        $controlCatalog->description = 'Каталог';
        $auth->add($controlCatalog, 0, $notification);

        $controlCoordination = $auth->createPermission('controlCoordination');
        $controlCoordination->description = 'Контроль узгоджень';
        $auth->add($controlCoordination, 0, $notification);

        $controlMyCoordination = $auth->createPermission('controlMyCoordination');
        $controlMyCoordination->description = 'Мої узгодження';
        $auth->add($controlMyCoordination, 0, $notification);

        $controlOrder = $auth->createPermission('controlOrder');
        $controlOrder->description = 'Контроль замовлень';
        $auth->add($controlOrder, 0, $notification);

        $controlMyOrder = $auth->createPermission('controlMyOrder');
        $controlMyOrder->description = 'Мої замовлення';
        $auth->add($controlMyOrder, 0, $notification);

        $controlBudget = $auth->createPermission('controlBudget');
        $controlBudget->description = 'Контроль бюджетів';
        $auth->add($controlBudget, 0, $notification);

        $controlMyBudget = $auth->createPermission('controlMyBudget');
        $controlMyBudget->description = 'Мої бюджети';
        $auth->add($controlMyBudget, 0, $notification);

        $controlCompany = $auth->createPermission('controlCompany');
        $controlCompany->description = 'Контроль підприємств';
        $auth->add($controlCompany, 0, $notification);

        $controlUser = $auth->createPermission('controlUser');
        $controlUser->description = 'Контроль користувачів';
        $auth->add($controlUser, 0, $notification);

        $controlGeneralRole = $auth->createPermission('controlGeneralRole');
        $controlGeneralRole->description = 'Контроль внутрішніх ролей';
        $auth->add($controlGeneralRole, 0, $notification);

        $controlUserRole = $auth->createPermission('controlUserRole');
        $controlUserRole->description = 'Контроль користувацьких ролей';
        $auth->add($controlUserRole, 0, $notification);

        $controlAllCatalog = $auth->createPermission('controlAllCatalog');
        $controlAllCatalog->description = 'Контроль каталога';
        $auth->add($controlAllCatalog, 0, $notification);

        $controlMarkup = $auth->createPermission('controlMarkup');
        $controlMarkup->description = 'Контроль націнок';
        $auth->add($controlMarkup, 0, $notification);

        $controlSpecification = $auth->createPermission('controlSpecification');
        $controlSpecification->description = 'Контроль специфікацій';
        $auth->add($controlSpecification, 0, $notification);

        $controlRequestOrder = $auth->createPermission('controlRequestOrder');
        $controlRequestOrder->description = 'Контроль заявок замовлень';
        $auth->add($controlRequestOrder, 0, $notification);

        /**
         * 20) Каталог
         */
        $catalog = AuthItemParent::addGroup('catalog', 'Каталог');

        $viewCatalog = $auth->createPermission('viewCatalog');
        $viewCatalog->description = 'Перегляд каталогу (доступ до розділу)';
        $auth->add($viewCatalog, 0, $catalog);


        /**
         * Роли
         */
        $admin = $auth->createRole('admin');
        $admin->description = 'Адміністратор';
        $auth->add($admin);

        $defaultUser = $auth->createRole('defaultUser');
        $defaultUser->description = 'Користувач';
        $auth->add($defaultUser);

        $mainUser = $auth->createRole('mainUser');
        $mainUser->description = 'Головний користувач';
        $auth->add($mainUser);

        $managerUser = $auth->createRole('managerUser');
        $managerUser->description = 'Менеджер';
        $auth->add($managerUser);


        $auth->addChild($admin, $viewDashboardOrder);
        $auth->addChild($admin, $viewDashboardOpenStatistic);
        $auth->addChild($admin, $viewDashboardCloseStatistic);

        $auth->addChild($admin, $updateProfileAddFields);

        $auth->addChild($admin, $viewContract);
        $auth->addChild($admin, $statusContract);
        $auth->addChild($admin, $createContract);
        $auth->addChild($admin, $updateContract);
        $auth->addChild($admin, $viewOnlyContract);

        $auth->addChild($admin, $viewCompanyEntity);
        $auth->addChild($admin, $statusCompanyEntity);
        $auth->addChild($admin, $viewOnlyCompanyEntity);
        $auth->addChild($admin, $updateCompanyEntity);
        $auth->addChild($admin, $createCompanyEntity);

        $auth->addChild($admin, $viewLimitation);
        $auth->addChild($admin, $catalogLimitationCreate);
        $auth->addChild($admin, $catalogLimitationUpdate);
        $auth->addChild($admin, $catalogLimitationViewDetail);

        $auth->addChild($admin, $viewSpecification);
        $auth->addChild($admin, $createSpecification);
        $auth->addChild($admin, $viewSpecificationDetail);
        $auth->addChild($admin, $updateSpecification);
        $auth->addChild($admin, $changeStatusSpecification);

        $auth->addChild($admin, $viewLocations);
        $auth->addChild($admin, $createLocations);
        $auth->addChild($admin, $statusLocations);
        $auth->addChild($admin, $updateLocations);
        $auth->addChild($admin, $viewOnlyLocations);

        $auth->addChild($admin, $viewLocationTree);

        $auth->addChild($admin, $viewCostCenter);
        $auth->addChild($admin, $updateCostCenter);
        $auth->addChild($admin, $createCostCenter);

        $auth->addChild($admin, $viewUser);
        $auth->addChild($admin, $createUser);
        $auth->addChild($admin, $updateUser);
        $auth->addChild($admin, $statusUser);

        $auth->addChild($admin, $viewManager);
        $auth->addChild($admin, $createManager);
        $auth->addChild($admin, $updateManager);
        $auth->addChild($admin, $updateManagerStatus);
        $auth->addChild($admin, $viewOnlyManager);

        $auth->addChild($admin, $viewRoles);
        $auth->addChild($admin, $createRoles);
        $auth->addChild($admin, $viewOnlyRoles);
        $auth->addChild($admin, $updateRoles);
        $auth->addChild($admin, $updateManagerRoles);
        $auth->addChild($admin, $changeStatusRoles);
        $auth->addChild($admin, $changeStatusManagerRoles);

        $auth->addChild($admin, $viewSupplier);
        $auth->addChild($admin, $updateSupplier);
        $auth->addChild($admin, $createSupplier);

        $auth->addChild($admin, $viewCoordination);
        $auth->addChild($admin, $createCoordination);
        $auth->addChild($admin, $updateCoordination);
        $auth->addChild($admin, $viewOnlyCoordination);
        $auth->addChild($admin, $changeOrderCoordination);
        $auth->addChild($admin, $changeStatusCoordination);
        $auth->addChild($admin, $deleteCoordination);

        $auth->addChild($admin, $viewOrder);
        $auth->addChild($admin, $viewProduct);
        $auth->addChild($admin, $createOrder);
        $auth->addChild($admin, $viewOrderDetail);
        $auth->addChild($admin, $updateOrder);
        $auth->addChild($admin, $coordinationApproveOrder);
        $auth->addChild($admin, $changeStatusOrder);
        $auth->addChild($admin, $changeAnyStatusOrder);
        $auth->addChild($admin, $viewAndCreateCostOrder);
        $auth->addChild($admin, $viewAndCreateLocationOrder);
        $auth->addChild($admin, $viewAndCreateAllOrder);

        $auth->addChild($admin, $viewBudgets);
        $auth->addChild($admin, $createBudget);
        $auth->addChild($admin, $updateBudget);
        $auth->addChild($admin, $detailBudget);
        $auth->addChild($admin, $deleteBudget);

        $auth->addChild($admin, $settingsCompany);
        $auth->addChild($admin, $administrationChangeCompany);
        $auth->addChild($admin, $createCompany);

        $auth->addChild($admin, $receiveMessagesSupport);

        $auth->addChild($admin, $controlContract);
        $auth->addChild($admin, $controlCompanyEntity);
        $auth->addChild($admin, $controlLocation);
        $auth->addChild($admin, $controlCatalog);
        $auth->addChild($admin, $controlCoordination);
        $auth->addChild($admin, $controlMyCoordination);
        $auth->addChild($admin, $controlOrder);
        $auth->addChild($admin, $controlMyOrder);
        $auth->addChild($admin, $controlBudget);
        $auth->addChild($admin, $controlMyBudget);
        $auth->addChild($admin, $controlCompany);
        $auth->addChild($admin, $controlUser);
        $auth->addChild($admin, $controlGeneralRole);
        $auth->addChild($admin, $controlUserRole);
        $auth->addChild($admin, $controlAllCatalog);
        $auth->addChild($admin, $controlMarkup);
        $auth->addChild($admin, $controlSpecification);
        $auth->addChild($admin, $controlRequestOrder);

        $auth->addChild($admin, $viewCatalog);

        //
        $auth->addChild($mainUser, $viewDashboardOrder);
        $auth->addChild($mainUser, $viewDashboardOpenStatistic);
//        $auth->addChild($mainUser, $viewDashboardCloseStatistic);

        $auth->addChild($mainUser, $updateProfileAddFields);

        $auth->addChild($mainUser, $viewContract);
        $auth->addChild($mainUser, $statusContract);
//        $auth->addChild($mainUser, $createContract);
//        $auth->addChild($mainUser, $updateContract);
        $auth->addChild($mainUser, $viewOnlyContract);

        $auth->addChild($mainUser, $viewCompanyEntity);
        $auth->addChild($mainUser, $statusCompanyEntity);
        $auth->addChild($mainUser, $viewOnlyCompanyEntity);
        $auth->addChild($mainUser, $updateCompanyEntity);
        $auth->addChild($mainUser, $createCompanyEntity);

//        $auth->addChild($mainUser, $viewLimitation);
//        $auth->addChild($mainUser, $catalogLimitationCreate);
//        $auth->addChild($mainUser, $catalogLimitationUpdate);
//        $auth->addChild($mainUser, $catalogLimitationViewDetail);

//        $auth->addChild($mainUser, $viewSpecification);
//        $auth->addChild($mainUser, $createSpecification);
//        $auth->addChild($mainUser, $viewSpecificationDetail);
//        $auth->addChild($mainUser, $updateSpecification);
//        $auth->addChild($mainUser, $changeStatusSpecification);

        $auth->addChild($mainUser, $viewLocations);
        $auth->addChild($mainUser, $createLocations);
        $auth->addChild($mainUser, $statusLocations);
        $auth->addChild($mainUser, $updateLocations);
        $auth->addChild($mainUser, $viewOnlyLocations);

        $auth->addChild($mainUser, $viewLocationTree);

        $auth->addChild($mainUser, $viewCostCenter);
        $auth->addChild($mainUser, $updateCostCenter);
        $auth->addChild($mainUser, $createCostCenter);

        $auth->addChild($mainUser, $viewUser);
        $auth->addChild($mainUser, $createUser);
        $auth->addChild($mainUser, $updateUser);
        $auth->addChild($mainUser, $statusUser);

//        $auth->addChild($mainUser, $viewManager);
//        $auth->addChild($mainUser, $createManager);
//        $auth->addChild($mainUser, $updateManager);
//        $auth->addChild($mainUser, $updateManagerStatus);
//        $auth->addChild($mainUser, $viewOnlyManager);

        $auth->addChild($mainUser, $viewRoles);
        $auth->addChild($mainUser, $createRoles);
        $auth->addChild($mainUser, $viewOnlyRoles);
        $auth->addChild($mainUser, $updateRoles);
        $auth->addChild($mainUser, $changeStatusRoles);

        $auth->addChild($mainUser, $viewSupplier);
//        $auth->addChild($mainUser, $updateSupplier);
//        $auth->addChild($mainUser, $createSupplier);

        $auth->addChild($mainUser, $viewCoordination);
        $auth->addChild($mainUser, $createCoordination);
        $auth->addChild($mainUser, $updateCoordination);
        $auth->addChild($mainUser, $viewOnlyCoordination);
        $auth->addChild($mainUser, $changeOrderCoordination);
        $auth->addChild($mainUser, $changeStatusCoordination);
        $auth->addChild($mainUser, $deleteCoordination);

        $auth->addChild($mainUser, $viewOrder);
        $auth->addChild($mainUser, $viewProduct);
        $auth->addChild($mainUser, $createOrder);
        $auth->addChild($mainUser, $viewOrderDetail);
        $auth->addChild($mainUser, $updateOrder);
        $auth->addChild($mainUser, $coordinationApproveOrder);
        $auth->addChild($mainUser, $changeStatusOrder);
        $auth->addChild($mainUser, $changeAnyStatusOrder);
        $auth->addChild($mainUser, $viewAndCreateCostOrder);
        $auth->addChild($mainUser, $viewAndCreateLocationOrder);
        $auth->addChild($mainUser, $viewAndCreateAllOrder);

        $auth->addChild($mainUser, $viewBudgets);
        $auth->addChild($mainUser, $createBudget);
        $auth->addChild($mainUser, $updateBudget);
        $auth->addChild($mainUser, $detailBudget);
        $auth->addChild($mainUser, $deleteBudget);

        $auth->addChild($mainUser, $settingsCompany);
//        $auth->addChild($mainUser, $administrationChangeCompany);
//        $auth->addChild($mainUser, $createCompany);

//        $auth->addChild($mainUser, $receiveMessagesSupport);

        $auth->addChild($mainUser, $controlContract);
        $auth->addChild($mainUser, $controlCompanyEntity);
        $auth->addChild($mainUser, $controlLocation);
        $auth->addChild($mainUser, $controlCatalog);
        $auth->addChild($mainUser, $controlCoordination);
        $auth->addChild($mainUser, $controlMyCoordination);
        $auth->addChild($mainUser, $controlOrder);
        $auth->addChild($mainUser, $controlMyOrder);
        $auth->addChild($mainUser, $controlBudget);
        $auth->addChild($mainUser, $controlMyBudget);
        $auth->addChild($mainUser, $controlCompany);
        $auth->addChild($mainUser, $controlUser);
        $auth->addChild($mainUser, $controlGeneralRole);
        $auth->addChild($mainUser, $controlUserRole);
        $auth->addChild($mainUser, $controlAllCatalog);
        $auth->addChild($mainUser, $controlMarkup);
        $auth->addChild($mainUser, $controlSpecification);
        $auth->addChild($mainUser, $controlRequestOrder);

        $auth->addChild($mainUser, $viewCatalog);


        $auth->addChild($managerUser, $viewDashboardOrder);
        $auth->addChild($managerUser, $viewDashboardOpenStatistic);
//        $auth->addChild($managerUser, $viewDashboardCloseStatistic);

        $auth->addChild($managerUser, $updateProfileAddFields);

        $auth->addChild($managerUser, $viewContract);
        $auth->addChild($managerUser, $statusContract);
        $auth->addChild($managerUser, $createContract);
        $auth->addChild($managerUser, $updateContract);
        $auth->addChild($managerUser, $viewOnlyContract);

        $auth->addChild($managerUser, $viewCompanyEntity);
        $auth->addChild($managerUser, $statusCompanyEntity);
        $auth->addChild($managerUser, $viewOnlyCompanyEntity);
        $auth->addChild($managerUser, $updateCompanyEntity);
        $auth->addChild($managerUser, $createCompanyEntity);

        $auth->addChild($managerUser, $viewLimitation);
        $auth->addChild($managerUser, $catalogLimitationCreate);
        $auth->addChild($managerUser, $catalogLimitationUpdate);
        $auth->addChild($managerUser, $catalogLimitationViewDetail);

        $auth->addChild($managerUser, $viewSpecification);
        $auth->addChild($managerUser, $createSpecification);
        $auth->addChild($managerUser, $viewSpecificationDetail);
        $auth->addChild($managerUser, $updateSpecification);
        $auth->addChild($managerUser, $changeStatusSpecification);

        $auth->addChild($managerUser, $viewLocations);
        $auth->addChild($managerUser, $createLocations);
        $auth->addChild($managerUser, $statusLocations);
        $auth->addChild($managerUser, $updateLocations);
        $auth->addChild($managerUser, $viewOnlyLocations);

        $auth->addChild($managerUser, $viewLocationTree);

        $auth->addChild($managerUser, $viewCostCenter);
        $auth->addChild($managerUser, $updateCostCenter);
        $auth->addChild($managerUser, $createCostCenter);

        $auth->addChild($managerUser, $viewUser);
        $auth->addChild($managerUser, $createUser);
        $auth->addChild($managerUser, $updateUser);
        $auth->addChild($managerUser, $statusUser);

        $auth->addChild($managerUser, $viewManager);
//        $auth->addChild($managerUser, $createManager);
//        $auth->addChild($managerUser, $updateManager);
//        $auth->addChild($managerUser, $updateManagerStatus);
        $auth->addChild($managerUser, $viewOnlyManager);

        $auth->addChild($managerUser, $viewRoles);
        $auth->addChild($managerUser, $createRoles);
        $auth->addChild($managerUser, $viewOnlyRoles);
        $auth->addChild($managerUser, $updateRoles);
//        $auth->addChild($managerUser, $updateManagerRoles);
        $auth->addChild($managerUser, $changeStatusRoles);
//        $auth->addChild($managerUser, $changeStatusManagerRoles);

        $auth->addChild($managerUser, $viewSupplier);
        $auth->addChild($managerUser, $updateSupplier);
        $auth->addChild($managerUser, $createSupplier);

        $auth->addChild($managerUser, $viewCoordination);
        $auth->addChild($managerUser, $createCoordination);
        $auth->addChild($managerUser, $updateCoordination);
        $auth->addChild($managerUser, $viewOnlyCoordination);
        $auth->addChild($managerUser, $changeOrderCoordination);
        $auth->addChild($managerUser, $changeStatusCoordination);
        $auth->addChild($managerUser, $deleteCoordination);

        $auth->addChild($managerUser, $viewOrder);
        $auth->addChild($managerUser, $viewProduct);
        $auth->addChild($managerUser, $createOrder);
        $auth->addChild($managerUser, $viewOrderDetail);
        $auth->addChild($managerUser, $updateOrder);
        $auth->addChild($managerUser, $coordinationApproveOrder);
        $auth->addChild($managerUser, $changeStatusOrder);
        $auth->addChild($managerUser, $changeAnyStatusOrder);
        $auth->addChild($managerUser, $viewAndCreateCostOrder);
        $auth->addChild($managerUser, $viewAndCreateLocationOrder);
        $auth->addChild($managerUser, $viewAndCreateAllOrder);

        $auth->addChild($managerUser, $viewBudgets);
        $auth->addChild($managerUser, $createBudget);
        $auth->addChild($managerUser, $updateBudget);
        $auth->addChild($managerUser, $detailBudget);
        $auth->addChild($managerUser, $deleteBudget);

        $auth->addChild($managerUser, $settingsCompany);
        $auth->addChild($managerUser, $administrationChangeCompany);
        $auth->addChild($managerUser, $createCompany);

        $auth->addChild($managerUser, $receiveMessagesSupport);

        $auth->addChild($managerUser, $controlContract);
        $auth->addChild($managerUser, $controlCompanyEntity);
        $auth->addChild($managerUser, $controlLocation);
        $auth->addChild($managerUser, $controlCatalog);
        $auth->addChild($managerUser, $controlCoordination);
        $auth->addChild($managerUser, $controlMyCoordination);
        $auth->addChild($managerUser, $controlOrder);
        $auth->addChild($managerUser, $controlMyOrder);
        $auth->addChild($managerUser, $controlBudget);
        $auth->addChild($managerUser, $controlMyBudget);
        $auth->addChild($managerUser, $controlCompany);
        $auth->addChild($managerUser, $controlUser);
        $auth->addChild($managerUser, $controlGeneralRole);
        $auth->addChild($managerUser, $controlUserRole);
        $auth->addChild($managerUser, $controlAllCatalog);
        $auth->addChild($managerUser, $controlMarkup);
        $auth->addChild($managerUser, $controlSpecification);
        $auth->addChild($managerUser, $controlRequestOrder);

        $auth->addChild($managerUser, $viewCatalog);


        $auth->assign($admin, 1);
        $auth->assign($admin, 2);
        $usr = User::find()->where(['id' => 37])->one();
        if ($usr) {
            $auth->assign($admin, 37);
        }
        $usr = User::find()->where(['id' => 56])->one();
        if ($usr) {
            $auth->assign($admin, 56);
        }
        AuthItem::updateAll(['default_role' => 1], ['in', 'name', ['admin', 'managerUser', 'mainUser', 'defaultUser']]);
    }

    public function actionAddRuleModeration()
    {
        $auth = Yii::$app->authManager;

        $orderModeration = $auth->createPermission('orderModeration');
        $orderModeration->description = 'Модерація замовлення';
        $auth->add($orderModeration, 0, 15);
        $admin = $auth->getRole('admin');
        $auth->addChild($admin, $orderModeration);
    }

    public function actionAddRuleShowBtn()
    {
        $auth = Yii::$app->authManager;

        $orderShowBtn = $auth->createPermission('orderShowBtn');
        $orderShowBtn->description = 'Доступ до всіх товарів';
        $auth->add($orderShowBtn, 0, 15);

        $catalogShowBtn = $auth->createPermission('catalogShowBtn');
        $catalogShowBtn->description = 'Доступ до всіх товарів';
        $auth->add($catalogShowBtn, 0, 20);

        $admin = $auth->getRole('admin');
        $auth->addChild($admin, $orderShowBtn);
        $auth->addChild($admin, $catalogShowBtn);
    }

    public function actionAddDashboardRule()
    {
        $auth = Yii::$app->authManager;

        $viewDashboard = $auth->createPermission('viewDashboard');
        $viewDashboard->description = 'Перегляд дошки (доступ до розділу)';
        $auth->add($viewDashboard, 0, 1);

        $admin = $auth->getRole('admin');
        $auth->addChild($admin, $viewDashboard);
    }

    public function actionAddRuleCostUuid()
    {
        $auth = Yii::$app->authManager;

        $uuidCostCenter = $auth->createPermission('uuidCostCenter');
        $uuidCostCenter->description = 'Кост-центри - uuid';
        $auth->add($uuidCostCenter, 0, 9);

        $admin = $auth->getRole('admin');
        $auth->addChild($admin, $uuidCostCenter);
    }

    public function actionAddBudgetRule()
    {
        $auth = Yii::$app->authManager;

        $budgetViewCost = $auth->createPermission('budgetViewCost');
        $budgetViewCost->description = 'Доступ до бюджетів моїх кост-центрів';
        $auth->add($budgetViewCost, 0, 16);

        $budgetViewLocation = $auth->createPermission('budgetViewLocation');
        $budgetViewLocation->description = 'Доступ до бюджетів моїх локацій';
        $auth->add($budgetViewLocation, 0, 16);

        $budgetViewAll = $auth->createPermission('budgetViewAll');
        $budgetViewAll->description = 'Доступ до бюджетів всього підприємства';
        $auth->add($budgetViewAll, 0, 16);

        $admin = $auth->getRole('admin');
        $auth->addChild($admin, $budgetViewCost);
        $auth->addChild($admin, $budgetViewLocation);
        $auth->addChild($admin, $budgetViewAll);
    }

    public function actionAddOrderApprove()
    {
        $auth = Yii::$app->authManager;

        $orderApproveReceive = $auth->createPermission('orderApproveReceive');
        $orderApproveReceive->description = 'Підтвердження прийому замовлення';
        $auth->add($orderApproveReceive, 0, 15);

        $admin = $auth->getRole('admin');
        $auth->addChild($admin, $orderApproveReceive);
    }

    public function actionDeleteRuleSpecVisible()
    {
        $auth = Yii::$app->authManager;
        $visibleSpecification = $auth->getPermission('visibleSpecification');
        $auth->remove($visibleSpecification);
    }

    public function actionAddOrderDuplicate()
    {
        $auth = Yii::$app->authManager;

        $orderDuplicate = $auth->createPermission('orderDuplicate');
        $orderDuplicate->description = 'Дублювання замовлення';
        $auth->add($orderDuplicate, 0, 15);

        $admin = $auth->getRole('admin');
        $auth->addChild($admin, $orderDuplicate);
    }

    public function actionOrderGoBack()
    {
        $auth = Yii::$app->authManager;

        $orderGoBack = $auth->createPermission('orderGoBack');
        $orderGoBack->description = 'Зміна статусу отриманих замовлень';
        $auth->add($orderGoBack, 0, 15);

        $admin = $auth->getRole('admin');
        $auth->addChild($admin, $orderGoBack);
    }

    public function actionAddOrderChangeProduct()
    {
        $auth = Yii::$app->authManager;

        $orderChangeProduct = $auth->createPermission('orderChangeProduct');
        $orderChangeProduct->description = 'Жорстка заміна товару';
        $auth->add($orderChangeProduct, 0, 15);

        $admin = $auth->getRole('admin');
        $auth->addChild($admin, $orderChangeProduct);
    }

    public function actionAddImportModel()
    {
        $auth = Yii::$app->authManager;

        $import = AuthItemParent::addGroup('import', 'Імпорт');

        $importModel = $auth->createPermission('importModel');
        $importModel->description = 'Імпорт моделей';
        $auth->add($importModel, 0, $import);

        $admin = $auth->getRole('admin');
        $auth->addChild($admin, $importModel);
    }

    public function actionEmail()
    {
        $auth = Yii::$app->authManager;

        $management = AuthItemParent::addGroup('management', 'Управління');

        $managementEmail = $auth->createPermission('managementEmail');
        $managementEmail->description = 'Управління заявками';
        $auth->add($managementEmail, 0, $management);

        $admin = $auth->getRole('admin');
        $auth->addChild($admin, $managementEmail);
    }

    public function actionAddApiModel()
    {
        $auth = Yii::$app->authManager;

        $apiBlock = AuthItemParent::addGroup('apiBlock', 'API');

        $apiModel = $auth->createPermission('apiModel');
        $apiModel->description = 'Імпорт моделей';
        $auth->add($apiModel, 0, $apiBlock);

        $api = $auth->createRole('api');
        $api->description = 'API';
        $auth->add($api);

        $auth->addChild($api, $apiModel);
        $auth->assign($api, 3);
    }

    public function actionAddManagementProduct()
    {
        $auth = Yii::$app->authManager;

        $managementProductView = $auth->createPermission('managementProductView');
        $managementProductView->description = 'Перегляд списку товарів (доступ до розділу)';
        $auth->add($managementProductView, 0, 22);

        $managementProductCreate = $auth->createPermission('managementProductCreate');
        $managementProductCreate->description = 'Створення товару';
        $auth->add($managementProductCreate, 0, 22);

        $managementProductUpdate = $auth->createPermission('managementProductUpdate');
        $managementProductUpdate->description = 'Редагування товару';
        $auth->add($managementProductUpdate, 0, 22);

        $admin = $auth->getRole('admin');
        $auth->addChild($admin, $managementProductView);
        $auth->addChild($admin, $managementProductCreate);
        $auth->addChild($admin, $managementProductUpdate);
    }

    public function actionAddEditOnApprove()
    {
        $auth = Yii::$app->authManager;

        $editOnApproveOrder = $auth->createPermission('editOnApproveOrder');
        $editOnApproveOrder->description = 'Редагування товарів замовлення "На узгодженні"';
        $auth->add($editOnApproveOrder, 0, 15);

        $admin = $auth->getRole('admin');
        $auth->addChild($admin, $editOnApproveOrder);
    }

    public function actionAddManagementImport()
    {
        $auth = Yii::$app->authManager;

        $managementImportCatalog = $auth->createPermission('managementImportCatalog');
        $managementImportCatalog->description = 'Імпорт товарів';
        $auth->add($managementImportCatalog, 0, 22);

        $managementImportPrice = $auth->createPermission('managementImportPrice');
        $managementImportPrice->description = 'Імпорт цін';
        $auth->add($managementImportPrice, 0, 22);

        $managementImportImage = $auth->createPermission('managementImportImage');
        $managementImportImage->description = 'Імпорт зображень товарів';
        $auth->add($managementImportImage, 0, 22);

        $admin = $auth->getRole('admin');
        $auth->addChild($admin, $managementImportCatalog);
        $auth->addChild($admin, $managementImportPrice);
        $auth->addChild($admin, $managementImportImage);
    }

    public function actionAddManagementBufferStatus()
    {
        $auth = Yii::$app->authManager;

        $managementProductBuffer = $auth->createPermission('managementProductBuffer');
        $managementProductBuffer->description = 'Редагування статусу товару в буфері';
        $auth->add($managementProductBuffer, 0, 22);


        $admin = $auth->getRole('admin');
        $auth->addChild($admin, $managementProductBuffer);
    }
}
