<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 08.06.2019
 * Time: 15:25
 */

namespace api\schema;

use api\schema\queries\AccountQuery;
use api\schema\queries\AuthItemListQuery;
use api\schema\queries\AuthItemParentQuery;
use api\schema\queries\AuthItemQuery;
use api\schema\queries\BudgetInfoQuery;
use api\schema\queries\BudgetQuery;
use api\schema\queries\CatalogLimitationQuery;
use api\schema\queries\CategoryQuery;
use api\schema\queries\ChatInfoQuery;
use api\schema\queries\CompanyEntityQuery;
use api\schema\queries\CompanyEntityStatusQuery;
use api\schema\queries\CompanyInfoQuery;
use api\schema\queries\CompanyQuery;
use api\schema\queries\CompanyStatusQuery;
use api\schema\queries\ContractQuery;
use api\schema\queries\CoordinationQuery;
use api\schema\queries\CostCenterQuery;
use api\schema\queries\CurrencyQuery;
use api\schema\queries\LocationQuery;
use api\schema\queries\ManagerQuery;
use api\schema\queries\MessageQuery;
use api\schema\queries\MyCompanyQuery;
use api\schema\queries\OneSpecificationQuery;
use api\schema\queries\OrderCommentQuery;
use api\schema\queries\OrderCoordinationQuery;
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
use api\schema\queries\UnreadMessageQuery;
use api\schema\queries\UserQuery;
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
 * Class PublicQueryType
 * @package api\schema
 */
class PublicQueryType extends ObjectType
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
                    'message' => (new MessageQuery())->getData(),
                    'unreadMessage' => (new UnreadMessageQuery())->getData(),
                    'chatInfo' => (new ChatInfoQuery())->getData()
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
