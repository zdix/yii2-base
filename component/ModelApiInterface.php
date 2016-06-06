<?php
/**
 * Created by PhpStorm.
 * User: dd
 * Date: 8/4/15
 * Time: 10:47
 */

namespace dix\base\component;

/**
 * ActiveRecordInterface
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
interface ModelApiInterface
{
    public static function basicAttributes();

    public static function detailAttributes();

    public static function attributeTypes();

    public static function processRaw($model, $keys = null);

    public static function processRawDetail($model);
}