<?php
#auto-name
namespace Mmb\Pay\Storage;
use Mmb\Storage\Storage;

class PayStorage extends Storage
{

    public static function getFileName()
    {
        return 'paysInfo';
    }
    
}
