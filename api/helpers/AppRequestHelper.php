<?php
namespace api\helpers;

use Yii;

class AppRequestHelper
{
    private $request  = null;

    public function __construct ()
    {
        $this->request = Yii::$app->getRequest();
    }

    public function getFromPost ($key,$default = null)
    {
        return $this->request->post($key,$default);
    }

    public function getFromGet ($key,$default = null)
    {
        return $this->request->get($key,$default);
    }



}