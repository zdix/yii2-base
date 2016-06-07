<?php

namespace dix\base\component;

use Yii;
use yii\base\InvalidConfigException;

class RedisSession extends \yii\web\Session
{
    /**
     * @var null|\Predis\Client
     */
    private $redis;

    public function init()
    {
        $this->redis = Redis::client();

        parent::init();
    }
    /**
     * Returns a value indicating whether to use custom session storage.
     * This method overrides the parent implementation and always returns true.
     * @return boolean whether to use custom storage.
     */
    public function getUseCustomStorage()
    {
        return true;
    }
    /**
     * Session read handler.
     * Do not call this method directly.
     * @param string $id session ID
     * @return string the session data
     */
    public function readSession($id)
    {
        $data = $this->redis->get($id);
        return $data === false || $data === null ? '' : $data;
    }
    /**
     * Session write handler.
     * Do not call this method directly.
     * @param string $id session ID
     * @param string $data session data
     * @return boolean whether session write is successful
     */
    public function writeSession($id, $data)
    {
        return (bool) $this->redis->setex($id, $this->getTimeout(), $data);
    }
    /**
     * Session destroy handler.
     * Do not call this method directly.
     * @param string $id session ID
     * @return boolean whether session is destroyed successfully
     */
    public function destroySession($id)
    {
        return (bool) $this->redis->del($id);
    }
}
