<?php
/**
 * Created by PhpStorm.
 * User: dd
 * Date: 10/10/15
 * Time: 19:00
 */

namespace dix\base\component;

use Yii;


class DXLog
{
    private static function processData($data)
    {
        if (is_array($data))
        {
            $data = json_encode($data);
        }

        $data = is_string($data) ? $data : strval($data);

        return $data;
    }

    public static function debug($key, $data)
    {
        $data = self::processData($data);

        Yii::warning($data, $key);
    }

    public static function info($key, $data)
    {
        $data = self::processData($data);

        Yii::info($data, $key);
    }

    public static function trace($key, $data)
    {
        $data = self::processData($data);

        Yii::trace($data, $key);
    }

    public static function error($key, $data)
    {
        $data = self::processData($data);

        Yii::error($data, $key);
    }

    public static function warning($key, $data)
    {
        $data = self::processData($data);

        Yii::warning($data, $key);
    }
}