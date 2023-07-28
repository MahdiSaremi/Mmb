<?php
#auto-name
namespace Mmb\Compile\Attributes;

use Attribute;
use Mmb\Compile\Compiler;

#[Attribute(Attribute::TARGET_CLASS)]
class AsPolicy extends CompilerAttribute
{

    public function __construct()
    {
    }

    public function apply()
    {
        Compiler::addPolicy($this->class->getName());
    }
    
}
