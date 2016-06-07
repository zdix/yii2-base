<?php

namespace dix\base\exception;


class ServiceWrongClientException extends ServiceException
{
    public function __construct($message = "wrong client", $data = null)
    {
        parent::__construct(ServiceException::ERROR_WRONG_TYPE, $message, $data);
    }
}