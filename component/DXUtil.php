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

        $key_action = DXKey::getKeyOfAction($route);
        $key_rank = DXKey::getKeyOfActionTimeRank();

        $redis = Redis::client();

        $stat = $redis->hgetall($key_action);
        $count = isset($stat['count']) ? intval($stat['count']) : 0;
        $average_time = isset($stat['average_time']) ? intval($stat['average_time']) : 0;
        $max_time = isset($stat['max_time']) ? intval($stat['max_time']) : 0;

        $average_time = intval(($average_time * $count + $time) * 1.0 / ($count + 1));

        $redis->pipeline()
            ->hincrby($key_action, 'count', 1)
            ->hset($key_action, 'average_time', $average_time)
            ->hset($key_action, 'last_time', $time)
            ->hset($key_action, 'max_time', $time > $max_time ? $time : $max_time)
            ->zadd($key_rank, [ $key_action => $average_time])
            ->execute()
        ;
    }

    public static function log($title, $data, $key = 'log')
    {
        $log = [
            'time' => self::timeFormat(time()),
            'title' => $title,
            'data' => $data
        ];
        $redis = Redis::client();
        $redis->lpush($key, json_encode($log));
        $redis->ltrim($key, 0, 10000);
    }

    public static function consoleLog($content)
    {
        if (defined('IN_CONSOLE_APP'))
        {
            echo self::timeFormat(time()) . ' ' . strval($content) . "\n";
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

    public static function isInt($value)
    {
        if (is_int($value))
        {
            return true;
        }

        if (is_string($value) && is_numeric($value))
        {
            if ($value === strval(intval($value)))
            {
                return true;
            }
        }

        return false;
    }

    public static function generateValidUid($uid_generator_func, $uid_valid_check_func, $uid_valid_func, $try_max_count = 9)
    {
        $uid = null;
        $try_count = 0;

        // try to max of 3 times to register
        while (true)
        {
            $try_count++;
            if ($try_count > $try_max_count)
            {
                return false;
            }

            // generate an available uid
            $try_generate_uid_count = 0;
            $should_start_new_try = false;
            while (true)
            {
                $uid = $uid_generator_func();
                if (!$uid_valid_check_func($uid))
                {
                    break;
                }

                $try_generate_uid_count++;
                if ($try_generate_uid_count >= $try_max_count)
                {
                    $should_start_new_try = true;
                    break;
                }
            }

            if ($should_start_new_try)
            {
                continue;
            }

            // lock uid
            if (Redis::lock('company.uid', $uid))
            {
                return $uid_valid_func($uid);
            }
        }


        return false;
    }

    /**
     * @param array $hosts
     * @param bool $enable_dix_permit
     */
    public static function cors($hosts, $enable_dix_permit = false)
    {
        if (isset($_SERVER['HTTP_ORIGIN']) )
        {
            $origin = trim($_SERVER['HTTP_ORIGIN']);
            $host = trim($origin, 'http://');
            $host = trim($host, 'https://');
            $host = trim($host, 'www');

            $has_dix = false;

            if ($enable_dix_permit)
            {
                if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
                {
                    $access_control_request_headers = $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'];
                    if (strpos(strtolower($access_control_request_headers), 'dix') !== -1)
                    {
                        $has_dix = true;
                    }
                }
                if (isset($_SERVER['HTTP_DIX']) || isset($_SERVER['DIX']))
                {
                    $has_dix = true;
                }
            }

            if (in_array($host, $hosts) || $has_dix)
            {
                header("Access-Control-Allow-Origin: ${origin}");
                header('Access-Control-Allow-Credentials: true');
                header('Access-Control-Max-Age: 86400');    // cache for 1 day
            }
        }

        // Access-Control headers are received during OPTIONS requests
        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS')
        {
            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
            {
                header("Access-Control-Allow-Methods: POST, OPTIONS");
            }

            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
            {
                header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
            }

            exit(0);
        }
    }

    /**
     * @param \yii\db\ActiveQuery $query
     * @param string $col
     * @return array
     */
    public static function getSingleColumnValueListFromQuery($query, $col = 'id')
    {
        $id_list = [];

        $i = 0;
        while (true)
        {
            $db_id_list = $query->offset($i * 20)->limit(20)->select($col)->asArray()->all();
            if (empty($db_id_list))
            {
                break;
            }

            foreach ($db_id_list as $db_id)
            {
                $id_list[] = intval($db_id[$col]);
            }

            $i++;
        }

        return array_unique($id_list);
    }

    public static function getHashOfArgs()
    {
        return md5(self::jsonEncode(func_get_args()));
    }
}