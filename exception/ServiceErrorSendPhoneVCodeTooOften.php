<?php

namespace dix\base\exception;


class ServiceErrorSendPhoneVCodeTooOften extends ServiceException
{
    public function __construct($message = "please send after 60s", $data = null)
    {
        parent::__construct(ServiceException::ERROR_SEND_PHONE_VCODE_TOO_OFTEN, $message, $data);
    }

}