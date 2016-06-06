<?php

namespace dix\base\exception;


class ServiceErrorTokenInvalidException extends ServiceException
{
    public function __construct($message = "token invalid", $data = null)
    {
        parent::__construct(ServiceException::ERROR_TOKEN_INVALID, $message, $data);
    }
}