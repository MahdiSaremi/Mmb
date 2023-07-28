<?php
#auto-name
namespace Mmb\Controller\Handler;

use Closure;

class HandlerIf extends HandlerGroup
{

    public function __construct(
        public ?Closure $if,
        public Closure|array $result
    )
    {
    }

    public function getHandlers()
    {
        $if = $this->if;
        if(!Handler::$requireStop && (!$if || $if()))
        {
            $res = $this->result;
            if(is_array($res))
            {
                return $res;
            }
            else
            {
                return $res();
            }
        }
        return [ ];
    }
    
}
