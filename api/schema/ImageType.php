<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 11.02.2020
 * Time: 18:47
 */

namespace api\schema;

use common\models\ProductPhoto;
use GraphQL\Type\Definition\Type;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * Class ImageType
 * @package api\schema
 */
class ImageType extends BasicType
{
    public function __construct($pagination = false)
    {
        $config = [
            'fields' => function () {
                return [
                    'id' => Type::int(),
                    'name' => Type::string(),
                    'url' => [
                        self::FIELD_TYPE => Type::string(),
                        self::FIELD_RESOLVE => function (ProductPhoto $productPhoto) {
                            return Yii::$app->urlManager->createAbsoluteUrl(['/product/' . $productPhoto->name]);
                        }
                    ]
                ];
            }
        ];
        parent::__construct($config, $pagination);
    }
}
