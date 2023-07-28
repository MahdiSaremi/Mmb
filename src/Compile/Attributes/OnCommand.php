<?php
#auto-name
namespace Mmb\Compile\Attributes;

use Attribute;
use Mmb\Compile\Compiler;
use Mmb\Mapping\Arr;

#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class OnCommand extends CompilerAttribute
{

    public function __construct(
        public string|array $command,
        public string $handler = 'pv',
        public ?int $offset = null
    )
    {
    }

    public function strCommand()
    {
        return is_array($this->command) ?
                '[' . implode(", ", array_map(fn($x) => '"' . addslashes($x) . '"', $this->command)) . ']' :
                '"' . addslashes($this->command) . '"';
    }

    public function apply()
    {

    }

    public function multiApply(Arr $all)
    {
        $class = $this->class->getName();
        $this->grouping($all, function($handler, $offset, $commands) use($class)
        {
            // if(count($commands) > 1)
            // {
            //     $code = "$class::handlerGroup(fn() => [";
            //     foreach($commands as $command)
            //     {
            //         $code .= "\n    $class::command(\"".addslashes($command->command)."\", '{$command->method->getName()}'),";
            //     }
            //     $code .= "\n]),";
            // }
            // else
            // {
            //     $code = "$class::command(\"".addslashes($commands[0]->command)."\", '{$commands[0]->method->getName()}'),";
            // }
            $code = "";
            foreach($commands as $command)
            {
                $code .= "\n$class::command(".$command->strCommand().", '{$command->method->getName()}'),";
            }
            Compiler::addHandler($handler, trim($code), $offset);
        });
    }
    
}
