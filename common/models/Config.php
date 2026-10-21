<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "config".
 *
 * @property int $id
 * @property int $params
 * @property string $value
 */
class Config extends \yii\db\ActiveRecord
{
    const MARKUP = 1;
    const ADMIN_TOTAL_ORDERS = 2;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'config';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['params'], 'integer'],
            [['value'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'params' => 'Params',
            'value' => 'Value',
        ];
    }

    /**
     * @param $param
     * @return bool|mixed|string
     */
    public static function getValue($param)
    {
        $cache = Yii::$app->cache;
        $key = __CLASS__ . '_' . $param;
        if ($cache->exists($key)) {
            return $cache->get($key);
        } else {
            $model = self::find()
                ->where(['params' => $param])
                ->one();
            if ($model) {
                $cache->set($key, $model->value, 3600);
                return $model->value;
            }
            return false;
        }
    }

    /**
     * @param $param
     * @param $value
     * @return bool
     */
    public static function setValue($param, $value)
    {
        $model = self::find()
            ->where(['params' => $param])
            ->one();
        if (!$model) {
            $model = new self();
            $model->params = $param;
        }
        $model->value = $value;
        $cache = Yii::$app->cache;
        $key = __CLASS__ . '_' . $param;
        $cache->delete($key);
        return $model->save();
    }
}
