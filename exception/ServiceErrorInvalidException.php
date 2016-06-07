<?php

namespace dix\base\exception;


class ServiceErrorInvalidException extends ServiceException
{
    public function __construct($message = "invalid", $data = null)
    {
        parent::__construct(ServiceException::ERROR_INVALID, $message, $data);
    }
}