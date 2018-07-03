<?php

namespace Translate\Console\Drivers;

class TranslateRedis
{
    public function store($key, $value)
    {
        return \Redis::set($key, $value);
    }

    public function get($key)
    {
        return \Redis::get($key);
    }
}