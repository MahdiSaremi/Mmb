<?php

namespace Mds\Mmb\Update\Message\Data; #auto

use Mds\Mmb\Mmb;
use Mds\Mmb\MmbBase;

class PollOption extends MmbBase
{
    
    /**
     * @var Mmb
     */
    private $_base;
    /**
     * @var Poll
     */
    private $_basep;

    /**
     * متن
     *
     * @var string
     */
    public $text;
    /**
     * تعداد رای ها به این گزینه
     *
     * @var int
     */
    public $votersCount;
    /**
     * درصد رای این گزینه
     *
     * @var float
     */
    public $cent;
    public function __construct($a, Poll $poll, $base)
    {
        $this->_base = $base;
        $this->_basep = $poll;
        $this->text = $a['text'];
        $this->votersCount = $a['voter_count'];
        if($poll->votersCount == 0)
            $this->cent = 0;
        else
            $this->cent = $this->votersCount / $poll->votersCount * 100;
    }
}
