<?php
#auto-name
namespace Mmb\Compile;

use Closure;
use Exception;
use ReflectionClass;

class Compiler
{

    private static $handlesFolder;
    private static $configsFolder;

    /**
     * شروع کامپایلر
     *
     * @param string $appFolder
     * @param string $handlesFolder
     * @param string $configsFolder
     * @return void
     */
    public static function compile($appFolder, $handlesFolder, $configsFolder)
    {
        echo "\n@ Compiling...";
        static::$handlesFolder = $handlesFolder;
        static::$configsFolder = $configsFolder;
        static::compileFolder($appFolder);

        static::changeAllHandlers();
        static::applyPolicies();
        static::applyModels();

        echo "\r~ Compile finished!\n";
    }

    /**
     * کامپایل کردن فایل های پوشه
     *
     * @param string $folder
     * @return void
     */
    private static function compileFolder($folder)
    {
        foreach(glob("$folder/*") as $path)
        {
            if(is_dir($path))
            {
                static::compileFolder($path);
            }
            elseif(endsWith($path, ".php", true))
            {
                static::compilePhp($path);
            }
        }
    }

    /**
     * کامپایل کردن فایل پی اچ پی
     *
     * @param string $file
     * @return void
     */
    private static function compilePhp($file)
    {
        // Get class info
        @include_once $file;
        $class = preg_match('/namespace ([\w\\\\]+);/', file_get_contents($file, length:10000), $class) ? $class[1] : "";
        $class .= ($class ? "\\" : "") . (preg_match('/(\w+)\.php$/i', $file, $r) ? $r[1] : "");

        if(!class_exists($class))
            return;
        
        $target = new ReflectionClass($class);

        if($target->isAbstract())
            return;

        $group = [];
        
        // Class attributes
        static::compileClass($target, $file, $group);

        // Method attributes
        foreach($target->getMethods() as $method)
        {
            foreach($method->getAttributes() as $attribute)
            {
                $attr = $attribute->newInstance();
                if($attr instanceof Attributes\CompilerAttribute)
                {
                    $attr->setReference($file, $target, $method, $group)->apply();
                    @$group[$attr->getGroup()][] = $attr;
                }
            }
        }

        foreach($group as $attrGroup)
        {
            $attrGroup[0]->multiApply(arr($attrGroup));
        }
        foreach($group as $attrGroup)
        {
            $attrGroup[0]->multiApplyEnd(arr($attrGroup));
        }
    }
    private static function compileClass(ReflectionClass $target, $file, array &$group, ?ReflectionClass $base = null)
    {
        $cancelParent = false;
        // Class attributes
        foreach($target->getAttributes() as $attribute)
        {
            $attr = $attribute->newInstance();
            if($attr instanceof Attributes\CancelParent)
            {
                $cancelParent = true;
            }
            elseif($attr instanceof Attributes\CompilerAttribute)
            {
                $attr->setReference($file, $base ?? $target, null, $group)->apply();
                @$group[$attr->getGroup()][] = $attr;
            }
        }
        if(!$cancelParent)
        {
            if($parent = $target->getParentClass())
            {
                static::compileClass($parent, $file, $group, $base ?? $target);
            }
        }
    }

    /**
     * تغییر تگ در یک فایل
     *
     * @param string $file
     * @param string $tag
     * @param string $value
     * @param Closure|null $addin
     * @return void
     */
    public static function changeFileTag(string $file, string $tag, string $value, ?Closure $addin = null)
    {
        if(!file_exists($file))
        {
            throw new Exception("File '$file' is not exists!");
        }
        $old = file_get_contents($file);
        if(preg_match('/([ \t]*)(#region Compiler '.$tag.')\r?\n([\s\S]*?)\r?\n(\s*)#endregion/', $old, $region, PREG_OFFSET_CAPTURE))
        {

            $space = $region[1][0] . "    ";
            $value = $space . str_replace("\n", "\n$space", $value);

            $offset = $region[2][1] + strlen($region[2][0]);
            $length = $region[4][1] - $offset;
            $new = substr_replace($old, "\n$value\n", $offset, $length);
            file_put_contents($file, $new);
            
        }
        else
        {
            if(is_null($addin))
            {
                throw new Exception("Compiler tag '$tag' required in file '$file'");
            }
            else
            {
                $new = $addin($old);
                if($new != $old)
                {
                    file_put_contents($file, $new);
                }
                static::changeFileTag($file, $tag, $value, null);
            }
        }
    }

    /**
     * تغییر تگ فایل هندلر
     *
     * @param string $handler
     * @param string $value
     * @param string $tag
     * @return void
     */
    public static function changeHandler(string $handler, string $value, string $tag = "Handler")
    {
        static::changeFileTag(static::$handlesFolder . "/" . $handler . ".php", $tag, $value);
    }

    /**
     * اعمال تغییرات تمامی هندلر ها
     *
     * @return void
     */
    public static function changeAllHandlers()
    {
        foreach([
            'Handler' => static::$handlers_before,
            'End Handler' => static::$handlers_after,
        ] as $tag => $handlers)
        foreach($handlers as $handler => $list)
        {
            $result = "";
            $sorted = arr($list)
                ->map(function($line) {
                    if(is_null($line['offset']))
                        $line['offset'] = 50;
                    return $line;
                })
                ->sortBy('offset');
            
            // Grouping repeated values
            $groupFrom = -1;
            foreach($sorted as $i => $line)
            {
                if(@$line['class'] == @$sorted[$i + 1]['class'])
                {
                    if($groupFrom == -1)
                    {
                        $groupFrom = $i;
                    }
                    $sorted[$i]['line'] = "    " . str_replace("\n", "\n    ", $sorted[$i]['line']);
                }
                elseif($groupFrom != -1)
                {
                    $sorted[$groupFrom]['line'] = "{$line['class']}::handlerGroup(fn() => [\n" . $sorted[$groupFrom]['line'];
                    $sorted[$i]['line'] = "    " . $sorted[$i]['line'] . "\n]),";
                    $groupFrom = -1;
                }
            }

            
            // $sorted = $sorted->groupBy('offset');
            // foreach($sorted as $lines)
            // {
                // Insert
                $value = $sorted->map(fn($line) => $line['line'])->implode("\n");
                $result .= "\n" . trim($value);
            // }
            static::changeHandler($handler, trim($result), tag:$tag);
        }
    }

    public static $handlers_before = [];
    public static $handlers_after = [];

    /**
     * افزودن به هندلر
     *
     * @param string $handler
     * @param string $line
     * @param integer|string|null $offset
     * @param boolean $after
     * @return void
     */
    public static function addHandler(string $handler, string $line, int|string|null $offset = null, bool $after = false)
    {
        if(is_string($offset))
            $offset = $offset === '' ? null : intval($offset);
        if(str_contains($handler, ","))
        {
            foreach(array_map('trim', explode(",", $handler)) as $hand)
            {
                static::addHandler($hand, $line, $offset, $after);
            }
        }
        else
        {
            $class = preg_match('/\s*([\w\\\\]+)::/', $line, $match) ? $match[1] : null;
            if($after)
                @static::$handlers_after[$handler][] = map([ 'line' => $line, 'offset' => $offset, 'class' => $class ]);
            else
                @static::$handlers_before[$handler][] = map([ 'line' => $line, 'offset' => $offset, 'class' => $class ]);
        }
    }

    public static $policies = [];

    public static function addPolicy(string $class)
    {
        static::$policies[] = $class;
    }
    public static function applyPolicies()
    {
        $policies = implode("\n", array_map(fn($policy) => "$policy::class,", static::$policies));
        static::changeFileTag(static::$configsFolder . "/policies.php", "Config", $policies);
    }
    

    public static $models = [];

    public static function addModel(string $class)
    {
        static::$models[] = $class;
    }
    public static function applyModels()
    {
        $models = implode("\n", array_map(fn($policy) => "$policy::class,", static::$models));
        static::changeFileTag(static::$configsFolder . "/database.php", "Config", $models);
    }
    
}
