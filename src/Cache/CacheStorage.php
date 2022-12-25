<?php

namespace Mmb\Cache; #auto

use Mmb\Storage\Storage;

class CacheStorage extends Storage
{
    
    public static function getFileName()
    {
        return 'cache';
    }

    public static function jsonFlag()
    {
        return 0;
    }

}
