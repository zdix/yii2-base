<?php

namespace dix\base\component;

class DXUtil extends \yii\base\Object
{
    public static function app()
    {
        return \Yii::$app;
    }

    public function dump($target)
    {
        \yii\helpers\VarDumper::dump($target, 10, true);
    }

    public static function param($name)
    {
        return isset(\Yii::$app->params[$name]) ? \Yii::$app->params[$name] : false;
    }

    /**
     * @return \yii\db\Command
     */
    public static function sql($sql = null)
    {
        $connection = \Yii::$app->db;
        $command = $connection->createCommand($sql);

        return $command;
    }

    public static function curl($method, $url, $post_data = null)
    {
        $data['error'] = null;

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
        if ($method == 'POST' && $post_data != null)
        {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $data['response'] = curl_exec($ch);
        if (curl_errno($ch))
        {
            $data['error'] = curl_error($ch);
        }
        curl_close($ch);

        return $data;
    }

    public static function timeFormat($time, $format = 'full')
    {

        if ($time === '')
            return '';

        if ($format == 'ago')
        {
            $unit = 60;
            $p = time() - $time;
            if ($p / $unit < 1) return ($p / 1) . '秒前';

            $unit*=60;
            if ($p / $unit < 1) return intval($p / 60) . '分钟前';

            $unit*=24;
            if ($p / $unit < 1) return intval($p / 60 / 60) . '小时前';

            $unit*=30;
            if ($p / $unit < 1) return intval($p / 60 / 60 / 24) . '天前';

            return self::timeFormat($time, 'full');
        }
        if ($format == 'full')	return date('Y-m-d H:i:s', $time);
        if ($format == 'date')	return date('Y-m-d', $time);
        if ($format == 'month')	return date('m-d', $time);

        return date('Y-m-d H:i:s', $time);
    }

    public static function checkArrayKeys($array, $keys)
    {
        foreach ($keys as $key)
        {
            if (!array_key_exists($key, $array))
            {
                return false;
            }
        }

        return true;
    }

    public static function generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++)
        {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public static function generateRandomNumberString($length = 10)
    {
        $characters = '0123456789012340123456789567012345678989';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++)
        {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }


    public static function convertArrayValueType($array, $config)
    {
        $new_array = [];
        foreach ($array as $key => $value)
        {
            if (isset($config[$key]))
            {
                $type = $config[$key];
                switch ($type)
                {
                    case 'i':
                    case 'int':
                        $new_array[$key] = intval($value);
                        break;

                    case 'f':
                    case 'float':
                        $new_array[$key] = floatval($value);
                        break;

                    case 'b':
                    case 'bool':
                        $new_array[$key] = $value ? true : false;
                        break;

                    default:
                        $new_array[$key] = $value;
                }
            }
            else
            {
                $new_array[$key] = $value;
            }
        }

        return $new_array;
    }

    public static function formatRawModel($item, $model_api_sub_class, $keys = null)
    {
        $get_attributes_func = "$model_api_sub_class::attributeTypes";
        if (!is_callable($get_attributes_func))
        {
            return null;
        }

        $attributes = call_user_func($get_attributes_func);
        $keys = $keys ? $keys : array_keys($attributes);

        return self::processModel($item, $keys, $attributes);
    }


    public static function processModel($model, $keys, $types)
    {
        if ($model == null) return null;

        if (isset($model->attributes))
        {
            $model = $model->attributes;
        }

        if (!DXUtil::checkArrayKeys($model, $keys))
        {
            return null;
        }

        $model = array_intersect_key($model, array_flip($keys));
        $model = DXUtil::convertArrayValueType($model, $types);


        return $model;
    }

    public static function formatModelList($item_list, $model_api_sub_class, $process_func = 'processRaw', $keys = null)
    {
        $result = [];

        if (!is_array($item_list))
        {
            return $result;
        }

        $process_func_name = $process_func;
        $process_func_param = [];
        if (is_array($process_func))
        {
            /**
             * ['processRaw', 'user_id']
             */
            if (count($process_func) <= 1)
            {
                return $result;
            }

            $process_func_name = $process_func[0];
            $process_func_param = array_slice($process_func, 1);
        }

        $get_attributes_func_callable = "$model_api_sub_class::basicAttributes";
        $process_func_callable = "$model_api_sub_class::$process_func_name";
        if (!is_callable($get_attributes_func_callable) || !is_callable($process_func_callable))
        {
            return $result;
        }
        
        $attributes = call_user_func($get_attributes_func_callable);
        $keys = $keys ? $keys : $attributes;

        foreach ($item_list as $item)
        {
            $params = array_merge([$item, $keys], $process_func_param);
            $result[] = call_user_func_array($process_func_callable, $params);
        }

        $result = array_filter($result);

        return $result;
    }

    public static function xml2array($xmlstring)
    {
        $xml = simplexml_load_string($xmlstring, "SimpleXMLElement", LIBXML_NOCDATA);
        $json = json_encode($xml);
        $array = json_decode($json, TRUE);

        return $array;
    }

    public static function convertArrayValueToString($array)
    {
        if ($array == null || !is_array($array)) return $array;

        $new_array = [];
        foreach ($array as $key => $value)
        {
            if (is_array($value))
            {
                $new_array[$key] = self::convertArrayValueToString($value);
            }
            else
            {
                if (is_float($value) || is_int($value))
                {
                    $new_array[$key] = strval($value);
                }
                else
                {
                    $new_array[$key] = $value;
                }

            }

        }

        return $new_array;
    }

    public static function convertArrayStringValueToInt($array)
    {
        if ($array == null || !is_array($array)) return $array;

        $new_array = [];
        foreach ($array as $key => $value)
        {
            if (is_array($value))
            {
                $new_array[$key] = self::convertArrayStringValueToInt($value);
            }
            else
            {
                if (is_string($value))
                {
                    $new_array[$key] = intval($value);
                }
                else
                {
                    $new_array[$key] = $value;
                }

            }

        }

        return $new_array;
    }

    /**
     * @return \Predis\Client $redis
     */
    public static function redis()
    {
        return self::redisClient();
    }

    /**
     * @return \Predis\Client $redis
     */
    public static function redisClient()
    {
        return Redis::client();
    }

    /**
     * @return \Predis\Client $redis
     */
    public static function redisPubSubClient()
    {
        static $redis = null;

        if ($redis === null)
        {
            $redis = Redis::createClient();
        }

        return $redis;
    }


    public static function time()
    {
        return RUN_START_TIME_INT;
    }

    public static function getElapsedTime()
    {
        return microtime(true) - RUN_START_TIME;
    }

    public static function doActionStat($route)
    {
        $time = self::getElapsedTime();
        $time = intval($time * 1000);

        $key = DXKey::getKeyOfAction($route);

        $redis = Redis::client();
        $count = intval($redis->HGET($key, 'count'));
        $average_time = intval($redis->HGET($key, 'average_time'));
        $max_time = intval($redis->HGET($key, 'max_time'));

        $average_time = intval(($average_time * $count + $time) * 1.0 / ($count + 1));

        $redis->HINCRBY($key, 'count', 1);
        $redis->HSET($key, 'average_time', $average_time);
        if ($time > $max_time)
        {
            $redis->HSET($key, 'max_time', $time);
        }

        $key_rank = DXKey::getKeyOfActionTimeRank();
        $redis->zadd($key_rank, [ $key => $average_time]);
    }

    public static function isMobile() {
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        $mobile_agents = Array("240x320","acer","acoon","acs-","abacho","ahong","airness","alcatel","amoi",
            "android","anywhereyougo.com","applewebkit/525","applewebkit/532","asus","audio","au-mic","avantogo",
            "becker","benq","bilbo","bird","blackberry","blazer","bleu","cdm-","compal","coolpad","danger","dbtel",
            "dopod","elaine","eric","etouch","fly ","fly_","fly-","go.web","goodaccess","gradiente","grundig","haier",
            "hedy","hitachi","htc","huawei","hutchison","inno","ipad","ipaq","ipod","jbrowser","kddi","kgt","kwc","lenovo",
            "lg ","lg2","lg3","lg4","lg5","lg7","lg8","lg9","lg-","lge-","lge9","longcos","maemo","mercator","meridian","micromax",
            "midp","mini","mitsu","mmm","mmp","mobi","mot-","moto","nec-","netfront","newgen","nexian","nf-browser","nintendo","nitro",
            "nokia","nook","novarra","obigo","palm","panasonic","pantech","philips","phone","pg-","playstation","pocket","pt-","qc-","qtek",
            "rover","sagem","sama","samu","sanyo","samsung","sch-","scooter","sec-","sendo","sgh-","sharp","siemens","sie-","softbank","sony",
            "spice","sprint","spv","symbian","tablet","talkabout","tcl-","teleca","telit","tianyu","tim-","toshiba","tsm","up.browser","utec",
            "utstar","verykool","virgin","vk-","voda","voxtel","vx","wap","wellco","wig browser","wii","windows ce","wireless","xda","xde","zte");
        $is_mobile = false;
        foreach ($mobile_agents as $device) {
            if (stristr($user_agent, $device)) {
                $is_mobile = true;
                break;
            }
        }
        return $is_mobile;
    }

    public static function log($title, $data, $key = 'log')
    {
        $log = [
            'time' => timeFormat(time()),
            'title' => $title,
            'data' => $data
        ];
        $redis = Redis::client();
        $redis->lpush($key, json_encode($log));
    }

    public static function consoleLog($content)
    {
        if (defined('IN_CONSOLE_APP'))
        {
            echo timeFormat(time()) . ' ' . strval($content) . "\n";
        }

    }

    public static function runClientAction($action, $params)
    {
        $module_name = '/client-100/';
        $action = trim($action, '/');
        $route = $module_name . $action;

        if (is_array($params))
        {
            $_REQUEST = array_merge($_REQUEST, $params);
        }

        app()->runAction($route, $params);

    }

    public static function jsonEncode($data)
    {
        return \yii\helpers\Json::encode($data);
    }

    public static function isPhone($phone)
    {
        $phone = strval($phone);
        return preg_match("/^1[34578]\d{9}$/", $phone);
    }

    public static function isHex($data)
    {
        return ctype_xdigit($data);
    }

    public static function multiExplode ($delimiters,$string)
    {
        $ready = str_replace($delimiters, $delimiters[0], $string);
        $launch = explode($delimiters[0], $ready);
        return  $launch;
    }

    public static function convertDecToBinArrayByKeys ($dec, array $keys)
    {
        $dec = intval($dec);
        $result = [];
        $length = count($keys);
        for ($i = 0; $i < $length; $i ++)
        {
            $key = $keys[$i];
            $bitValue = pow(2, $i);
            if (($dec&$bitValue) === $bitValue)
            {
                $result[$key] = 1;
            }
            else
            {
                $result[$key] = 0;
            }
        }
        return $result;
    }

    public static function doTransaction($user_func)
    {
        if (!is_callable($user_func))
        {
            return null;
        }

        $result = null;

        $transaction = DXUtil::app()->db->beginTransaction();
        try
        {
            $result = $user_func();

            $transaction->commit();
        }
        catch (\Exception $e)
        {
            $transaction->rollBack();
            throw $e;
        }

        return $result;
    }

    public static function filterIdString($id_string)
    {
        return implode(',', self::getIdArrayFromIdString($id_string));
    }

    public static function getIdArrayFromIdString($id_string)
    {
        $id_list_filtered = [];
        if ($id_string)
        {
            $id_string = trim(trim($id_string), ',');
            $id_list = explode(',', $id_string);
            foreach ($id_list as $id)
            {
                $id = trim($id);
                if (strval(intval($id)) === strval($id))
                {
                    $id_list_filtered[] = $id;
                }
            }
        }

        return $id_list_filtered;
    }

    public static function getTransformFunc($type)
    {
        $func = 'strval';
        switch ($type)
        {
            case 'i': $func = 'intval'; break;
            case 'f': $func = 'floatval'; break;
            case 'b': $func = 'boolval'; break;
        }
        return $func;
    }

    public static function getServiceMethodCall($method)
    {
        if (strpos($method, '!') === 0)
        {
            return substr($method, 1);
        }
        else
        {
            return substr($method, strrpos($method, '\\') + 1);
        }
    }

    public static function getFileMimeType($path)
    {
        if (!file_exists($path))
        {
            return null;
        }

        $finfo = new \finfo(FILEINFO_MIME);
        $mimetype = $finfo->file($path);
        $mimetypeParts = preg_split('/\s*[;,]\s*/', $mimetype);
        $type = strtolower($mimetypeParts[0]);
        unset($finfo);

        return $type;
    }

    public static function validateUploadFile($key, $mime_types, $max_size)
    {
        if (!isset($_FILES[$key])
            || !isset($_FILES[$key]['tmp_name'])
            || !isset($_FILES[$key]['size'])
            || !isset($_FILES[$key]['error'])
            || !isset($_FILES[$key]['name'])
        )
        {
            return [1, 'no file uploaded'];
        }

        $path = $_FILES[$key]['tmp_name'];
        $size = $_FILES[$key]['size'];
        $error = $_FILES[$key]['error'];
        $name = $_FILES[$key]['name'];

        if ($error !== UPLOAD_ERR_OK)
        {
            $message = 'upload file error';
            switch ($error)
            {
                case UPLOAD_ERR_INI_SIZE:
                    $message = 'The uploaded file exceeds the upload_max_filesize';
                    break;
                case UPLOAD_ERR_FORM_SIZE:
                    $message = 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form';
                    break;
                case UPLOAD_ERR_PARTIAL:
                    $message = 'The uploaded file was only partially uploaded';
                    break;
                case UPLOAD_ERR_NO_FILE:
                    $message = 'No file was uploaded';
                    break;
                case UPLOAD_ERR_NO_TMP_DIR:
                    $message = 'Missing a temporary folder';
                    break;
                case UPLOAD_ERR_CANT_WRITE:
                    $message = 'Failed to write file to disk';
                    break;
                case UPLOAD_ERR_EXTENSION:
                    $message = 'An extension stopped the file upload';
                    break;
            }

            return [2, $message];
        }

        if (!file_exists($path))
        {
            return [3, 'uploaded file not exists'];
        }

        if ($size > $max_size || $size <= 0)
        {
            return [4, 'wrong size'];
        }

        $type = DXUtil::getFileMimeType($path);
        if (!empty($mime_types) && !in_array($type, $mime_types))
        {
            return [5, 'wrong mime type'];
        }

        return [0, null];
    }

    public static function parseHeader($header)
    {
        $headers = explode("\r\n", $header);
        $output = array();

        if ('HTTP' === substr($headers[0], 0, 4)) {
            list(, $output['status'], $output['status_text']) = explode(' ', $headers[0]);
            unset($headers[0]);
        }

        foreach ($headers as $v)
        {
            $h = preg_split('/:\s*/', $v);
            if (count($h) < 2) continue;
            $output[$h[0]] = $h[1];
        }

        return $output;
    }
    
    public static function convertType($value, $type)
    {
        switch ($type)
        {
            case 's':
            case 'string':
                $value = strval($value);
                break;

            case 'i':
            case 'integer':
                $value = intval($value);
                break;

            case 'f';
            case 'float';
                $value = floatval($value);
                break;

            case 'b':
            case 'bool':
                $value = boolval($value);
                break;
        }

        return $value;
    }

    public static function safeGet($target, $key, $default = '', $type = 'do not convert')
    {
        $value = $default;
        if (is_object($target) && isset($target->$key))
        {
            $value = $target->$key;
        }

        if (is_array($target) && isset($target[$key]))
        {
            $value = $target[$key];
        }

        $value = self::convertType($value, $type);

        return $value;
    }
    
}