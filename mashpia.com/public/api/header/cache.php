<?php
require_once( $_SERVER['DOCUMENT_ROOT'] . "/../includes/globals.php");

class Cache
{
    private static $client = null;
    private static $enabled = null;

    private static function client()
    {
        if (self::$client !== null || self::$enabled === false) {
            return self::$client;
        }

        try {
            $redis = new Redis();
            $redis->connect($GLOBALS['global_redis_host'], (int) $GLOBALS['global_redis_port'], 1.5);

            if (!empty($GLOBALS['global_redis_password'])) {
                $redis->auth($GLOBALS['global_redis_password']);
            }

            if (isset($GLOBALS['global_redis_db'])) {
                $redis->select((int) $GLOBALS['global_redis_db']);
            }

            self::$client = $redis;
            self::$enabled = true;
            return self::$client;
        } catch (\Throwable $e) {
            self::$enabled = false;
            return null;
        }
    }

    public static function enabled()
    {
        return self::client() !== null;
    }

    public static function get($key)
    {
        $client = self::client();
        if (!$client) return null;

        $value = $client->get($key);
        return $value === false ? null : json_decode($value, true);
    }

    public static function set($key, $value, $ttl)
    {
        $client = self::client();
        if (!$client) return false;

        return $client->setex($key, (int) $ttl, json_encode($value));
    }

    public static function delete($key)
    {
        $client = self::client();
        if (!$client) return false;

        return $client->del($key);
    }

    public static function remember($key, $ttl, callable $callback)
    {
        $cached = self::get($key);
        if ($cached !== null) return $cached;

        $value = $callback();
        self::set($key, $value, $ttl);
        return $value;
    }

    public static function bumpVersion($key)
    {
        $client = self::client();
        if (!$client) return 1;

        return $client->incr($key);
    }

    public static function version($key)
    {
        $client = self::client();
        if (!$client) return 1;

        $value = $client->get($key);
        if ($value === false) {
            $client->set($key, 1);
            return 1;
        }

        return (int) $value;
    }
}