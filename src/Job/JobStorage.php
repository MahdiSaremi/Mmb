<?php
#auto-name
namespace Mmb\Job;

class JobStorage extends \Mmb\Storage\Storage
{

    public static function getFileName()
    {
        return 'jobs';
    }

}
