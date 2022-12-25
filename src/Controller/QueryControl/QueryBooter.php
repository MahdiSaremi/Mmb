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
    

    // -- -- -- -- -- --      Use      -- -- -- -- -- -- \\

    /**
     * پیدا کردن پترنی که با کوئری مچ می شود
     * @param string $query
     * @return array|bool
     */
    public function matchQuery($query)
    {
        foreach($this->patterns as $pattern)
        {
            $result = $pattern->matchQuery($query);
            if($result)
            {
                return $result;
            }
        }
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

}
