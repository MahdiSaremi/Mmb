<?php
#auto-name
namespace Mmb\ExtraThrow;

use Closure;

class ExtraErrorCallback extends ExtraException
{

    public function __construct(protected Closure $callback, ?string $exceptionMessage = null)
    {
        parent::__construct($exceptionMessage);
    }

    public function invoke()
    {
        $cb = $this->callback;
        $cb();
    }
    
}
