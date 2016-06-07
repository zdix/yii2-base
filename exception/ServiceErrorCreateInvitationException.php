<?php
/**
 * Created by PhpStorm.
 * User: Cheng Yang <yangcheng0816@gmail.com>
 * Date: 16/2/23
 * Time: 15:59
 */

namespace dix\base\exception;


class ServiceErrorCreateInvitationException extends ServiceException
{
    public function __construct()
    {
        parent::__construct(ServiceException::ERROR_CREATE_INVITATION, "create invitation fail");
    }

}