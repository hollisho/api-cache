<?php

namespace hollisho\apicache\Adapter;

abstract class AbstractAdapter
{
    abstract public function get($key);

    abstract public function put($key, $value);

    abstract public function isCache($key);

}