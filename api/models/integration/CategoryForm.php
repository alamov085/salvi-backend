<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 05.02.2020
 * Time: 15:13
 */

namespace api\models\integration;

use api\job\UploadPhotoCategory;
use common\models\Attribute;
use common\models\AttributeValue;
use common\models\Category;
use Yii;
use yii\base\Model;
use yii\helpers\Inflector;

/**
 * Class ProductForm
 * @package api\models\integration
 */
class CategoryForm extends Model
{
    public $uuid;
    public $Name;
    public $uuid_parent;
    public $sort;
    public $color;
    public $statusk;
    public $views;
    public $has_child;
    public $dop_propertis;
    public $URL;
    public $additional_category;

    public $image;


    public function rules()
    {
        return [
            [['uuid', 'Name', 'uuid_parent', 'URL'], 'required'],
            [['color','additional_category'],'string'],
            [['has_child', 'statusk', 'sort'], 'integer'],
            [['image','img_path'], 'safe'],
            ['dop_propertis', 'checkIsArray'],
//            ['skipOnEmpty' => false, 'skipOnError' => false]
        ];
    }
    public function checkIsArray(){
        if(!is_array($this->dop_propertis)){
            $this->addError('dop_propertis','X is not array!');
        }
    }

    public function modelValidate($attribute)
    {
        $modelName = 'api\\models\\integration\\' . Inflector::camelize($attribute . '_form');
        $model = new $modelName();
        if ($model->load($this->$attribute, '') && !$model->validate()) {
            $this->addErrors([$attribute => $model->errors]);
            return false;
        }
    }

    public function save()
    {
        $db = \Yii::$app->db;
        $transaction = $db->beginTransaction();

        $category = Category::find()
            ->where(['uuid' => $this->uuid])
            ->one();

        if (!$category) {
            $category = new Category([
                'uuid' => $this->uuid
            ]);
        }

        $category->name = $this->Name;
        $category->color = $this->color;
        $category->parent_uuid = $this->uuid_parent == '00000000-0000-0000-0000-000000000000' ? null : $this->uuid_parent;
        $category->delete = (int)$this->statusk == 1 ? 0 : 1;
        $category->has_child = $this->has_child;
        $category->sort = $this->sort;
        $category->additional_category = ($this->additional_category == 'true') ? 1 : 0;


        if(in_array($this->uuid,(new ProductForm())->listHideUuidCategory))
        {
            $category->delete = 1;
        }

        $this->URL = strtolower($this->URL);
        $this->URL = str_replace(array(' ', '<', '>', '&', '{', '}', '*', ')', '(', '_'), array('-'), $this->URL);

        $category->code = $this->URL;

        if (!empty($category->parent_uuid)) {
            $parentCategory = Category::find()
                ->where(['uuid' => $this->uuid_parent])
                ->one();

            if ($parentCategory) {
                $category->parent_id = $parentCategory->id;
            }
        }

        if ($category->save())
        {
            $this->saveAttributes($category);

            if ($this->image)
            {
                Yii::$app->queue->push(new UploadPhotoCategory([
                    'category' => $category,
                    'image'  => $this->image
                ]));
            }
//            Yii::$app->queue->push(new UploadPhoto([
//                'product' => $category,
//                'images'  => $this->image
//            ]));

            $transaction->commit();
            return true;
        }
        $transaction->rollBack();
        return false;

    }

    private function saveAttributes($category) {

        if(isset($this->dop_propertis) && !empty($this->dop_propertis)) {
            foreach ($this->dop_propertis as $dop_property) {
                $attribute = Attribute::find()
                    ->where([
                        'name' => $dop_property['propert'],
                        'category_uuid' => $category->uuid
                    ])
                    ->one();

                if(!$attribute) {
                    $attribute = new Attribute([
                        'name' => $dop_property['propert'],
                        'category_uuid' => $category->uuid
                    ]);
                }

                $attribute->category_id = $category->id;

                if($attribute->save()) {
                    if(isset($dop_property['values']) && !empty($dop_property['values'])) {
                        foreach ($dop_property['values'] as $key => $value) {
                            $attributeValue = AttributeValue::find()
                                ->where([
                                    'attribute_id' => $attribute->id,
                                    'name' => $value
                                ])->one();

                            if(!$attributeValue) {
                                $attributeValue = new AttributeValue([
                                    'attribute_id' => $attribute->id,
                                    'name' => $value
                                ]);
                            }

                            $attributeValue->save();
                        }
                    }
                }
            }
        }
    }
}
