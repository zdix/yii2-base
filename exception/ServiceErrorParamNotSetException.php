<?php

namespace dix\base\exception;


class ServiceErrorParamNotSetException extends ServiceException
{

    public function __construct($message = "param not set", $data = null)
    {
        parent::__construct(ServiceException::ERROR_PARAM_NOT_SET, $message, $data);
    }

}