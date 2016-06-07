<?php

namespace dix\base\exception;


class ServiceErrorInvalidInvitationException extends ServiceException
{
    public function __construct($message = "invalid invitation", $data = null)
    {
        parent::__construct(ServiceException::ERROR_INVALID_INVITATION, $message, $data);
    }
}