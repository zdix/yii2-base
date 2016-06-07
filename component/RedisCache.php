<?php

namespace dix\base\component;

use Yii;
use yii\di\Instance;

class RedisCache extends \yii\caching\Cache
{
    /**
     * @var null|\Predis\Client
     */
    private $redis;

    public function init()
    {
        parent::init();
        $this->redis = Redis::client();
    }
    /**
     * Checks whether a specified key exists in the cache.
     * This can be faster than getting the value from the cache if the data is big.
     * Note that this method does not check whether the dependency associated
     * with the cached data, if there is any, has changed. So a call to [[get]]
     * may return false while exists returns true.
     * @param mixed $key a key identifying the cached value. This can be a simple string or
     * a complex data structure consisting of factors representing the key.
     * @return boolean true if a value exists in cache, false if the value is not in the cache or expired.
     */
    public function exists($key)
    {
        return (bool) $this->redis->exists($this->buildKey($key));
    }
    /**
     * @inheritdoc
     */
    protected function getValue($key)
    {
        return $this->redis->get($key);
    }
    /**
     * @inheritdoc
     */
    protected function getValues($keys)
    {
        $response = $this->redis->mget($keys);
        $result = [];
        $i = 0;
        foreach ($keys as $key) {
            $result[$key] = $response[$i++];
        }
        return $result;
    }
    /**
     * @inheritdoc
     */
    protected function setValue($key, $value, $expire)
    {
        if ($expire == 0) {
            return (bool) $this->redis->set($key, $value);
        } else {
            $expire = (int) ($expire * 1000);
            return (bool) $this->redis->setex($key, $expire, $value);
        }
    }
    /**
     * @inheritdoc
     */
    protected function setValues($data, $expire)
    {
        $args = [];
        foreach ($data as $key => $value) {
            $args[] = $key;
            $args[] = $value;
        }
        $failedKeys = [];
        if ($expire == 0) {
            $this->redis->mset($args);
        } else {
            $expire = (int) ($expire * 1000);
            $this->redis->multi();
            $this->redis->mset($args);
            $index = [];
            foreach ($data as $key => $value) {
                $this->redis->pexpire($key, $expire);
                $index[] = $key;
            }
            $result = $this->redis->exec();
            array_shift($result);
            foreach ($result as $i => $r) {
                if ($r != 1) {
                    $failedKeys[] = $index[$i];
                }
            }
        }
        return $failedKeys;
    }
    /**
     * @inheritdoc
     */
    protected function addValue($key, $value, $expire)
    {
        if ($expire == 0) {
            return (bool) $this->redis->set($key, $value);
        } else {
            $expire = (int) ($expire * 1000);
            return (bool) $this->redis->setex($key, $value, $expire);
        }
    }
    /**
     * @inheritdoc
     */
    protected function deleteValue($key)
    {
        return (bool) $this->redis->del($key);
    }
    /**
     * @inheritdoc
     */
    protected function flushValues()
    {
        return $this->redis->flushdb();
    }
}