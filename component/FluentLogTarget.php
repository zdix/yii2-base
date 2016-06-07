<?php
namespace dix\base\component;

use Yii;
use yii\helpers\VarDumper;
use yii\log\Target;

class FluentLogTarget extends Target
{
    public function init()
    {
        parent::init();
    }


    public function export()
    {
        foreach ($this->messages as $message)
        {
            list($text, $level, $category, $timestamp) = $message;
            if (!is_string($text))
            {
                // exceptions may not be serializable if in the call stack somewhere is a Closure
                if ($text instanceof \Exception)
                {
                    $text = (string) $text;
                }
                else
                {
                    $text = VarDumper::export($text);
                }
            }
            $this->postData(json_encode([
                'level' => $level,
                'category' => $category,
                'log_time' => $timestamp,
                'prefix' => $this->getMessagePrefix($message),
                'message' => $text,
            ]));
        }
    }

    private function postData($json)
    {
        $fluent = DXUtil::param('fluent');
        $host = $fluent['host'];
        $port = $fluent['port'];
        $tag = $fluent['tag'];
        $url = "http://$host:$port/$tag";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1);

        curl_setopt($ch, CURLOPT_POSTFIELDS, ['json' => $json]);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        if (curl_errno($ch))
        {
            $error = curl_error($ch);
        }

        curl_close($ch);
    }
}
