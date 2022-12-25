<?php

namespace Mmb\Background; #auto

use Mmb\Storage\Storage as StorageBase;

/**
 * حافظه بکگراند
 * 
 * از این کلاس استفاده نکنید
 */
class BackgroundStorage extends StorageBase
{
    
    public static function getFileName()
    {
        return 'background-storage';
    }

}
