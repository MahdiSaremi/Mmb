<?php

namespace Mmb\Cache; #auto

/**
 * حافظه موقت
 */
class Cache
{

    /**
     * گرفتن کش
     * 
     * @param string $name
     * @param \Closure $create
     * @param int $life
     * @return mixed
     */
    public static function get($name, \Closure $create = null, $life = 3600)
    {
        $base = CacheStorage::getBase();
        if($cache = $base[$name] ?? false)
        {
            // Check life time
            if(time() < @$cache['life'])
            {
                return $cache['value'];
            }

            // Delete cache
            if(!$create)
            {
                CacheStorage::editBase(function (&$base) use ($name) {
                    unset($base[$name]);
                });
                return null;
            }
        }

        // Create value
        if($create)
        {
            $value = $create();
            static::set($name, $value, $life);
            return $value;
        }

        return null;
    }

    /**
     * تنظیم کش
     * 
     * @param string $name
     * @param mixed $value
     * @param int $life
     * @return void
     */
    public static function set($name, $value, $life = 3600)
    {
        CacheStorage::editBase(function (&$base) use ($name, $value, $life) {
            $base[$name] = [
                'life' => time() + $life,
                'value' => $value,
            ];
        });
    }

    /**
     * تنظیم کش
     * 
     * @param string $name
     * @param mixed $value
     * @param int $lifeToTime
     * @return void
     */
    public static function setUntil($name, $value, $lifeToTime)
    {
        CacheStorage::editBase(function (&$base) use ($name, $value, $lifeToTime) {
            $base[$name] = [
                'life' => $lifeToTime,
                'value' => $value,
            ];
        });
    }

}
