<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 08.06.2019
 * Time: 15:31
 */

namespace api\schema;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\UnionType;
use yii\base\Model;
use api\schema\mutations\UserMutationType;

/**
 * Class Types
 * @package api\schema
 */
class Types
{
    private static $query;
    private static $queryPublic;
    private static $mutation;
    private static $validationError;
    private static $validationErrorsList;
    private static $validationTypes;
    private static $paginationType;

    private static $location;
    private static $locationList;
    private static $locationPagination;

    private static $costCenter;
    private static $costCenterPagination;

    private static $company;
    private static $companyInfo;
    private static $companyPagination;
    private static $companyStatus;

    private static $currency;
    private static $currencyPagination;

    private static $authItem;
    private static $authItemPagination;

    private static $authItemParent;
    private static $authItemParentPagination;

    private static $user;
    private static $userPagination;

    private static $userMutation;

    private static $companyEntity;
    private static $companyEntityPagination;
    private static $companyEntityStatus;
    private static $companyEntityLocation;

    private static $userLocation;
    private static $userCostCenters;
    private static $userCompany;

    private static $budget;
    private static $budgetPagination;
    private static $budgetLocation;
    private static $budgetCostCenters;

    private static $coordination;
    private static $coordinationRoles;
    private static $coordinationUsers;

    private static $supplier;
    private static $supplierPagination;

    private static $contract;
    private static $contractPagination;
    private static $contractCostCenter;
    private static $contractLocation;

    private static $order;
    private static $orderPagination;
    private static $orderComment;
    private static $orderCoordination;
    private static $orderFile;
    private static $orderProduct;
    private static $orderProductHistory;
    private static $orderProductStatus;
    private static $orderProductPagination;
    private static $orderDashboard;
    private static $orderDashboardPagination;

    private static $status;

    private static $unit;
    private static $product;
    private static $productPagination;

    private static $category;

    private static $catalogLimitation;
    private static $catalogLimitationPagination;

    private static $taxOption;
    private static $specification;
    private static $specificationPagination;
    private static $specificationLocation;
    private static $specificationProduct;
    private static $specificationProductPagination;

    private static $text;
    private static $chatMessages;
    private static $unreadMessages;

    private static $notification;
    private static $notificationPagination;
    private static $chatInfo;
    private static $countNotification;

    private static $companySupplier;

    private static $history;

    private static $cart;
    private static $cartProduct;
    private static $cartProductPagination;
    private static $cartProductHistory;
    private static $cartProductHistoryPagination;

    private static $oneSCheck;

    private static $managementProduct;
    private static $managementProductPagination;

    private static $attributeValue;
    private static $attribute;

    private static $vendor;
    private static $brand;
    private static $uktzed;

    private static $adminEmail;
    private static $adminEmailPagination;

    private static $image;


    /**
     * @return QueryType
     */
    public static function query()
    {
        return self::$query ?: (self::$query = new QueryType());
    }

    public static function queryPublic()
    {
        return self::$queryPublic ?: (self::$queryPublic = new PublicQueryType());
    }

    /**
     * @return MutationType
     */
    public static function mutation()
    {
        return self::$mutation ?: (self::$mutation = new MutationType());
    }

    /**
     * @return ValidationErrorType
     */
    public static function validationError()
    {
        return self::$validationError ?: (self::$validationError = new ValidationErrorType());
    }

    /**
     * @return ValidationErrorsListType
     */
    public static function validationErrorsList()
    {
        return self::$validationErrorsList ?: (self::$validationErrorsList = new ValidationErrorsListType());
    }

    /**
     * @param ObjectType $type
     * @return mixed
     */
    public static function validationErrorsUnionType(ObjectType $type)
    {
        if (!isset(self::$validationTypes[$type->name . 'ValidationErrorsType'])) {
            self::$validationTypes[$type->name . 'ValidationErrorsType'] = new UnionType([
                'name' => $type->name . 'ValidationErrorsType',
                'types' => [
                    $type,
                    Types::validationErrorsList(),
                ],
                'resolveType' => function ($value) use ($type) {
                    if ($value instanceof Model) {
                        return $type;
                    } else {
                        return Types::validationErrorsList();
                    }
                }
            ]);
        }
        return self::$validationTypes[$type->name . 'ValidationErrorsType'];
    }

    public static function paginationType(ObjectType $type)
    {
        return self::$paginationType ?: (self::$paginationType = new PaginationType($type));
    }

    public static function location()
    {
        return self::$location ?: (self::$location = new LocationType());
    }

    public static function locationList()
    {
        return self::$locationList ?: (self::$locationList = new LocationListType());
    }

    public static function locationPagination()
    {
        return self::$locationPagination ?: (self::$locationPagination = new PaginationType(new LocationType(true)));
    }

    public static function costCenter()
    {
        return self::$costCenter ?: (self::$costCenter = new CostCenterType());
    }

    public static function costCenterPagination()
    {
        return self::$costCenterPagination ?: (self::$costCenterPagination = new PaginationType(
            new CostCenterType(true)
        ));
    }

    public static function company()
    {
        return self::$company ?: (self::$company = new CompanyType());
    }

    public static function companyInfo()
    {
        return self::$companyInfo ?: (self::$companyInfo = new CompanyType());
    }

    public static function companyPagination()
    {
        return self::$companyPagination ?: (self::$companyPagination = new PaginationType(new CompanyType(true)));
    }


    public static function companyStatus()
    {
        return self::$companyStatus ?: (self::$companyStatus = new CompanyStatusType());
    }

    /**
     * @return CurrencyType
     */
    public static function currency()
    {
        return self::$currency ?: (self::$currency = new CurrencyType());
    }

    public static function currencyPagination()
    {
        return self::$currencyPagination ?: (self::$currencyPagination = new PaginationType(new CurrencyType(true)));
    }

    /**
     * @return AuthItemType
     */
    public static function authItem()
    {
        return self::$authItem ?: (self::$authItem = new AuthItemType());
    }

    public static function authItemPagination()
    {
        return self::$authItemPagination ?: (self::$authItemPagination = new PaginationType(new AuthItemType(true)));
    }

    /**
     * @return AuthItemParentType
     */
    public static function authItemParent()
    {
        return self::$authItemParent ?: (self::$authItemParent = new AuthItemParentType());
    }

    public static function authItemParentPagination()
    {
        return self::$authItemParentPagination ?: (self::$authItemParentPagination = new PaginationType(
            new AuthItemParentType(true)
        ));
    }

    /**
     * @return UserType
     */
    public static function user()
    {
        return self::$user ?: (self::$user = new UserType());
    }

    public static function userPagination()
    {
        return self::$userPagination ?: (self::$userPagination = new PaginationType(new UserType(true)));
    }


    /**
     * @return UserMutationType
     */
    public static function userMutation()
    {
        return self::$userMutation ?: (self::$userMutation = new UserMutationType());
    }

    public static function companyEntity()
    {
        return self::$companyEntity ?: (self::$companyEntity = new CompanyEntityType());
    }

    public static function companyEntityPagination()
    {
        return self::$companyEntityPagination ?: (self::$companyEntityPagination = new PaginationType(
            new CompanyEntityType(true)
        ));
    }

    public static function companyEntityStatus()
    {
        return self::$companyEntityStatus ?: (self::$companyEntityStatus = new CompanyEntityStatusType());
    }

    public static function companyEntityLocation()
    {
        return self::$companyEntityLocation ?: (self::$companyEntityLocation = new CompanyEntityLocationType());
    }

    public static function userLocation()
    {
        return self::$userLocation ?: (self::$userLocation = new UserLocationType());
    }

    public static function userCostCenters()
    {
        return self::$userCostCenters ?: (self::$userCostCenters = new UserCostCentersType());
    }

    public static function userCompany()
    {
        return self::$userCompany ?: (self::$userCompany = new UserCompanyType());
    }

    public static function budget()
    {
        return self::$budget ?: (self::$budget = new BudgetType());
    }

    public static function budgetPagination()
    {
        return self::$budgetPagination ?: (self::$budgetPagination = new PaginationType(new BudgetType(true)));
    }

    public static function budgetLocation()
    {
        return self::$budgetLocation ?: (self::$budgetLocation = new BudgetLocationType());
    }

    public static function budgetCostCenters()
    {
        return self::$budgetCostCenters ?: (self::$budgetCostCenters = new BudgetCostCentersType());
    }

    public static function coordination()
    {
        return self::$coordination ?: (self::$coordination = new CoordinationType());
    }

    public static function coordinationUsers()
    {
        return self::$coordinationUsers ?: (self::$coordinationUsers = new CoordinationUsersType());
    }

    public static function coordinationRoles()
    {
        return self::$coordinationRoles ?: (self::$coordinationRoles = new CoordinationRolesType());
    }

    public static function supplier()
    {
        return self::$supplier ?: (self::$supplier = new SupplierType());
    }

    public static function supplierPagination()
    {
        return self::$supplierPagination ?: (self::$supplierPagination = new PaginationType(new SupplierType(true)));
    }

    public static function contract()
    {
        return self::$contract ?: (self::$contract = new ContractType());
    }

    public static function contractPagination()
    {
        return self::$contractPagination ?: (self::$contractPagination = new PaginationType(new ContractType(true)));
    }

    public static function contractCostCenter()
    {
        return self::$contractCostCenter ?: (self::$contractCostCenter = new ContractCostCenterType());
    }

    public static function contractLocation()
    {
        return self::$contractLocation ?: (self::$contractLocation = new ContractLocationType());
    }

    public static function order()
    {
        return self::$order ?: (self::$order = new OrderType());
    }

    public static function orderPagination()
    {
        return self::$orderPagination ?: (self::$orderPagination = new PaginationType(new OrderType(true)));
    }

    public static function orderCoordination()
    {
        return self::$orderCoordination ?: (self::$orderCoordination = new OrderCoordinationType());
    }

    public static function status()
    {
        return self::$status ?: (self::$status = new StatusType());
    }

    public static function orderComment()
    {
        return self::$orderComment ?: (self::$orderComment = new OrderCommentType());
    }

    public static function orderFile()
    {
        return self::$orderFile ?: (self::$orderFile = new OrderFileType());
    }

    public static function orderProduct()
    {
        return self::$orderProduct ?: (self::$orderProduct = new OrderProductType());
    }

    public static function orderProductHistory()
    {
        return self::$orderProductHistory ?: (self::$orderProductHistory = new OrderProductHistoryType());
    }

    public static function orderProductStatus()
    {
        return self::$orderProductStatus ?: (self::$orderProductStatus = new OrderProductStatusType());
    }

    public static function orderProductPagination()
    {
        return self::$orderProductPagination ?: (self::$orderProductPagination = new PaginationType(
            new OrderProductType(true)
        ));
    }

    public static function unit()
    {
        return self::$unit ?: (self::$unit = new UnitType());
    }

    public static function product()
    {
        return self::$product ?: (self::$product = new ProductType());
    }

    public static function productPagination()
    {
        return self::$productPagination ?: (self::$productPagination = new PaginationType(new ProductType(true)));
    }

    public static function category()
    {
        return self::$category ?: (self::$category = new CategoryType());
    }

    public static function catalogLimitation()
    {
        return self::$catalogLimitation ?: (self::$catalogLimitation = new CatalogLimitationType());
    }

    public static function catalogLimitationPagination()
    {
        return self::$catalogLimitationPagination ?: (self::$catalogLimitationPagination = new PaginationType(
            new CatalogLimitationType(true)
        ));
    }

    public static function taxOption()
    {
        return self::$taxOption ?: (self::$taxOption = new TaxOptionType());
    }

    public static function specificationLocation()
    {
        return self::$specificationLocation ?: (self::$specificationLocation = new SpecificationLocationType());
    }

    public static function specification()
    {
        return self::$specification ?: (self::$specification = new SpecificationType());
    }

    public static function specificationPagination()
    {
        return self::$specificationPagination ?: (self::$specificationPagination = new PaginationType(
            new SpecificationType(true)
        ));
    }

    public static function specificationProduct()
    {
        return self::$specificationProduct ?: (self::$specificationProduct = new SpecificationProductType());
    }

    public static function specificationProductPagination()
    {
        return self::$specificationProductPagination ?: (self::$specificationProductPagination = new PaginationType(
            new SpecificationProductType(true)
        ));
    }

    public static function text()
    {
        return self::$text ?: (self::$text = new TextType());
    }

    public static function chatMessages()
    {
        return self::$chatMessages ?: (self::$chatMessages = new MessageType());
    }

    public static function unreadMessages()
    {
        return self::$unreadMessages ?: (self::$unreadMessages = new UnreadMessagesType());
    }

    public static function notification()
    {
        return self::$notification ?: (self::$notification = new NotificationType());
    }

    public static function chatInfo()
    {
        return self::$chatInfo ?: (self::$chatInfo = new ChatInfoType());
    }

    public static function countNotification()
    {
        return self::$countNotification ?: (self::$countNotification = new CountNotificationType());
    }

    public static function orderDashboard()
    {
        return self::$orderDashboard ?: (self::$orderDashboard = new OrderDashboardType());
    }

    public static function orderDashboardPagination()
    {
        return self::$orderDashboardPagination ?: (self::$orderDashboardPagination = new PaginationType(
            new OrderDashboardType(true)
        ));
    }

    public static function companySupplier()
    {
        return self::$companySupplier ?: (self::$companySupplier = new CompanySupplierType());
    }

    public static function notificationPagination()
    {
        return self::$notificationPagination ?: (self::$notificationPagination = new PaginationType(
            new NotificationType(true)
        ));
    }

    public static function history()
    {
        return self::$history ?: (self::$history = new HistoryType());
    }

    public static function cart()
    {
        return self::$cart ?: (self::$cart = new CartType());
    }

    public static function cartProduct()
    {
        return self::$cartProduct ?: (self::$cartProduct = new CartProductType());
    }

    public static function cartProductPagination()
    {
        return self::$cartProductPagination ?: (self::$cartProductPagination = new PaginationType(
            new CartProductType(true)
        ));
    }

    public static function cartProductHistory()
    {
        return self::$cartProductHistory ?: (self::$cartProductHistory = new CartProductHistoryType());
    }

    public static function cartProductHistoryPagination()
    {
        return self::$cartProductHistoryPagination ?: (self::$cartProductHistoryPagination = new PaginationType(
            new CartProductHistoryType(true)
        ));
    }

    public static function oneSCheck()
    {
        return self::$oneSCheck ?: (self::$oneSCheck = new OneSCheckType());
    }

    public static function managementProduct()
    {
        return self::$managementProduct ?: (self::$managementProduct = new ManagementProductType());
    }

    public static function managementProductPagination()
    {
        return self::$managementProductPagination ?: (self::$managementProductPagination = new PaginationType(
            new ManagementProductType(true)
        ));
    }

    public static function attribute()
    {
        return self::$attribute ?: (self::$attribute = new AttributeType());
    }

    public static function attributeValue()
    {
        return self::$attributeValue ?: (self::$attributeValue = new AttributeValueType());
    }

    public static function vendor()
    {
        return self::$vendor ?: (self::$vendor = new VendorType());
    }

    public static function brand()
    {
        return self::$brand ?: (self::$brand = new BrandType());
    }

    public static function uktzed()
    {
        return self::$uktzed ?: (self::$uktzed = new UktzedType());
    }

    public static function adminEmail()
    {
        return self::$adminEmail ?: (self::$adminEmail = new AdminEmailType());
    }

    public static function adminEmailPagination()
    {
        return self::$adminEmailPagination ?: (self::$adminEmailPagination = new PaginationType(
            new AdminEmailType(true)
        ));
    }

    public static function image()
    {
        return self::$image ?: (self::$image = new ImageType());
    }
}
