<?php
#auto-name
namespace Mmb\Compile\Attributes;

use Attribute;
use Mmb\Compile\Compiler;

#[Attribute(Attribute::TARGET_CLASS)]
class AsModel extends CompilerAttribute
{

    public function __construct()
    {
    }

    public function apply()
    {
        Compiler::addModel($this->class->getName());
    }
    
}
