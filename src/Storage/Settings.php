<?php

namespace Mmb\Storage; #auto

/**
 * مقدار هایی که در این کلاس تنظیم می کنید، به عنوان تنظیمات ذخیره می شوند
 */
class Settings extends Storage
{

    public static function getFileName()
    {
        return 'settings';
    }

}
