<?php

namespace dix\base\component;

class DXKey extends \yii\base\Object
{
    public static function getKeyOfUserUidById($user_id)
    {
        $user_id = intval($user_id);
        return "user.id.$user_id.uid";
    }

    public static function getKeyOfAction($action)
    {
        $date = date('Y-m-d', time());
        return "api.stat.$date.$action";
    }

    public static function getKeyOfActionTimeRank()
    {
        $date = date('Y-m-d', time());
        return "api.stat.$date.rank";
    }

    public static function getKeyOfPhoneVerificationCode($phone)
    {
        return "phone.$phone.verification.code";
    }

    public static function getKeyOfPhoneVerificationCodeExpireTime($phone)
    {
        $phone = intval($phone);
        return "phone.$phone.verification.code.expire.time";
    }

    public static function getKeyOfPhoneVerificationCodeSendTime($phone)
    {
        $phone = intval($phone);
        return "phone.$phone.verification.code.send.time";
    }



}