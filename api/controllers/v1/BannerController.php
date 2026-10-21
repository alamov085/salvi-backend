<?php


namespace api\controllers\v1;

use api\controllers\RestController;
use api\models\forms\BannerForm;
use common\models\Banner;
use common\models\User;
use api\schema\PaginationType;
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\web\UploadedFile;
use yii\caching\TagDependency;


/**
 * Class BannerController
 * @package api\controllers\v1
 */
class BannerController extends RestController
{
    const CACHE_KEY = 'GetBanners';

    /**
     * @return BannerForm\|array
     * @throws Throwable
     * @throws InvalidConfigException
     */
    public function actionCreate()
    {
        $model = new BannerForm();

        if ($model->load(Yii::$app->getRequest()->getBodyParams(), '') && $model->validate())
        {
            $result = $model->create();

            if(!is_array($result))
            {
                TagDependency::invalidate(
                    Yii::$app->cache,
                    self::CACHE_KEY
                );

                return $this->successResponse();
            }
            else
            {
                return $this->failResponse($result);
            }
        }

        $model->validate();

        return $model;

    }

    /**
     * @return BannerForm|array
     * @throws Throwable
     * @throws InvalidConfigException
     */
    public function actionUpdate()
    {
            $model = new BannerForm();

            if ($model->load(Yii::$app->getRequest()->getBodyParams(), '') && $model->validate() && $model->update())
            {
                TagDependency::invalidate(
                    Yii::$app->cache,
                    self::CACHE_KEY
                );

                return $this->successResponse();
            }

            $model->validate();
            return $model;

    }

    /**
     * @return array
     * @throws Throwable
     */
    public function actionDelete()
    {
        $id = ArrayHelper::getValue($this->post, 'id', 0);

        if ($id > 0 && is_numeric($id))
        {
            $model = Banner::find()
                ->where(['id' => $id])
                ->one();
            if ($model)
            {
                if ($model->delete())
                {
                    TagDependency::invalidate(
                        Yii::$app->cache,
                        self::CACHE_KEY
                    );

                    return $this->successResponse();
                }
            }
        }
        return $this->failResponse();
    }

    public function actionUpdatePhoto()
    {
        $user = Yii::$app->user->getIdentity();

        $id = ArrayHelper::getValue($this->post, 'id', 0);

        $query = Banner::find()->where(['id' => $id]);

        $model = $query->one();


        $file = UploadedFile::getInstanceByName('img');

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

        TagDependency::invalidate(
            Yii::$app->cache,
            self::CACHE_KEY
        );

        if ($model)
        {
            $oldPath = $model->img_path;

            $model->setAttributes([
                'user_update' => $user->id,
                'img_path' => $fileName,
            ]);

            $result = $model->save();

            if ($result)
            {
                $file = Yii::getAlias('@api/web/sliders/' .$oldPath);

                if (file_exists($file))
                {
                    unlink($file);
                }

            }

            return $this->successResponse();
        }

        return $this->failResponse();
    }

    public function actionGetBanners()
    {
        $result = ['banners' => []];

        if(Yii::$app->user->can('catalogShowBtn') || Yii::$app->user->can('orderShowBtn'))
        {
            $query = Banner::find()->where(['active' => 1])->orderBy(['sort' => SORT_ASC]);

            $hashSql = md5($query->createCommand()->getRawSql());

            $bannersCache = Yii::$app->cache->get('GetBanners_' . $hashSql);

            if ( !$bannersCache)
            {
                $bannersCache['banners'] = ArrayHelper::toArray($query->all());

                foreach ($bannersCache['banners'] as &$banner)
                {
                    $path = Yii::$app->urlManager->createAbsoluteUrl(['/sliders/' . $banner['img_path']]);

                    $banner['img_path'] = $path;
                }

                unset($banner);

                Yii::$app->cache->set('GetBanners_' . $hashSql, $bannersCache, 260000,new TagDependency(['tags'=>[self::CACHE_KEY]]));
            }

            $result = $bannersCache;
        }

        return $this->successResponse($result);
    }

    public function actionGetBannersAdm()
    {
        $result = [];

        $pagination = Yii::$app->getRequest()->getBodyParam('pagination');

        $args = [
            'page' => $pagination['page'],
            'perPage' => $pagination['rowsPerPage'],
            'orderBy' => $pagination['sortBy'],
            'order' => mb_strtoupper($pagination['order']),
        ];

        $arguments = PaginationType::arguments($args, array_keys(Banner::getTableSchema()->columns));

        $query = Banner::find();

        $query->offset($arguments['perPage'] * $arguments['page'])->limit($arguments['perPage']);

        $query->orderBy([
            $arguments['orderBy'] => $args['orderBy'],
        ]);

        $result['banners'] = ArrayHelper::toArray($query->all());

        foreach ($result['banners'] as &$banner)
        {
            $path = Yii::$app->urlManager->createAbsoluteUrl(['/sliders/' . $banner['img_path']]);

            $banner['img_path'] = $path;
        }

        unset($banner);

        return $this->successResponse($result);
    }
}