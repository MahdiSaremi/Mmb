<?php

namespace Mmb\Job; #auto

class JobStorage extends \Mmb\Storage\Storage
{

    public static function getFileName()
    {
        return 'jobs';
    }

}
