<?php

namespace Mmb\Controller\QueryControl; #auto

use Closure;
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
        preg_match_all('/\{(\w+)(?:\:(\w+))?\}/', $pattern, $matches);
        foreach($matches[1] as $index => $name)
        {
            $type = $matches[2][$index] ?: "any";
            $this->$type($name);
        }

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
     * پترن مورد نظر باید یکی از این مقدار ها را داشته باشد
     *
     * @param string $name
     * @param array $value
     * @return $this
     */
    public function filter($name, array $value)
    {
        $this->inputs[$name] = [ 'anyOf', $value ];
        return $this;
    }

    /**
     * پترن مورد نظر باید یکی از این مقدار ها را داشته باشد
     * 
     * در ساخت کوئری، زمانی که نام کلید را وارد می کنید، مقدار روبروی آن جایگزین می شود
     * 
     * `$booter->pattern("test:{method}")->filterAs('method', [ 'create' => 'c', 'delete' => 'd' ]);`
     * 
     * `public function create() { }`
     * 
     * `Target::keyShareInline("+ New project +", 'create') // "test:c"`
     *
     * @param string $name
     * @param array $value
     * @return $this
     */
    public function filterAs($name, array $name_value)
    {
        $this->inputs[$name] = [ 'anyAs', $name_value ];
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
     * تعریف کردن پترن عدد
     * 
     * alias to num()
     * 
     * @param string $name
     * @return $this
     */
    public function number($name)
    {
        return $this->num($name);
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
     * تعریف کردن پترن عدد صحیح
     * 
     * alias to int()
     *
     * @param string $name
     * @return $this
     */
    public function integer($name)
    {
        return $this->int($name);
    }

    /**
     * تعریف کردن پترن کلمه
     * 
     * `[\w\d_\-]+`
     * 
     * @param string $name
     * @return $this
     */
    public function word($name)
    {
        $this->match($name, '[\w\-]+');
        return $this;
    }

    /**
     * تعریف کردن پترن کلمه به شکل بیس 64
     * 
     * `[a-zA-Z0-9\+\/]+[=]*`
     * 
     * @param string $name
     * @return $this
     */
    public function base64($name)
    {
        $this->match($name, '[a-zA-Z0-9\+\/]+[=]*');
        return $this;
    }

    /**
     * تعریف کردن پترن کلمه به شکل بیس 16
     * 
     * `[0-9a-fA-F]+`
     * 
     * @param string $name
     * @return $this
     */
    public function base16($name)
    {
        $this->match($name, '[0-9a-fA-F]+');
        return $this;
    }

    /**
     * تعریف کردن پترن کلمه به شکل بیس 2 یا همان باینری
     * 
     * `[01]+`
     * 
     * @param string $name
     * @return $this
     */
    public function base2($name)
    {
        $this->match($name, '[01]+');
        return $this;
    }

    /**
     * تعریف کردن پترن مچ جدید
     * 
     * @param string $name
     * @param string $pattern
     * @return $this
     */
    public function match($name, $pattern)
    {
        $this->inputs[$name] = ['match', $pattern];
        return $this;
    }


    // -- -- -- -- -- --    Pro Methods   -- -- -- -- -- -- \\

    public $hasSub = false;
    public $subName;
    public $subCallback;
    public $subBooter;

    /**
     * تعریف کردن زیرمجموعه
     * 
     * `$booter->pattern("film:{sub}")->sub('sub', function(QueryBooter $booter) { ... });`
     * 
     * توجه: تنها یک ساب می توانید تعریف کرد!
     *
     * @param string $name
     * @param Closure $callback
     * @return $this
     */
    public function sub($name, Closure $callback)
    {
        $this->any($name);
        $this->hasSub = true;
        $this->subName = $name;
        $this->subCallback = $callback;
        return $this;
    }

    private function subBoot()
    {
        if(!$this->subBooter)
        {
            $subCallback = $this->subCallback;
            $this->subBooter = new QueryBooter(null);
            $subCallback($this->subBooter);
        }
    }


    // -- -- -- -- -- --      Method      -- -- -- -- -- -- \\
    
    private $method = 'method';

    /**
     * تعریف کردن نام متد با نام پترن
     * 
     * بصورت پیشفرض روی نام متد تنظیم است `method`
     * 
     * @param string $name
     * @return $this
     */
    public function method($name)
    {
        $this->method = $name;
        $this->invoke = null;
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
        $this->method = null;
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
    public function matchQuery($query, $parentMatch = [], $parentSubIndex = 0)
    {
        $names = [ 'query' ];
        $recheck_args = [];
        

        // Replace patterns in regex pattern
        $pattern = preg_replace_callback('/\\\{(\w+)(?:\\\:.*?)?\\\}/', function($match) use(&$names, &$recheck_args) {

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
                case 'anyAs':
                    $recheck_args[] = $match[1];
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

            // Recheck filter
            foreach($recheck_args as $arg)
            {
                $index = array_search($arg, $names);
                $inp = $this->inputs[$arg];
                switch($inp[0])
                {
                    case 'anyAs':
                        $match[$index] = array_search($match[$index], $inp[1]);
                    break;
                }
            }

            // Sub
            if($this->hasSub)
            {
                $index = array_search($this->subName, $names);
                $this->subBoot();
                $sub = $match[$index];
                unset($match[0]);
                unset($match[$index]);
                return $this->subBooter->matchQuery($sub, array_values($match), $index - 1);
            }

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

            $resArgs = $parentMatch;
            ATool::insertMulti($resArgs, $parentSubIndex, $args);

            return [ $method, $resArgs ];
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

        [$namedArgs, $indexArgs, $result, $countAll]
                = $this->makeQueryReplace($namedArgs, $indexArgs, $this->pattern, 0);

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

    public function makeQueryReplace($namedArgs, $indexArgs, $result, $countAll)
    {
        $isJson = $this->argsType == 'json';

        $result = preg_replace_callback('/\{(\w+)(?:\:.*)?\}/', function ($match) use (&$namedArgs, &$indexArgs, &$countAll, $isJson) {

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

            // Sub make query
            if($this->hasSub && $this->subName == $name)
            {
                $this->subBoot();
                [ $namedArgs, $indexArgs, $value, $countAll ] = 
                        $this->subBooter->makeQueryReplace($namedArgs, $indexArgs, null, $countAll);
                return $value;
            }

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
                case 'anyAs':
                    if(!isset($inp[1][$value]))
                    {
                        throw new \InvalidArgumentException("Argument '$name' don't accept value '$value'");
                    }
                    $value = $inp[1][$value];
                break;
                case 'num':
                    if(!is_numeric($value))
                    {
                        throw new \InvalidArgumentException("Argument '$name' is not numeric, given '$value'");
                    }
                    $value = @floatval($value);
                break;
                case 'int':
                    if(!is_numeric($value) && strpos($value, '.') !== false)
                    {
                        throw new \InvalidArgumentException("Argument '$name' must be integer, given '$value'");
                    }
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

        }, $result, -1, $count);

        return [ $namedArgs, $indexArgs, $result, $countAll + $count ];
    }
    
}
