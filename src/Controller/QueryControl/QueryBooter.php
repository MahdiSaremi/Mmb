<?php

namespace Mmb\Controller\QueryControl; #auto

use Mmb\Exceptions\MmbException;
use Mmb\Exceptions\TypeException;
use Mmb\Tools\ATool;

class QueryBooter
{


    // -- -- -- -- -- --      Controller      -- -- -- -- -- -- \\

    private $class;

    public function __construct($class)
    {
        $this->class = $class;
    }


    // -- -- -- -- -- --      Pattern      -- -- -- -- -- -- \\

    private $patterns = [];

    /**
     * ایجاد پترن جدید
     * 
     * در پترن از ساختار زیر استفاده کنید و سپس آنها را تعریف کنید:
     * {name}
     * 
     * Example: `$booter -> pattern("news:{method}:{id}") -> any('method', ['info', 'delete']) -> int('id');`
     * 
     * @param string $pattern
     * @return QueryPattern
     */
    public function pattern($pattern)
    {
        return $this->patterns[] = new QueryPattern($pattern);
    }

    private $else;

    /**
     * متدی تعریف کنید تا در صورت عدم تطابق، آن صدا شود
     *
     * @param string $method
     * @return void
     */
    public function else($method)
    {
        $this->else = $method;
    }
    

    // -- -- -- -- -- --      Use      -- -- -- -- -- -- \\

    /**
     * پیدا کردن پترنی که با کوئری مچ می شود
     * @param string $query
     * @return array|bool
     */
    public function matchQuery($query, $parentMatch = [], $parentSubIndex = 0)
    {
        foreach($this->patterns as $pattern)
        {
            $result = $pattern->matchQuery($query, $parentMatch, $parentSubIndex);
            if($result)
            {
                return $result;
            }
        }

        if($this->else)
            return [ $this->else, [] ];

        return false;
    }

    /**
     * ایجاد کوئری با ورودی های دلخواه
     * 
     * @param array $args
     * @throws MmbException 
     * @return string
     */
    public function makeQuery(array $args)
    {
        $errors = "";
        foreach($this->patterns as $pattern)
        {
            try
            {
                $query = $pattern->makeQuery($args);
                return $query;
            }
            catch(\Exception $e)
            {
                $errors .= "\n" . $e->getMessage();
            }
        }
        throw new MmbException("No match found, pattern errors:$errors");
    }

    public function makeQueryReplace($namedArgs, $indexArgs, $result, $countAll)
    {
        $errors = "";
        foreach($this->patterns as $pattern)
        {
            try
            {
                $res = $pattern->makeQueryReplace($namedArgs, $indexArgs, $pattern->pattern, $countAll);
                return $res;
            }
            catch(\Exception $e)
            {
                $errors .= "\n\t" . $e->getMessage();
            }
        }
        throw new MmbException("Sub failed errors:$errors");
    }

}
