<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 08.06.2019
 * Time: 15:51
 */

namespace api\controllers\v1;

use api\controllers\RestController;
use api\schema\Types;
use GraphQL\Executor\Promise\Promise;
use GraphQL\GraphQL;
use GraphQL\Type\Schema;
use Yii;
use yii\base\InvalidArgumentException;
use yii\filters\auth\HttpBearerAuth;
use yii\helpers\Json;

/**
 * Class PublicGraphqlController
 * @package api\controllers\v1
 */
class PublicGraphqlController extends \api\controllers\PublicController
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::class,
            'optional' => ['index']
        ];
        return $behaviors;
    }

    public $modelClass = '';

    /**
     * @inheritdoc
     */
//    protected function verbs()
//    {
//        return [
//            'index' => ['POST', 'OPTIONS'],
//        ];
//    }

    /**
     * @return array
     */
    public function actions()
    {
        return [];
    }

    /**
     * @return Promise|mixed[]
     */
    public function actionIndex()
    {
        $query = Yii::$app->request->get('query', Yii::$app->request->post('query'));
        $variables = Yii::$app->request->get('variables', Yii::$app->request->post('variables'));
        $operation = Yii::$app->request->get('operation', Yii::$app->request->post('operation', null));

        if (empty($query)) {
            $rawInput = file_get_contents('php://input');
            $input = json_decode($rawInput, true);
            $query = $input['query'];
            $variables = isset($input['variables']) ? $input['variables'] : [];
            $operation = isset($input['operation']) ? $input['operation'] : null;
        }

        if (!empty($variables) && !is_array($variables)) {
            try {
                $variables = Json::decode($variables);
            } catch (InvalidArgumentException $e) {
                $variables = null;
            }
        }

        $schema = new Schema([
            'query' => Types::queryPublic()
        ]);

        $result = GraphQL::executeQuery(
            $schema,
            $query,
            null,
            null,
            empty($variables) ? null : $variables,
            empty($operation) ? null : $operation
        )->toArray();

        return $result;
    }
}
