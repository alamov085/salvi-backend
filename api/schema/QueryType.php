<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 08.06.2019
 * Time: 15:25
 */

namespace api\schema;

use api\schema\queries\AccountQuery;
use api\schema\queries\AdminEmailPaginationQuery;
use api\schema\queries\AllProductQuery;
use api\schema\queries\AttributeQuery;
use api\schema\queries\AuthItemListQuery;
use api\schema\queries\AuthItemParentQuery;
use api\schema\queries\AuthItemQuery;
use api\schema\queries\BrandQuery;
use api\schema\queries\BudgetInfoQuery;
use api\schema\queries\BudgetQuery;
use api\schema\queries\CartProductHistoryQuery;
use api\schema\queries\CartProductQuery;
use api\schema\queries\CartQuery;
use api\schema\queries\CatalogLimitationQuery;
use api\schema\queries\CategoryQuery;
use api\schema\queries\CategoryTypesQuery;
use api\schema\queries\ChatInfoQuery;
use api\schema\queries\CompanyEntityQuery;
use api\schema\queries\CompanyEntityStatusQuery;
use api\schema\queries\CompanyInfoQuery;
use api\schema\queries\CompanyQuery;
use api\schema\queries\CompanyStatusQuery;
use api\schema\queries\ContractQuery;
use api\schema\queries\CoordinationQuery;
use api\schema\queries\CostCenterQuery;
use api\schema\queries\CountNotificationQuery;
use api\schema\queries\CurrencyQuery;
use api\schema\queries\LocationListQuery;
use api\schema\queries\LocationQuery;
use api\schema\queries\ManagementCategoryQuery;
use api\schema\queries\ManagementProductQuery;
use api\schema\queries\ManagerQuery;
use api\schema\queries\MessageQuery;
use api\schema\queries\MyCompanyQuery;
use api\schema\queries\NotificationQuery;
use api\schema\queries\OneSCheckQuery;
use api\schema\queries\OneSpecificationQuery;
use api\schema\queries\OrderCommentQuery;
use api\schema\queries\OrderCoordinationQuery;
use api\schema\queries\OrderDashboardQuery;
use api\schema\queries\OrderFileQuery;
use api\schema\queries\OrderInfoQuery;
use api\schema\queries\OrderProductQuery;
use api\schema\queries\OrderQuery;
use api\schema\queries\OrderStatusQuery;
use api\schema\queries\ProductQuery;
use api\schema\queries\SpecificationProductQuery;
use api\schema\queries\SpecificationQuery;
use api\schema\queries\SupplierQuery;
use api\schema\queries\TaxOptionQuery;
use api\schema\queries\UktzedQuery;
use api\schema\queries\UktzedTypesQuery;
use api\schema\queries\UnitQuery;
use api\schema\queries\UnreadMessageQuery;
use api\schema\queries\UserQuery;
use api\schema\queries\VatQuery;
use api\schema\queries\VendorQuery;
use common\models\AuthItem;
use common\models\AuthItemParent;
use common\models\Company;
use common\models\CostCenter;
use common\models\Currency;
use common\models\Locations;
use common\models\User;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * Class QueryType
 * @package api\schema
 */
class QueryType extends ObjectType
{
    const ORDER_BY = 'id';
    const ORDER = 'DESC';

    /**
     * QueryType constructor.
     */
    public function __construct()
    {
        $config = [
            'fields' => function () {
                return [
                    'account' => (new AccountQuery())->getData(),
                    'user' => (new UserQuery())->getData(),
                    'currency' => (new CurrencyQuery())->getData(),
                    'location' => (new LocationQuery())->getData(),
                    'costCenter' => (new CostCenterQuery())->getData(),
                    'authItem' => (new AuthItemQuery())->getData(),
                    'company' => (new CompanyQuery())->getData(),
                    'companyStatus' => (new CompanyStatusQuery())->getData(),
                    'authItemList' => (new AuthItemListQuery())->getData(),
                    'authItemParent' => (new AuthItemParentQuery())->getData(),
                    'companyEntity' => (new CompanyEntityQuery())->getData(),
                    'companyEntityStatus' => (new CompanyEntityStatusQuery())->getData(),
                    'budget' => (new BudgetQuery())->getData(),
                    'budgetInfo' => (new BudgetInfoQuery())->getData(),
                    'manager' => (new ManagerQuery())->getData(),
                    'coordination' => (new CoordinationQuery())->getData(),
                    'supplier' => (new SupplierQuery())->getData(),
                    'contract' => (new ContractQuery())->getData(),
                    'order' => (new OrderQuery())->getData(),
                    'orderStatus' => (new OrderStatusQuery())->getData(),
                    'orderInfo' => (new OrderInfoQuery())->getData(),
                    'orderComment' => (new OrderCommentQuery())->getData(),
                    'orderCoordination' => (new OrderCoordinationQuery())->getData(),
                    'orderFile' => (new OrderFileQuery())->getData(),
                    'orderProduct' => (new OrderProductQuery())->getData(),
                    'product' => (new ProductQuery())->getData(),
                    'category' => (new CategoryQuery())->getData(),
                    'myCompany' => (new MyCompanyQuery())->getData(),
                    'catalogLimitation' => (new CatalogLimitationQuery())->getData(),
                    'taxOption' => (new TaxOptionQuery())->getData(),
                    'specification' => (new SpecificationQuery())->getData(),
                    'specificationProduct' => (new SpecificationProductQuery())->getData(),
                    'oneSpecification' => (new OneSpecificationQuery())->getData(),
                    'message' => (new MessageQuery())->getData(),
                    'unreadMessage' => (new UnreadMessageQuery())->getData(),
                    'notification' => (new NotificationQuery())->getData(),
                    'chatInfo' => (new ChatInfoQuery())->getData(),
                    'countNotification' => (new CountNotificationQuery())->getData(),
                    'orderDashboard' => (new OrderDashboardQuery())->getData(),
                    'locationList' => (new LocationListQuery())->getData(),
                    'allProduct' => (new AllProductQuery())->getData(),
                    'cartProduct' => (new CartProductQuery())->getData(),
                    'cartProductHistory' => (new CartProductHistoryQuery())->getData(),
                    'cart' => (new CartQuery())->getData(),
                    'oneSCheck' => (new OneSCheckQuery())->getData(),
                    'managementProduct' => (new ManagementProductQuery())->getData(),
                    'attribute' => (new AttributeQuery())->getData(),
                    'unit' => (new UnitQuery())->getData(),
                    'managementCategory' => (new ManagementCategoryQuery())->getData(),
                    'brand' => (new BrandQuery())->getData(),
                    'vendor' => (new VendorQuery())->getData(),
                    'uktzed' => (new UktzedQuery())->getData(),
                    'uktzedTypes' => (new UktzedTypesQuery())->getData(),
                    'categoryTypes' => (new CategoryTypesQuery())->getData(),
                    'adminEmail' => (new AdminEmailPaginationQuery())->getData(),
                    'vat'=>(new VatQuery())->getData()
                ];
            }
        ];
        parent::__construct($config);
    }

    /**
     * @return array
     */
    public static function accessDeniedPagination()
    {
        return [
            'model' => [],
            'total' => 0,
        ];
    }
}
