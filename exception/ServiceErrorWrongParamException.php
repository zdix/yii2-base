<?php

namespace dix\base\exception;


class ServiceErrorWrongParamException extends ServiceException
{
    public function __construct($message = "invalid param", $data = null)
    {
        parent::__construct(ServiceException::ERROR_WRONG_PARAM, $message, $data);
    }

}