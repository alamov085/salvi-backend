<?php
namespace api\models\forms;


use common\models\Banner;
use common\models\User;
use Throwable;
use Yii;
use yii\base\Model;
use yii\web\UploadedFile;

/**
 * Class BannerForm
 * @package api\models\forms
 */
class BannerForm extends Model
{
    public $id;
    public $active;
    public $sort;
    public $img_path;
    public $name;
    public $url;
    public $text;
    public $time_swap;
    public $user_create;
    public $user_update;
    public $created_at;
    public $updated_at;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['name', 'url', 'text','time_swap','active','sort'], 'required'],
            [['active'], 'boolean'],
            [['id','time_swap','sort'], 'integer'],
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

        $model = new Banner();

        $file = UploadedFile::getInstanceByName('img');

        $uploadDir = Yii::getAlias('@api/web/sliders/');
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileName = uniqid('slider_') . '.' . $file->extension;

        if (!$file->saveAs($uploadDir . $fileName))
        {
            return ['success' => false, 'error' => 'Cannot save file'];
        }

        $model->setAttributes([
            'user_create' => $user->id,
            'active' => $this->active,
            'sort' => $this->sort,
            'img_path' => $fileName,
            'name' => $this->name,
            'url' => $this->url,
            'text' => $this->text,
            'time_swap' => $this->time_swap,
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
            'active' => 'Active',
            'sort' => 'Sort',
            'name' => 'Name',
            'img_path' => 'img Path',
            'url' => 'url',
            'text' => 'text',
            'time_swap' => 'time Swap',
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

        $query = Banner::find()->where(['id' => $this->id]);
	    
        $model = $query->one();

        $file = UploadedFile::getInstanceByName('img');

        $isNeedUpdateFile = false;

        $fileName = '';

        $oldPath = '';

        if(!is_null($file))
        {
            $isNeedUpdateFile = true;

            $uploadDir = Yii::getAlias('@api/web/sliders/');

            if (!is_dir($uploadDir))
            {
                mkdir($uploadDir, 0777, true);
            }

            $fileName = uniqid('slider_') . '.' . $file->extension;

            if (!$file->saveAs($uploadDir . $fileName))
            {
                return $this->failResponse(['success' => false, 'error' => 'Cannot save file']);
            }
        }

        if ($model)
        {
           $fields = [
               'user_update' => $user->id,
               'active' => $this->active,
               'sort' => $this->sort,
               'name' => $this->name,
               'url' => $this->url,
               'text' => $this->text,
               'time_swap' => $this->time_swap,
           ];

            if($isNeedUpdateFile)
            {
                $oldPath = $model->img_path;

                $fields['img_path'] = $fileName;
            }

            $model->setAttributes($fields);

            $result = $model->save();

            if ($result)
            {
                if(!empty($oldPath))
                {
                    $file = Yii::getAlias('@api/web/sliders/' .$oldPath);

                    if (file_exists($file))
                    {
                        unlink($file);
                    }
                }
            }

            return $result;
        }

        return false;
    }
}
