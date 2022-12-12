<?php

namespace Mmb\Assets; #auto

class Assets
{

    private static $path;

    public static function setPath($path)
    {
        self::$path = $path;
    }

    public static function getPath()
    {
        return self::$path;
    }

    public static function file($path, $mime_type = '', $posted_filename = '')
    {
        return new \CURLFile(self::getPath() . "/" . $path, $mime_type, $posted_filename);
    }
    
}
