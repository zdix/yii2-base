<?php

namespace dix\base\exception;


class ServiceErrorWrongVCodeException extends ServiceException
{
    public function __construct($message = "wrong verification code", $data = null)
    {
        parent::__construct(ServiceException::ERROR_WRONG_VERIFICATION_CODE, $message, $data);
    }

}