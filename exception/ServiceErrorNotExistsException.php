<?php

namespace dix\base\exception;


class ServiceErrorNotExistsException extends ServiceException
{
    public function __construct($message = "not exists", $data = null)
    {
        parent::__construct(ServiceException::ERROR_NOT_EXIST, $message, $data);
    }


}