<?php

namespace dix\base\exception;


class ServiceErrorNotAllowedException extends ServiceException
{
    public function __construct($message = "action not allowed", $data = null)
    {
        parent::__construct(ServiceException::ERROR_ACTION_NOT_ALLOWED, $message, $data);
    }

}