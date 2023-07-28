<?php
#auto-name
namespace Mmb\Compile\Attributes;

use Attribute;
use Exception;
use Mmb\Compile\Compiler;
use Mmb\Mapping\Arr;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS)]
class AutoHandle extends CompilerAttribute
{

    public function __construct(
        public string $handler = 'pv',
        public ?int $offset = null,
        public bool $after = false,
        public ?string $call = null
    )
    {
    }

    public function multiApply(Arr $all)
    {
        $class = $this->class->getName();
        $this->grouping($all, function($handler, $offset, $commands) use($class)
        {
            $code = "";
            /** @var static $command */
            foreach($commands as $command)
            {
                if(!$command->method)
                {
                    $method = $this->call ?? 'instance';
                }
                else
                {
                    $method = $this->call ?? match(strtolower($command->method->getShortName())) {
                        'bootcallback' => 'callbackQuery',
                        'bootstart' => 'startCommand',
                        'bootmsg' => 'msgQuery',
                        'handle' => 'instance',
                        default => '',
                    };
                    if(!$method)
                    {
                        throw new Exception("Unknown method '{$command->method->getShortName()}'");
                    }
                }
                $code .= "\n$class::$method(),";
            }
            Compiler::addHandler($handler, trim($code), $offset, $command->after);
        });
    }

}
