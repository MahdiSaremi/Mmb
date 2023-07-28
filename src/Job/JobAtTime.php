<?php
#auto-name
namespace Mmb\Job;

class JobAtTime extends Job
{

    /**
     * @var int
     */
    public $time;
    
    public function __construct($time)
    {
        $this->time = $time;
    }
    
    protected function cond()
    {
        return time() >= $this->time;
    }

    protected function autoDelete()
    {
        return true;
    }

}
