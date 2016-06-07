<?php

namespace dix\base\exception;

use yii\base\UserException;

class ServiceException extends UserException
{
    const ERROR_INVALID = -1;

    const ERROR_PARAM_NOT_SET = 1;
    const ERROR_TOKEN_INVALID = 2;
    const ERROR_LOGIN_FAIL = 3;
    const ERROR_WRONG_PARAM = 4;
    const ERROR_NOT_EXIST = 5;
    const ERROR_EXIST = 6;

    const ERROR_ORG_NOT_EXIST = 7;
    const ERROR_ORG_MEMBER_NOT_EXISTS = 8;
    const ERROR_REGISTER = 9;
    const ERROR_COMPANY_REGISTER = 9;
    const ERROR_USER_NOT_EXISTS = 10;
    const ERROR_PHONE_HAS_BEEN_TAKEN = 11;
    const ERROR_BIND_USER_BIND_EXISTS = 12;
    const ERROR_CREATE_INVITATION = 13;
    const ERROR_INVALID_INVITATION = 14;
    const ERROR_ORG_NO_DEFAULT = 1;

    const ERROR_WRONG_TYPE = 13;
    const ERROR_SAVE_ERROR = 14;
    const ERROR_ACTION_NOT_ALLOWED = 15;
    const ERROR_WRONG_VERIFICATION_CODE = 16;
    const ERROR_SEND_PHONE_VCODE_TOO_OFTEN = 17;

    const ERROR_WRONG_NAME = 18;
    const ERROR_UNKNOWN_POST_ACTION = 19;
    const ERROR_REGION = 20;
    const ERROR_MSG_INVALID_TYPE = 21;
    const ERROR_ACTION_ERROR = 22;
    const ERROR_GEO_CODE_FAIL = 23;
    const ERROR_NOT_PAYED = 24;
    const ERROR_MONEY_NOT_SUFFICIENT = 25;

    protected $data;

    /**
     * @param int $code
     * @param string $message
     * @param array $data
     */
    public function __construct($code, $message, array $data = null)
    {
        $this->code = $code;
        $this->message = $message;
        $this->data = $data;
        // parent::__construct($message, $code, $previous);
    }

    public function getData()
    {
        return $this->data;
    }

}