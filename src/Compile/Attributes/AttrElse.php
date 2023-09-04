<?php
#auto-name
namespace Mmb\Compile\Attributes;

use Attribute;
use Mmb\Compile\Compiler;

#[Attribute(Attribute::TARGET_ALL)]
class AttrElse extends CompilerAttribute
{

    public function __construct()
    {
    }

}
