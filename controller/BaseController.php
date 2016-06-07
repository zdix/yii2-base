<?php

namespace dix\base\controller;

use dix\base\component\DXUtil;
use dix\base\component\Redis;
use Yii;
use yii\web\Controller;

class BaseController extends Controller
{
    public $redis;

    public function beforeAction($action)
    {
        $ok = parent::beforeAction($action);

        header("Access-Control-Allow-Origin: *");

        return $ok;
    }

    public function finish($data)
    {
        header('Content-type:application/json;charset=UTF-8');

    	die(DXUtil::jsonEncode($data));
    }

    public function finishData($data)
    {
        $data['code'] = 0;
        if ($data)
        {
            $data['data'] = $data;
        }
        $this->finish($data);
    }

    public function finishError($code, $message, $extra = [])
    {
        $data['code'] = intval($code);
        $data['message'] = $message;
        if (is_array($extra))
        {
            $data = array_merge($data, $extra);
        }

        $this->finish($data);
    }

    public function redis()
    {
        return Redis::client();
    }     

}
