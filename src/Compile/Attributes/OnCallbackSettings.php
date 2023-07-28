<?php
#auto-name
namespace Mmb\Compile\Attributes;

use Attribute;
use Mmb\Compile\Compiler;
use Mmb\Mapping\Arr;

#[Attribute(Attribute::TARGET_CLASS)]
class OnCallbackSettings extends CompilerAttribute
{

    public function __construct(
        public ?string $name = null,
        public string $handler = 'pv',
        public ?int $offset = null
    )
    {
    }

    public function apply()
    {
        $this->name ??= $this->class->getName();
    }

    public $methods = [];
    public $lines = [];

    public function multiApplyEnd(Arr $all)
    {
        if($this->methods)
        {
            $pattern = '"' . addslashes($this->name) . ':{method}:{args}"';
            $code = "\$booter->pattern($pattern)";
            $code .= "\n    ->filter('method', [";
            foreach($this->methods as $method)
            {
                $method = "'" . addslashes($method) . "'";
                $code .= "\n        $method,";
            }
            $code .= "\n    ])";
            $code .= "\n    ->argsJson('args');";
        }
        else
        {
            $code = "";
        }

        $code .= "\n" . implode("\n", $this->lines);

        Compiler::changeFileTag($this->file, "Callback", trim($code), function($file) {
            $file = preg_replace(
                '/public\s+function\s+bootCallback\(.*\)[\s\r\n]*\{/i',
                "$0\n        #region Compiler Callback\n\n        #endregion",
                $file, 1, $ok);
            if(!$ok)
            {
                $file = preg_replace(
                    '/class\s+\w+.*[\s\n\r]*\{/i',
                    "$0\n\n    use CallbackControl;\n    public function bootCallback(QueryBooter \$booter)\n    {\n        #region Compiler Callback\n\n        #endregion\n    }",
                    $file, 1, $ok);
            }
            return $file;
        });

        Compiler::addHandler($this->handler, "{$this->class->getName()}::callbackQuery(),", offset:$this->offset);
    }

}
