<?php

namespace hollisho\apicache;

use hollisho\apicache\Adapter\FilesystemAdapter;

class ApiCache
{
    private $cache_time = 10;

    /**
     * @var AbstractAdapter  $adapter
     */
    private $adapter;

    /**
     * @return AbstractAdapter
     */
    public function getAdapter()
    {
        return $this->adapter ? $this->adapter : new FilesystemAdapter('', null, $this->cache_time);
    }

    /**
     * @param AbstractAdapter $adapter
     */
    public function setAdapter($adapter)
    {
        $this->adapter = $adapter;
    }

    public function getData($key, $callback, $params = [], $refresh = false) {
        if (!$refresh && $result = $this->getCache($key)) {
            return json_decode($result, true);
        } else {
            $result = call_user_func_array($callback, $params);
            $ress = $this->setCache($key, json_encode($result));
            return $result;
        }
    }

    public function getCache($key) {
        return $this->getAdapter()->get($key);
    }

    public function isCached($key) {
        return $this->getAdapter()->exist();
    }

    public function setCache($key, $value) {
        return $this->getAdapter()->put($key, $value);
    }
}