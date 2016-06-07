<?php

namespace dix\base\exception;


class ServiceErrorSaveException extends ServiceException
{
    public function __construct($message = "save error", $data = null)
    {
        parent::__construct(ServiceException::ERROR_SAVE_ERROR, $message, $data);
    }

}