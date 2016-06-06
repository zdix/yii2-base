<?php

namespace dix\base\exception;


class ServiceErrorPhoneHasBeenTakenException extends ServiceException
{
    public function __construct($message = "phone has been taken", $data = null)
    {
        parent::__construct(ServiceException::ERROR_PHONE_HAS_BEEN_TAKEN, $message, $data);
    }

}