<?php

namespace hollisho\apicache\Adapter;


class RedisAdapter extends AbstractAdapter
{
    private $redis;

    public function __construct($redisClient)
    {
        $this->init($redisClient);
    }

    public function get($key)
    {
        return $this->redis->get($key);
    }

    public function put($key, $value, $defaultLifetime)
    {
        $this->redis->set($key, $value, $defaultLifetime);
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
            }, $options, $options)();
            $redisClient = new $redisClient($redisClient->getConnection(), $options);
        }

        $this->redis = $redisClient;
    }
    
}