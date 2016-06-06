<?php

ini_set('date.timezone','Asia/Shanghai');

/**
 * @return \yii\web\Application
 */
function app() 
{
	return \Yii::$app;
}

function dump($target) 
{
    \yii\helpers\VarDumper::dump($target, 10, true);
}

function param($name) 
{
	return isset(\Yii::$app->params[$name]) ? \Yii::$app->params[$name] : false;
}

function curl($method, $url, $post_data = null)
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

function consoleLog($content)
{
    if (defined('IN_CONSOLE_APP'))
    {
        echo timeFormat(time()) . ' ' . strval($content) . "\n";
    }

}