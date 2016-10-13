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

    public function finishSuccess($success)
    {
        $data['code'] = 0;
        if ($success)
        {
            $data['data'] = $success;
        }
        $this->finish($data);
    }

    public function finish($data)
    {
        // DXUtil::doActionStat($this->route);
        header('Content-type:application/json;charset=UTF-8');
        die(json_encode($data));
    }

    public function redis()
    {
        return Redis::client();
    }     

}
