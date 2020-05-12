<?php

namespace hollisho\apicache\Adapter;


class RedisAdapter extends AbstractAdapter
{
    private $redis;

    private $timeout;

    public function __construct($redisClient, $timeout)
    {
        $this->init($redisClient);
        $this->timeout = $timeout;
    }

    public function get($key)
    {
        return $this->redis->get($key);
    }

    public function put($key, $value)
    {
        if ($this->redis instanceof \Redis) {
            $result = $this->redis->set($key, $value, $this->timeout);
        } else if ($this->redis instanceof \Predis\ClientInterface) {
            $result = $this->redis->set($key, $value);
            $this->redis->expire($key, $this->timeout);
        }
        return $result;
    }
    
    public function isCache($key) {
        return $this->redis->exists();
    }

    /**
     * @param \Redis|\RedisArray|\RedisCluster|\Predis\ClientInterface $redisClient
     */
    private function init($redisClient)
    {
        if (!$redisClient instanceof \Redis && !$redisClient instanceof \RedisArray && !$redisClient instanceof \RedisCluster && !$redisClient instanceof \Predis\ClientInterface && !$redisClient instanceof RedisProxy && !$redisClient instanceof RedisClusterProxy) {
            throw new InvalidArgumentException(sprintf('"%s()" expects parameter 1 to be Redis, RedisArray, RedisCluster or Predis\ClientInterface, "%s" given.', __METHOD__, \is_object($redisClient) ? \get_class($redisClient) : \gettype($redisClient)));
        }

        if ($redisClient instanceof \Predis\ClientInterface && $redisClient->getOptions()->exceptions) {
            $options = clone $redisClient->getOptions();
            \Closure::bind(function () {
                $this->options['exceptions'] = false;
            }, $options, $options);
            $redisClient = new $redisClient($redisClient->getConnection(), $options);
        }

        $this->redis = $redisClient;
    }
    
}