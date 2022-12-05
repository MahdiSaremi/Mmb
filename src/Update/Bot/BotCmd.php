<?php

namespace Mmb\Update\Bot; #auto

use Mmb\Mmb;
use Mmb\MmbBase;

class BotCmd extends MmbBase
{

    /**
     * متن کامند
     *
     * @var string
     */
    public $cmd;

    /**
     * توضیحات کامند
     *
     * @var string
     */
    public $des;

    /**
     * @var Mmb
     */
    private $_base;

    public function __construct(array $args, Mmb $base){

        $this->_base = $base;
        $this->cmd = $args['command'];
        $this->des = $args['description'];

    }
    
    /**
     * تبدیل به آرایه
     *
     * @return array
     */
    public function toAr(){

        return [
            'command' => $this->cmd,
            'description' => $this->des
        ];

    }

    public static function newCmd(Mmb $mmb, $command, $description) {

        return new static([
            'command' => $command,
            'description' => $description,
        ], $mmb);

    }

}
