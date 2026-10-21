<?php
namespace api\models\forms;


use common\models\ElasticSearchHistoryProsses;
use common\models\User;
use Throwable;
use Yii;
use yii\base\Model;
use yii\web\UploadedFile;

class ElasticSearchHistoryProssesForm extends Model
{
    public $id;
    public $query;
    public $user_create;
    public $user_update;
    public $created_at;
    public $updated_at;
    public $type;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['query','type'], 'required'],
            [['id'], 'integer'],
        ];
    }

    public function validate($attributeNames = null, $clearErrors = true)
    {
        return parent::validate($attributeNames, $clearErrors);
    }

    /**
     * @throws Throwable
     */
    public function create()
    {
        /** @var  $user User */
        $user = Yii::$app->user->getIdentity();

        $model = new ElasticSearchHistoryProsses();

        $model->setAttributes([
            'user_create' => $user->id,
//            'type' => $this->type,
            'query' => $this->query,
        ]);

        $result = $model->save();

        if ($result)
        {

        }
        return $result;
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'query' => 'query',
            'type' => 'type',
            'user_create' => 'user Create',
            'user_update' => 'user Update',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return bool
     * @throws Throwable
     */
    public function update()
    {
        /** @var  $user User */
        $user = Yii::$app->user->getIdentity();

        $query = ElasticSearchHistoryProsses::find()->where(['id' => $this->id]);

        $model = $query->one();

        if ($model)
        {
            $model->setAttributes([
                'user_update' => $user->id,
                'query' => $this->query,
            ]);

            $result = $model->save();

            if ($result)
            {


            }

            return $result;
        }

        return false;
    }
}