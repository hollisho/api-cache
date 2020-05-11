<?php

namespace hollisho\apicache;

use hollisho\apicache\Adapter\RedisAdapter;

class ApiCache
{
    /**
     * @var AbstractAdapter  $adapter
     */
    private $adapter;

    public function __construct($adapter)
    {
        $this->adapter = $adapter;
    }

    public function getData($key, $callback, $params = []) {
        if ($this->adapter) {
            $result = $this->adapter->get($key);
            return json_decode($result);
        } else {
            $result = call_user_func_array($callback, $params);
            $this->adapter->put($key, json_encode($result));
        }
    }

    public function getCache($key) {
        $result = $this->adapter->get($key);
        return json_decode($result);
    }

    public function isCached($key) {
        return $this->getCache();
    }
}