<?php

namespace dix\base\component;

use dix\base\exception\ServiceErrorSendPhoneVCodeTooOften;

class PhoneVerification extends \yii\base\Object
{
    public static function send($phone, $send_func)
    {
        $code = rand(1000, 9999);

        $redis = Redis::client();
        $redis_key_phone_verification_code = DXKey::getKeyOfPhoneVerificationCode($phone);
        $redis_key_phone_verification_code_send_time = DXKey::getKeyOfPhoneVerificationCodeSendTime($phone);

        $rcode = $redis->get($redis_key_phone_verification_code);
        $send_time = $redis->get($redis_key_phone_verification_code_send_time);
        $now = time();

        if ($rcode && $send_time && intval($send_time) + 1800 > $now)
        {
            // $code = intval($rcode);
        }

        if ($now - $send_time < 60)
        {
            throw new ServiceErrorSendPhoneVCodeTooOften();
        }

        $redis->set($redis_key_phone_verification_code, $code);
        $redis->set($redis_key_phone_verification_code_send_time, $now);

        if (is_callable($send_func))
        {
            $send_func($phone, $code);
        }
    }

    public static function sendCode($phone, $code)
    {
        // send code
    }

    public static function validate($phone, $code)
    {
        $valid = false;
        $redis = Redis::client();
        $redis_key_phone_verification_code = DXKey::getKeyOfPhoneVerificationCode($phone);
        $redis_key_phone_verification_code_send_time = DXKey::getKeyOfPhoneVerificationCodeSendTime($phone);
        $vcode = $redis->get($redis_key_phone_verification_code);
        $send_time = $redis->get($redis_key_phone_verification_code_send_time);
        if ($vcode && intval($vcode) == $code && $send_time && intval($send_time) + 1800 > time())
        {
            $valid = true;
        }

        return $valid;
    }

}