<?php
#auto-name
namespace Mmb\Tools\Advanced;
use Mmb\Storage\Storage;

class AdvUserRepatStorage extends Storage
{

    public static function getFileName()
    {
        return 'advuserrepeat_storage';
    }

    public static function jsonFlag()
    {
        return JSON_UNESCAPED_UNICODE;
    }
    
}
