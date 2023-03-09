<?php

namespace Mmb\Controller\QueryControl; #auto

use Mmb\Exceptions\MmbException;
use Mmb\Tools\ATool;

class QueryPattern
{

    
    // -- -- -- -- -- --      Pattern      -- -- -- -- -- -- \\

    /**
     * @var string
     */
    public $pattern;
    
    public function __construct($pattern)
    {
        $this->pattern = $pattern;
    }

    
    // -- -- -- -- -- --      Inputs      -- -- -- -- -- -- \\

    private $inputs = [];

    /**
     * تعریف کردن پترنی که هر متنی را بگیرد
     * 
     * با تعریف ورودی دوم، تنظیم می کنید که یکی از این مقدار ها باشد
     * 
     * @param string $name
     * @param array|null $filter
     * @return $this
     */
    public function any($name, array $filter = null)
    {
        $this->inputs[$name] = $filter ? ['anyOf', $filter] : ['any'];
        return $this;
    }

    /**
     * تعریف کردن پترن عدد
     * 
     * @param string $name
     * @return $this
     */
    public function num($name)
    {
        $this->inputs[$name] = ['num'];
        return $this;
    }

    /**
     * تعریف کردن پترن عدد صحیح
     * 
     * @param string $name
     * @return $this
     */
    public function int($name)
    {
        $this->inputs[$name] = ['int'];
        return $this;
    }

    /**
     * تعریف کردن پترن کلمه
     * 
     * @param string $name
     * @return $this
     */
    public function word($name)
    {
        $this->match($name, '[\w\d]+');
        return $this;
    }

    /**
     * تعریف کردن پترن مچ جدید
     * 
     * @param string $name
     * @param string $pattern
     * @return $this
     */
    public function match ($name, $pattern)
    {
        $this->inputs[$name] = ['match', $pattern];
        return $this;
    }


    // -- -- -- -- -- --      Method      -- -- -- -- -- -- \\
    
    private $method;

    /**
     * تعریف کردن نام متد با نام پترن
     * 
     * @param string $name
     * @return $this
     */
    public function method($name)
    {
        $this->method = $name;
        return $this;
    }

    private $invoke;

    /**
     * تعریف کردن نام متد بصورت ثابت
     * 
     * @param string $method
     * @return $this
     */
    public function invoke($method)
    {
        $this->invoke = $method;
        return $this;
    }
    

    // -- -- -- -- -- --      Args      -- -- -- -- -- -- \\

    private $argsType = 'except-method';
    private $args;

    /**
     * تنظیم آرگومنت ها با نام پترن ها
     * 
     * @param string ...$names
     * @return $this
     */
    public function args(...$names)
    {
        $this->argsType = 'list';
        $this->args = $names;
        return $this;
    }

    /**
     * تنظیم آرگومنت ها همه پترن ها بجز
     * 
     * @param string ...$names
     * @return $this
     */
    public function argsExcept(...$names)
    {
        $this->argsType = 'except';
        $this->args = $names;
        return $this;
    }

    /**
     * تنظیم آرگومنت ها همه پترن ها بجز پترن متد
     * 
     * @return $this
     */
    public function argsExceptMethod()
    {
        $this->argsType = 'except-method';
        $this->args = null;
        return $this;
    }

    /**
     * تنظیم آرگومنت ها همه پترن ها
     * 
     * @return $this
     */
    public function argsAll()
    {
        $this->argsType = 'all';
        $this->args = null;
        return $this;
    }

    /**
     * تنظیم آرگومنت ها بر اساس اینپوت جیسونی
     * 
     * @param string $name
     * @return $this
     */
    public function argsJson($name)
    {
        $this->argsType = 'json';
        $this->args = $name;
        $this->any($name);
        return $this;
    }

    
    // -- -- -- -- -- --      Attributes      -- -- -- -- -- -- \\

    private $attrs = [];
    
    public function ignoreCase()
    {
        $this->attrs['i'] = true;
    }


    // -- -- -- -- -- --      Use      -- -- -- -- -- -- \\
    
    /**
     * بررسی کردن پترن با کوئری
     * 
     * @param string $query
     * @throws ArgumentNameException 
     * @return array|bool
     */
    public function matchQuery($query)
    {
        
        $names = [ 'query' ];

        // Replace patterns in regex pattern
        $pattern = preg_replace_callback('/\\\{([\w\d_]+)\\\}/', function($match) use(&$names) {

            $inp = $this->inputs[$match[1]] ?? false;
            if(!$inp)
                throw new ArgumentNameException("Argument {".$match[1]."} is not defined with methods");

            $names[] = $match[1];

            switch($inp[0])
            {
                case 'any':
                    return '(.*)';
                case 'anyOf':
                    return "(" . join('|', array_map('preg_quote', $inp[1])) . ")";
                case 'num':
                    return '([\-\d][\d\.]*)';
                case 'int':
                    return '([\-\d]\d*)';
                case 'match':
                    return '(' . $inp[1] . ')';
            }

            return $match[0];

        }, preg_quote($this->pattern));

        $attrs = join('', array_keys($this->attrs));
        if(preg_match("/^$pattern$/u$attrs", $query, $match))
        {

            // Method
            $method = false;
            if($this->invoke)
            {
                $method = $this->invoke;
            }
            else
            {
                $i = array_search($this->method, $names);
                if($i === false)
                {
                    throw new ArgumentNameException("Method pattern '{$this->method}' is not defined");
                }
                $method = $match[$i];
            }

            // Args
            switch($this->argsType)
            {
                case 'all':
                    $args = $match;
                    ATool::remove($args, 0);
                break;
                case 'list':
                    $args = [];
                    foreach($this->args as $arg)
                    {
                        $i = array_search($arg, $names);
                        if($i === false)
                        {
                            throw new ArgumentNameException("Argument pattern '$arg' is not defined");
                        }
                        $args[] = $match[$i];
                    }
                break;
                case 'except':
                    $args = [];
                    foreach($names as $i => $arg)
                    {
                        if ($i == 0 || in_array($arg, $this->args))
                            continue;
                        $args[] = $match[$i];
                    }
                break;
                case 'except-method':
                    $args = [];
                    foreach($names as $i => $arg)
                    {
                        if ($i == 0 || $arg == $this->method)
                            continue;
                        $args[] = $match[$i];
                    }
                break;
                case 'json':
                    $i = array_search($this->args, $names);
                    $args = @json_decode($match[$i], true);
                    if (!is_array($args))
                        $args = [];
                break;
            }

            return [ $method, $args ];

        }
        
        return false;

    }

    /**
     * ساخت کوئری با مقدار های دلخواه
     * 
     * @param array $args
     * @throws ExpectedName
     * @throws \InvalidArgumentException
     * @return string
     */
    public function makeQuery(array $args)
    {

        $namedArgs = [];
        $indexArgs = [];
        foreach($args as $i => $arg)
        {
            if(is_numeric($i))
                $indexArgs[] = $arg;
            else
                $namedArgs[$i] = $arg;
        }

        $isJson = $this->argsType == 'json';

        $result = preg_replace_callback('/\{([\w\d_]+)\}/', function ($match) use (&$namedArgs, &$indexArgs, $isJson) {

            $name = $match[1];

            if($isJson && $name == $this->args)
            {
                $args = $indexArgs;
                $indexArgs = [];
                return json_encode($args);
            }

            $inp = $this->inputs[$name] ?? false;
            if(!$inp)
                throw new ArgumentNameException("Argument {".$name."} is not defined with methods");

            if(array_key_exists($name, $namedArgs))
            {
                $value = $namedArgs[$name];
                unset($namedArgs[$name]);
            }
            else
            {
                if($indexArgs)
                {
                    $value = $indexArgs[0];
                    ATool::remove($indexArgs, 0);
                }
                else
                {
                    throw new ExpectedName("Argument pattern '$name' required");
                }
            }

            switch($inp[0])
            {
                case 'anyOf':
                    if(!in_array($value, $inp[1]))
                    {
                        throw new \InvalidArgumentException("Argument '$name' don't accept value '$value'");
                    }
                break;
                case 'num':
                    $value = @floatval($value);
                break;
                case 'int':
                    $value = @intval($value);
                break;
                case 'match':
                    if(!@preg_match("/^({$inp[1]})$/", $value))
                    {
                        throw new \InvalidArgumentException("Argument '$name' not match with pattern '$inp[1]'. value is '$value'");
                    }
                break;
            }

            return $value;

        }, $this->pattern, -1, $countAll);

        if($indexArgs)
        {
            throw new \InvalidArgumentException("Too many arguments, required $countAll, given " . count($args));
        }

        if($namedArgs)
        {
            throw new \InvalidArgumentException("Too many arguments, '" . array_keys($namedArgs)[0] . "' is not exists in pattern");
        }

        return $result;

    }

    
}
