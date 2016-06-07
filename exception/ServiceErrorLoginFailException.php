<?php

namespace dix\base\exception;


class ServiceErrorLoginFailException extends ServiceException
{
    public function __construct($message = "wrong password", $data = null)
    {
        parent::__construct(ServiceException::ERROR_LOGIN_FAIL, $message, $data);
    }

}