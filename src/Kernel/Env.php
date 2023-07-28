<?php
#auto-name
namespace Mmb\Kernel;

class Env
{

    private static $_values = [];

    /**
     * بارگذاری از طریق فایل
     *
     * @param string $path
     * @return void
     */
    public static function loadFrom($path)
    {
        static::$_values = array_replace(static::$_values, includeFile($path));
    }

    /**
     * تنظیم مقدار
     *
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public static function set($name, $value)
    {
        static::$_values[$name] = $value;
    }

    /**
     * گرفتن مقدار
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public static function get($name, $default = null)
    {
        return static::$_values[$name] ?? $default;
    }

}
