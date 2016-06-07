<?php

namespace dix\base\exception;


class ServiceErrorRegisterFailException extends ServiceException
{
    public function __construct($message = "register fail", $data = null)
    {
        parent::__construct(ServiceException::ERROR_REGISTER, $message, $data);
    }

}