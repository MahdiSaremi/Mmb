<?php
#auto-name
namespace Mmb\Compile\Attributes;

use Closure;
use Mmb\Mapping\Arr;
use ReflectionClass;
use ReflectionMethod;

abstract class CompilerAttribute
{

    public function __construct(
        public string $handler,
        public ?int $offset = null
    )
    {   
    }
    
    public string $file;
    public ReflectionClass $class;
    public ?ReflectionMethod $method;
    public array $group;
    public final function setReference(string $file, ReflectionClass $class, ?ReflectionMethod $method, array &$group)
    {
        $this->file = $file;
        $this->class = $class;
        $this->method = $method;
        $this->group = &$group;
        return $this;
    }


    public function grouping(Arr $all, Closure $callback)
    {
        foreach($all->groupBy('handler') as $handler => $list)
        {
            foreach($list->groupBy('offset') as $offset => $commands)
            {
                $callback($handler, $offset, $commands);
            }
        }
    }

    public function apply()
    { }

    /**
     * @param Arr|static[] $all
     * @return void
     */
    public function multiApply(Arr $all)
    { }

    public function multiApplyEnd(Arr $all)
    { }

    public function getGroup()
    {
        return static::class;
    }

}
