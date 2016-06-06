<?php

namespace dix\base\web\controller;

use dix\base\component\DXUtil;
use dix\base\controller\BaseController;
use dix\base\web\model\User;

class TestController extends BaseController
{
    public function actionModelFormat()
    {
        $db_user_list = User::find()->all();
        dump(DXUtil::formatModelList($db_user_list, User::className(), 'processRaw'));
        // dump(DXUtil::formatModelList($db_user_list, User::class, 'processRawDetail'));
    }
}