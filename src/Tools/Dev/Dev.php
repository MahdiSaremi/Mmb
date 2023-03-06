<?php
#auto-name
namespace Mmb\Tools\Dev;

use Mmb\Tools\ATool;

class Dev
{

    public static function handle()
    {
        global $argv, $argc;
        
        $target = $argv[1] ?? 'help';
        $dev = static::$devs[$target] ?? false;
        if(!$dev)
        {
            die("Error: '$target' is not defined!\n");
        }

        $dev = new $dev;
        for($i = 2; $i < $argc; $i++)
        {
            $arg = $argv[$i];
            if(@$arg[0] == '-')
            {
                $arg = ltrim($arg, '-');
                $p = strpos($arg, '=');
                if($p)
                {
                    $val = substr($arg, $p + 1);
                    $arg = substr($arg, 0, $p);
                }
                else
                {
                    $val = true;
                }
                $dev->addCons($arg, $val);
            }
            else
            {
                $dev->addInput($arg);
            }
        }
        $dev->run();
    }

    public static function loadDefault()
    {
        foreach(glob(__DIR__ . '/Mmb/*.php') as $file)
        {
            $class = preg_match('/Mmb\/(.*?)\.php/', $file, $class) ? $class : null;
            $class = __NAMESPACE__ . "\\Mmb\\$class[1]";
            $class::boot();
        }
    }

    protected static $devs = [];
    public static function set($name)
    {
        static::$devs[strtolower($name)] = static::class;
    }

    public static function boot()
    {
    }

    private $row_inputs = [];
    public function addInput($text)
    {
        $this->row_inputs[] = $text;
        return $this;
    }
    public function addInputs(Dev $dev)
    {
        array_push($this->row_inputs, ...$dev->row_inputs);
        $this->cons_vals = array_replace($this->cons_vals, $dev->cons_vals);
        return $this;
    }


    private $cons_vals = [];
    public function addCons($name, $value = true)
    {
        $this->cons_vals[strtolower($name)] = $value;
        return $this;
    }

    public function getCons($name)
    {
        return $this->cons_vals[strtolower($name)] ?? false;
    }

    /**
     * خواندن ورودی
     *
     * @param string $text
     * @return string
     */
    public function input($text = "")
    {
        if($this->row_inputs)
        {
            $value = $this->row_inputs[0];
            ATool::remove($this->row_inputs, 0);
            return $value;
        }

        echo $text;
        return readline();
    }

    /**
     * گرفتن ورودی اسم کلاس
     *
     * `[$namespace, $class] = $this->inputClass();`
     * 
     * @param string $text
     * @return string[]
     */
    public function inputClass($text = "")
    {
        $name = trim(str_replace('/', '\\', $this->input($text)), '\\');
        $p = strrpos($name, '\\');
        if(!$p)
            return ['', $name];
        else
            return [ substr($name, 0, $p), substr($name, $p + 1) ];
    }

    public function inputYN($text = "")
    {
        return in_array(strtolower($this->input($text)), ['y', 'yes', 'yep', 'ok']);
    }

    /**
     * خطا
     *
     * @param string $text
     * @return void
     */
    public function error($text)
    {
        die($text . "\n");
    }
    

    public function findClass($namespace, $class)
    {
        return new DevFile($this, $namespace, $class);
    }
    
}
