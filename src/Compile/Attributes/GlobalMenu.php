<?php
#auto-name
namespace Mmb\Compile\Attributes;

use Attribute;
use Mmb\Compile\Attributes\CompilerAttribute;
use Mmb\Compile\Compiler;
use Mmb\Mapping\Arr;

#[Attribute(Attribute::TARGET_METHOD)]
class GlobalMenu extends CompilerAttribute
{

    public function __construct(
        public string $handler = 'pv',
        public ?int $offset = null
    )
    {
    }

    public function multiApply(Arr $all)
    {
        $this->grouping($all, function($handler, $offset, $list)
        {
            /** @var static $menu */
            foreach($list as $menu)
            {
                Compiler::addHandler($handler, "{$menu->class->getName()}::globalFixMenu('{$menu->method->getName()}'),", offset:$offset);
            }
        });
    }

}
