<?php

namespace Mmb\Update\Bot; #auto

use Mmb\Mmb;
use Mmb\MmbBase;

class BotCmd extends MmbBase
{

    /**
     * متن کامند
     *
     * @var ?string
     */
    public $cmd;

    /**
     * توضیحات کامند
     *
     * @var ?string
     */
    public $des;

    public function __construct(array $args, ?Mmb $mmb = null)
    {
        parent::__construct($args, $mmb);
        $this->initFrom($args, [
            'command' => 'cmd',
            'description' => 'des',
        ]);
    }
    
    /**
     * تبدیل به آرایه
     *
     * @return array
     */
    public function toAr()
    {
        return [
            'command' => $this->cmd,
            'description' => $this->des
        ];
    }

    public static function newCmd(Mmb $mmb, $command, $description)
    {
        return new static([
            'command' => $command,
            'description' => $description,
        ], $mmb);
    }

}
