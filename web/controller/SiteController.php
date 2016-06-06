<?php

namespace dix\base\web\controller;

use dix\base\controller\BaseController;
use dix\base\exception\ServiceException;
use Yii;
use yii\web\HttpException;
use yii\base\UserException;

class SiteController extends BaseController
{
    public function actionError()
    {
        http_response_code(200);

        if (($exception = Yii::$app->getErrorHandler()->exception) === null) {

        }

        if ($exception instanceof ServiceException)
        {
            $this->finishError($exception->getCode(), $exception->getMessage(), $exception->getData());
        }

        if ($exception instanceof HttpException) {
            $code = $exception->statusCode;
        } else {
            $code = $exception->getCode();
        }

        if ($exception instanceof UserException) {
            $message = $exception->getMessage();
        } else {
            $message = 'An internal server error occurred';
        }

        if ($code == 404)
        {
            $message = 'Not found';
        }

        $this->finish(['code' => $code, 'error' => $message]);
    }

    public function actionIndex()
    {
        $this->finish(['hello' => 'world']);
    }

}
