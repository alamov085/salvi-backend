<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 02.08.2019
 * Time: 16:25
 */

namespace api\schema\queries;

use api\schema\PaginationType;
use api\schema\Types;
use common\models\Specification;
use common\models\User;
use GraphQL\Type\Definition\Type;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * Class OneSpecificationQuery
 * @package api\schema\queries
 */
class OneSpecificationQuery extends BasicQuery
{

    public function type()
    {
        return Types::specification();
    }

    public function args()
    {
        return ['id' => Type::nonNull(Type::int())];
    }

    public function resolve()
    {
        return function ($root, $args) {
            if (Yii::$app->user->can('viewSpecification')) {
                /** @var  $user User */
                $user = Yii::$app->user->getIdentity();
                $query = Specification::find()
                    ->joinWith(['locations', 'contract', 'company', 'locationsModel'])
                    ->where(['specification.company_id' => $user->company_id])
                    ->andWhere(['specification.id' => $args['id']]);
                return $query->one();
            }
            return self::accessDeniedPagination();
        };
    }
}
