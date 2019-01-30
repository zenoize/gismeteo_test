<?php

namespace app\controllers;

use app\models\Gismeteo;
use yii\rest\Controller;

class ApiController extends Controller
{
    public function actionGetTemperature()
    {
        $userIp = \Yii::$app->request->getUserIP();

        $result = Gismeteo::getTemperatureByIP($userIp);
        \Yii::info([
            'user_ip' => $userIp,
            'responce' => \Yii::$app->request->absoluteUrl,
            'status' => $result != false,
        ], 'api');

        return $this->asJson($result);
    }

    public function actionGetWeekends()
    {
        $data = \Yii::$app->request->get();

        if (!isset($data['lat']) || !isset($data['lon'])) {
            return false;
        }

        $result = Gismeteo::getTemperatureOnWeekendsByCoors($data['lat'], $data['lon']);

        \Yii::info([
            'user_ip' => \Yii::$app->request->getUserIP(),
            'response' => \Yii::$app->request->absoluteUrl,
            'status' => $result != false,
        ], 'api');

        return $this->asJson($result);
    }

    public function actionGetNearest()
    {
        $data = \Yii::$app->request->get();

        if (!isset($data['lat']) || !isset($data['lon'])) {
            return false;
        }

        $result = Gismeteo::getTemperatureInNearestPlacesByCoords($data['lat'], $data['lon']);

        \Yii::info([
            'user_ip' => \Yii::$app->request->getUserIP(),
            'response' => \Yii::$app->request->absoluteUrl,
            'status' => $result != false,
        ], 'api');

        return $this->asJson($result);
    }
}