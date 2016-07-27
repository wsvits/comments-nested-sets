<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;

class ActiveController extends Controller
{
    /**
     * Init controller
     */
    public function init()
    {
        parent::init();
        if (Yii::$app->request->isAjax) {
            \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        }
    }

    /**
     * Get the body or a request as array
     *
     * @return array|null Parsed data or null if the data supplied is not a valid json
     */
    public function getRawData()
    {
        $json = Yii::$app->request->rawBody;

        if ($json) {
            return json_decode($json, true);
        } else {
            return null;
        }
    }
}