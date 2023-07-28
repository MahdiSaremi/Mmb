<?php

namespace Mmb\Update\Message\Data; #auto

use Mmb\Mmb;
use Mmb\MmbBase;

class PollOption extends MmbBase
{
    
    /**
     * @var Poll
     */
    public Poll $poll;

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
    public $percent;
    public function __construct(array $args, Poll $poll, ?Mmb $mmb = null)
    {
        parent::__construct($args, $mmb);
        $this->poll = $poll;

        $this->initFrom($args, [
            'text' => 'text',
            'voter_count' => 'votersCount',
        ]);

        if($poll->votersCount == 0)
            $this->percent = 0;
        else
            $this->percent = $this->votersCount / $poll->votersCount * 100;
    }
}
