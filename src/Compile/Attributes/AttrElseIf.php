<?php
#auto-name
namespace Mmb\Compile\Attributes;

use Attribute;
use Mmb\Calling\Caller;
use Mmb\Compile\Compiler;

#[Attribute(Attribute::TARGET_ALL)]
class AttrElseIf extends CompilerAttribute
{

    protected $condition;

    public function __construct(
        $config = null,
        $env = null,
        $method = null,
        $call = null,
        protected bool $not = false,
    )
    {
        if(!is_null($config))
        {
            $this->condition = fn() => config()->get($config);
        }
        if(!is_null($env))
        {
            $this->condition = fn() => env($env);
        }
        if(!is_null($method))
        {
            $this->condition = fn() => Caller::invoke($this->class->getName(), $method, silentMode: true);
        }
        if(!is_null($call))
        {
            $this->condition = fn() => Caller::invoke2($call, silentMode: true);
        }
    }

    public function getValue()
    {
        if($this->not)
        {
            return !value($this->condition);
        }
        else
        {
            return value($this->condition);
        }
    }
    
}
