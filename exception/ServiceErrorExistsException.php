<?php

namespace dix\base\exception;


class ServiceErrorExistsException extends ServiceException
{
    public function __construct($message = "already exists", $data = null)
    {
        parent::__construct(ServiceException::ERROR_EXIST, $message, $data);
    }
}