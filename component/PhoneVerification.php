<?php

namespace dix\base\component;

use dix\base\exception\ServiceErrorSendPhoneVCodeTooOften;
use yii\web\Request;

class PhoneVerification extends \yii\base\Object
{
    public static function send($phone, $send_func)
    {
        $code = rand(1000, 9999);

        $redis = Redis::client();
        $now = time();

        $guest_id = self::getGuestId();
        $access_api_time_key = DXKey::getKeyOfVCodeApiAccessTime($guest_id);
        $access_time_value = $redis->get($access_api_time_key);
        if ($now - $access_time_value < 60)
        {
            throw new ServiceErrorSendPhoneVCodeTooOften();
        }

        $redis_key_phone_verification_code = DXKey::getKeyOfPhoneVerificationCode($phone);
        $redis_key_phone_verification_code_send_time = DXKey::getKeyOfPhoneVerificationCodeSendTime($phone);

        $rcode = $redis->get($redis_key_phone_verification_code);
        $send_time = $redis->get($redis_key_phone_verification_code_send_time);

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
        $redis->set($access_api_time_key, $now);

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

    public static function getGuestId()
    {
        $request = new Request();
        $user_ip = !is_null($request->getUserIP()) ? $request->getUserIP() : "";

        $request_headers = $request->getHeaders();

        $ip = isset($request_headers['X-Real-IP']) ? $request_headers['X-Real-IP'] : $user_ip;
        $agent = $request_headers['user-agent'];
        $language = $request_headers['accept-language'];
        $guest_id = sprintf("%s.%s.%s", $ip, $agent, $language);

        return md5($guest_id);
    }

}
