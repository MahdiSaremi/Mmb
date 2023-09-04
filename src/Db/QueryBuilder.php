<?php

namespace Mmb\Db; #auto

use BadMethodCallException;
use Closure;
use Exception;
use InvalidArgumentException;
use Mmb\Exceptions\MmbException;
use Mmb\Exceptions\TypeException;
use Mmb\ExtraThrow\ExtraErrorMessage;
use Mmb\Listeners\HasCustomMethod;
use Mmb\Listeners\HasNormalStaticListeners;
use Mmb\Mapping\Arr;
use Mmb\Mapping\Arrayable;
use Mmb\Mapping\Map;
use Mmb\Tools\ATool;
use Mmb\Tools\Text;
use Traversable;

/**
 * @template R
 */
class QueryBuilder
{

    use HasCustomMethod;

    public function __construct()
    {
        
    }

    /**
     * هدف موردنظر
     *
     * @var string
     */
    protected $table;
    /**
     * تنظیم جدول موردنظر
     *
     * @param string $table
     * @return $this
     */
    public function table(string $table)
    {
        $this->table = static::stringColumn($table);

        return $this;
    }

    /**
     * انتخاب از یک کوئری دیگر
     *
     * @param QueryBuilder $from
     * @param string $as
     * @return $this
     */
    public function from(QueryBuilder $from, string $as)
    {
        $this->table = '(' . $from->createQuery() . ') as ' . static::stringColumn($as);

        return $this;
    }

    use QueryHasWhere;
    use QueryHasHaving;

    /**
     * مرتب سازی بر اساس
     *
     * @var array
     */
    protected $order = [];
    /**
     * مرتب سازی بر اساس
     *
     * @param string|array $cols
     * @param string $sortType
     * @return $this
     */
    public function orderBy($cols, $sortType = null)
    {
        if(!is_array($cols))
            $cols = [ $cols ];

        $cols = array_map([$this, 'stringColumn'], $cols);

        $this->order[] = [ $cols, $sortType ];

        return $this;
    }

    /**
     * مرتب سازی نزولی بر اساس
     *
     * @param string|array $cols
     * @return $this
     */
    public function orderDescBy($cols)
    {
        return $this->orderBy($cols, 'DESC');
    }

    /**
     * مرتب سازی از آخرین ستون ها
     * 
     * این متد بر اساس آیدی ای که خودکار پر می شود محاسبه می شود
     *
     * @param string $idCol
     * @return $this
     */
    public function latest($idCol = 'id')
    {
        return $this->orderDescBy($idCol);
    }

    /**
     * حداکثر تعداد
     *
     * @var int|false
     */
    protected $limit = false;
    /**
     * محل شروع
     *
     * @var int|false
     */
    protected $offset = false;

    /**
     * محدود کردن تعداد انتخاب
     *
     * @param ?int $limit
     * @param int $offset
     * @return $this
     */
    public function limit($limit, $offset = null)
    {
        $this->limit = $limit;
        
        if($offset !== null)
            $this->offset = $offset;

        return $this;
    }

    /**
     * محل شروع انتخاب
     *
     * @param ?int $offset
     * @return $this
     */
    public function offset($offset)
    {
        $this->offset = $offset;

        return $this;
    }


    /**
     * گروه بندی بر اساس
     *
     * @var array
     */
    public $groupBy;

    /**
     * گروه بندی بر اساس
     *
     * @param array|string $by
     * @return $this
     */
    public function groupBy($by)
    {
        $this->groupBy = array_map([$this, 'stringColumn'], is_array($by) ? $by : [ $by ]);
        return $this;
    }


    /**
     * کلاس خروجی ابجکت
     *
     * @var string
     */
    protected $output = Table\Unknown::class;
    /**
     * تنظیم کلاس خروجی
     *
     * @template T
     * @param class-string<T> $class
     * @return QueryBuilder<T>
     */
    public function output($class)
    {
        $this->output = $class;
        return $this;
    }


    protected $db_driver;
    /**
     * تنظیم دیتابیس مربوطه
     *
     * @param Driver $driver
     * @return $this
     */
    public function db(?Driver $driver)
    {
        $this->db_driver = $driver;
        return $this;
    }

    use HasNormalStaticListeners;

    /**
     * افزودن شنونده قبل از اجرای یک کوئری
     * 
     * اگر چیزی را ریترن کنید، آن مقدار جایگزین می شود
     *
     * @param Closure $callback `function(QueryCompiler $query)`
     * @return void
     */
    public static function queryExecuting(Closure $callback)
    {
        static::listen('queryExecuting', $callback);
    }
    /**
     * افزودن شنونده بعد از اجرای یک کوئری
     * 
     * اگر چیزی را ریترن کنید، آن مقدار جایگزین می شود
     *
     * @param Closure $callback `function(QueryResult $result)`
     * @return void
     */
    public static function queryExecuted(Closure $callback)
    {
        static::listen('queryExecuted', $callback);
    }

    public function fireQueryExecuting(QueryCompiler $query)
    {
        $result = static::invokeListeners('queryExecuting', [ $query ], 'last-not-null') ?? $query;
        if(!($result instanceof QueryCompiler))
        {
            throw new TypeException("Query listener, must return QueryCompiler, returned " . typeOf($result));
        }

        return $result;
    }

    public function fireQueryExecuted(QueryResult $query)
    {
        $result = static::invokeListeners('queryExecuted', [ $query ], 'last-not-null') ?? $query;
        if(!($result instanceof QueryResult))
        {
            throw new TypeException("Query listener, must return QueryResult, returned " . typeOf($result));
        }

        return $result;
    }

    /**
     * گرفتن تمامی متغیر ها برای ارسال به کامپایلر
     *
     * @return array
     */
    protected function getObjectVars()
    {
        return get_object_vars($this);
    }

    /**
     * اجرای کوئری
     *
     * @param string $type
     * @return QueryResult|string
     */
    protected function run($type, $local = [], $exportStringQuery = false)
    {
        $driver = $this->db_driver ?: Driver::defaultStatic();

        $compilerClass = $driver->queryCompiler;
        $compiler = new $compilerClass;

        foreach ($this->getObjectVars() as $name => $value)
            $compiler->$name = $value;
        foreach ($local as $name => $value)
            $compiler->$name = $value;
            
        $compiler->start($type);

        if($exportStringQuery)
        {
            return $compiler->query;
        }

        $compiler = $this->fireQueryExecuting($compiler);
        $result = $driver->runQuery($compiler);
        $result = $this->fireQueryExecuted($result);

        return $result;
    }

    protected $joins = [];

    protected function _join($type, $isSub, $class, $condition = null, $operator = null, $colValue = null)
    {
        if(is_array($class))
        {
            $cls = $class[0];
            if($isSub)
                $joinQuery = '(' . $cls . ')' . ' AS ' . static::stringColumn($class[1]);
            else
                $joinQuery = static::stringColumn($cls::getTable()) . ' AS ' . static::stringColumn($class[1]);
        }
        else
        {
            if($isSub)
                $joinQuery = '(' . $class . ')';
            else
                $joinQuery = static::stringColumn($class::getTable());
        }

        if(!is_null($condition))
        {
            $args = func_get_args();
            unset($args[0], $args[1], $args[2]);
            $this->where(function($query) use($condition, $args)
            {
                if($condition instanceof Closure)
                {
                    $query->where($condition);
                }
                else
                {
                    $query->whereCol(...$args);
                }
            });

            $on = array_pop($this->where)[2];
        }
        else
        {
            $on = null;
        }

        $this->joins[] = [ $type, $joinQuery, $on ];

        return $this;
    }

    /**
     * جوین کردن یک جدول دیگر
     * 
     * `$usersAndOrders = User::join(Order::class, Order::column('user_id'), User::column('id'))->all();`
     * 
     * `$usersAndOrders = User::join(Order::class, Order::column('user_id'), '=', User::column('id'))->all();`
     * 
     * `$usersAndOrders = User::join(Order::class, function($query) { $query->where(Order::column('user_id'), '=', User::column('id')); })->all();`
     * 
     * توجه کنید که با وجود جوین، نمی توانید از شرط دیگری استفاده کنید (where)
     *
     * @param string|array $class
     * @param string|Closure $condition
     * @param string $operator
     * @param string $colValue
     * @return $this
     */
    public function join($class, $condition = null, $operator = null, $colValue = null)
    {
        return $this->_join(null, false, ...func_get_args());
    }
    
    /**
     * جوین کردن یک جدول دیگر با یک نام
     * 
     * `$commentAndReplyTo = Comment::crossJoinAs(Comment::class, 'replyTo', 'replyTo.reply_id', Comment::column('id'))->all();`
     * 
     * توجه کنید که با وجود جوین، نمی توانید از شرط دیگری استفاده کنید (where)
     *
     * @param string $class
     * @param string $as
     * @param string|Closure $condition
     * @param string $operator
     * @param string $colValue
     * @return $this
     */
    public function joinAs($class, $as, $condition = null, $operator = null, $colValue = null)
    {
        $args = func_get_args();
        unset($args[0], $args[1]);
        return $this->_join(null, false, [ $class, $as ], ...$args);
    }
    
    /**
     * جوین کردن یک جدول دیگر با اولویت جدول سمت چپ
     *
     * @param string|array $class
     * @param string|Closure $condition
     * @param string $operator
     * @param string $colValue
     * @return $this
     */
    public function leftJoin($class, $condition = null, $operator = null, $colValue = null)
    {
        return $this->_join('LEFT', false, ...func_get_args());
    }
    
    /**
     * @param string $class
     * @param string $as
     * @param string|Closure $condition
     * @param string $operator
     * @param string $colValue
     * @return $this
     */
    public function leftJoinAs($class, $as, $condition = null, $operator = null, $colValue = null)
    {
        $args = func_get_args();
        unset($args[0], $args[1]);
        return $this->_join('LEFT', false, [ $class, $as ], ...$args);
    }
    
    /**
     * جوین کردن یک جدول دیگر با اولویت جدول سمت راست
     *
     * @param string|array $class
     * @param string|Closure $condition
     * @param string $operator
     * @param string $colValue
     * @return $this
     */
    public function rightJoin($class, $condition = null, $operator = null, $colValue = null)
    {
        return $this->_join('RIGHT', false, ...func_get_args());
    }
    
    /**
     * @param string $class
     * @param string $as
     * @param string|Closure $condition
     * @param string $operator
     * @param string $colValue
     * @return $this
     */
    public function rightJoinAs($class, $as, $condition = null, $operator = null, $colValue = null)
    {
        $args = func_get_args();
        unset($args[0], $args[1]);
        return $this->_join('RIGHT', false, [ $class, $as ], ...$args);
    }
    
    /**
     * جوین کردن یک جدول دیگر بصورت کراس
     *
     * @param string|array $class
     * @param string|Closure $condition
     * @param string $operator
     * @param string $colValue
     * @return $this
     */
    public function crossJoin($class, $condition = null, $operator = null, $colValue = null)
    {
        return $this->_join('CROSS', false, ...func_get_args());
    }
    
    /**
     * @param string $class
     * @param string $as
     * @param string|Closure $condition
     * @param string $operator
     * @param string $colValue
     * @return $this
     */
    public function crossJoinAs($class, $as, $condition = null, $operator = null, $colValue = null)
    {
        $args = func_get_args();
        unset($args[0], $args[1]);
        return $this->_join('CROSS', false, [ $class, $as ], ...$args);
    }

    /**
     * جوین کردن یک کوئری دیگر در این کوئری
     *
     * @param string|QueryBuilder $query
     * @param string $as
     * @param string|Closure $condition
     * @param string $operator
     * @param string $colValue
     * @return $this
     */
    public function joinSub($query, $as, $condition = null, $operator = null, $colValue = null)
    {
        if($query instanceof QueryBuilder)
            $query = $query->createQuery();
        $args = func_get_args();
        unset($args[0], $args[1]);
        return $this->_join(null, true, [ $query, $as ], ...$args);
    }
    
    /**
     * جوین کردن یک کوئری دیگر در این کوئری
     *
     * @param string|QueryBuilder $query
     * @param string $as
     * @param string|Closure $condition
     * @param string $operator
     * @param string $colValue
     * @return $this
     */
    public function leftJoinSub($query, $as, $condition = null, $operator = null, $colValue = null)
    {
        if($query instanceof QueryBuilder)
            $query = $query->createQuery();
        $args = func_get_args();
        unset($args[0], $args[1]);
        return $this->_join('LEFT', true, [ $query, $as ], ...$args);
    }
    
    /**
     * جوین کردن یک کوئری دیگر در این کوئری
     *
     * @param string|QueryBuilder $query
     * @param string $as
     * @param string|Closure $condition
     * @param string $operator
     * @param string $colValue
     * @return $this
     */
    public function rightJoinSub($query, $as, $condition = null, $operator = null, $colValue = null)
    {
        if($query instanceof QueryBuilder)
            $query = $query->createQuery();
        $args = func_get_args();
        unset($args[0], $args[1]);
        return $this->_join('RIGHT', true, [ $query, $as ], ...$args);
    }
    
    
    /**
     * جوین کردن یک کوئری دیگر در این کوئری
     *
     * @param string|QueryBuilder $query
     * @param string $as
     * @param string|Closure $condition
     * @param string $operator
     * @param string $colValue
     * @return $this
     */
    public function crossJoinSub($query, $as, $condition = null, $operator = null, $colValue = null)
    {
        if($query instanceof QueryBuilder)
            $query = $query->createQuery();
        $args = func_get_args();
        unset($args[0], $args[1]);
        return $this->_join('CROSS', true, [ $query, $as ], ...$args);
    }

    protected bool $distinct = false;

    /**
     * انتخاب ها را یکتا می کند
     *
     * @return $this
     */
    public function distinct()
    {
        $this->distinct = true;
        return $this;
    }
    
    /**
     * دیتا های مورد نظر برای انتخاب
     *
     * @var string[]
     */
    protected $selects = [];
    
    /**
     * ستون های مورد نظر برای انتخاب
     *
     * @var string[]
     */
    protected $select = [];

    /**
     * افزودن مقدار انتخابی
     *
     * @param string $raw
     * @param string $as
     * @return $this
     */
    public function select($raw, $as = null)
    {
        if($as !== null)
            $raw .= " as " . $this->stringColumn($as);
        $this->selects[] = $raw;

        return $this;
    }

    /**
     * افزودن ستون انتخابی
     *
     * @param string $col
     * @param string $as
     * @return $this
     */
    public function selectCol($col, $as = null)
    {
        $raw = $this->stringColumn($col);
        if($as !== null)
            $raw .= " as " . $this->stringColumn($as);
        $this->selects[] = $raw;

        return $this;
    }

    /**
     * افزودن چندتایی ستون ها
     * 
     * می توانید از آرایه کلید و مقدار دار نیز برای تعریف نام خروجی ستون نیز استفاده کنید
     * 
     * `User::selectCols('id', 'score')->all();`
     * 
     * `User::selectCols([ 'id', 'name' => 'first_name_in_db', 'score' ])->all();`
     *
     * @param string|array $cols
     * @return $this
     */
    public function selectCols($cols)
    {
        if(!is_array($cols))
        {
            $cols = func_get_args();
        }
        
        foreach($cols as $as => $col)
        {
            if(is_string($as))
                $this->selectCol($col, $as);
            else
                $this->selectCol($col);
        }
        
        return $this;
    }

    /**
     * افزودن ستون انتخابی
     *
     * @param string|QueryBuilder $query
     * @param string $as
     * @return $this
     */
    public function selectSub($query, $as)
    {
        if($query instanceof QueryBuilder)
        {
            $query = $query->createQuery(true);
        }

        $this->selects[] = "($query) as " . $this->stringColumn($as, false);
        
        return $this;
    }

    /**
     * افزودن مقدار انتخابی
     *
     * @param string $raw
     * @param string $as
     * @return $this
     */
    public function selectBefore($raw, $as = null)
    {
        if($as !== null)
            $raw .= " as " . $this->stringColumn($as);
        ATool::insert($this->selects, 0, $raw);

        return $this;
    }

    /**
     * افزودن ستون انتخابی
     *
     * @param string $col
     * @param string $as
     * @return $this
     */
    public function selectColBefore($col, $as = null)
    {
        $raw = $this->stringColumn($col);
        if($as !== null)
            $raw .= " as " . $this->stringColumn($as);
        ATool::insert($this->selects, 0, $raw);

        return $this;
    }

    /**
     * افزودن چندتایی ستون ها
     * 
     * می توانید از آرایه کلید و مقدار دار نیز برای تعریف نام خروجی ستون نیز استفاده کنید
     * 
     * `User::selectCols('id', 'score')->all();`
     * 
     * `User::selectCols([ 'id', 'name' => 'first_name_in_db', 'score' ])->all();`
     *
     * @param string|array $cols
     * @return $this
     */
    public function selectColsBefore($cols)
    {
        if(!is_array($cols))
        {
            $cols = func_get_args();
        }
        
        foreach($cols as $as => $col)
        {
            if(is_string($as))
                $this->selectColBefore($col, $as);
            else
                $this->selectColBefore($col);
        }
        
        return $this;
    }

    /**
     * افزودن ستون انتخابی
     *
     * @param string|QueryBuilder $query
     * @param string $as
     * @return $this
     */
    public function selectSubBefore($query, $as)
    {
        if($query instanceof QueryBuilder)
        {
            $query = $query->createQuery(true);
        }

        ATool::insert($this->selects, 0, "($query) as " . $this->stringColumn($as, false));
        
        return $this;
    }

    /**
     * حذف مقدار های ثبت شده انتخابی
     *
     * @return $this
     */
    public function clearSelect()
    {
        $this->selects = [];
        return $this;
    }

    protected array $with = [];
    /**
     * افزودن رابطه برای بارگیری
     * 
     * با افزودن رابطه، تمامی داده ها همزمان و با یک کوئری لود می شوند که باعث بهینگی عملکرد کد ها می شود
     *
     * @param string ...$relation
     * @return $this
     */
    public function with(string ...$relation)
    {
        array_push($this->with, ...$relation);
        return $this;
    }

    /**
     * حذف بارگیری خودکار رابطه
     *
     * @param string ...$relation
     * @return $this
     */
    public function without(string ...$relation)
    {
        foreach($relation as $rel)
        {
            ATool::remove2($this->with, $rel);
        }
        return $this;
    }

    /**
     * تنظیم رابطه برای بارگیری
     * 
     * با افزودن رابطه، تمامی داده ها همزمان و با یک کوئری لود می شوند که باعث بهینگی عملکرد کد ها می شود
     *
     * @param string ...$relation
     * @return $this
     */
    public function withOnly(string ...$relation)
    {
        $this->with = $relation;
        return $this;
    }

    /**
     * افزودن رابطه ها به کوئری
     * 
     * این کار باعث می شود شما بتوانید از شرط ها برای جدول های رابطه استفاده کنید
     * 
     * توجه: این کوئری، گروه بندی را اضافه می کند، بنابر این شرط هایتان را بر اساس آن اضافه کنید
     * 
     * `Post::withQuery('comments')->having(Comment::column('user_id'), $user_id)->select(Post::column('*'))->all();`
     * 
     * @param string ...$relation
     * @return $this
     */
    public function withQuery(string ...$relation)
    {
        if(!$this->selects)
        {
            $this->selectCol($this->output::column('*'));
        }

        foreach($relation as $rel)
        {
            $target = $this->getRelation($rel)->addWithQuery($this);
            if($target)
            {
                $this->selectColBefore($target::column('*'));
            }
        }

        $this->groupBy($this->output::column($this->output::getPrimaryKey()));
        return $this;
    }

    protected $auto_load_relation = true;
    /**
     * بارگیری خودکار رابطه ها را غیرفعال می کند
     *
     * @return $this
     */
    public function disableAutoLoadRelations()
    {
        $this->auto_load_relation = false;
        return $this;
    }

    // /**
    //  * کد های درون تابع شما اجرا می شود و سپس در آخر، رابطه ها لود می شوند
    //  *
    //  * @param Closure $callback
    //  * @return mixed
    //  */
    // public function lazyLoadRelations(Closure $callback)
    // {
    //     $this->disableAutoLoadRelations();
    //     $value = $callback($this);

    //     if($value instanceof Table\Table)
    //     {
    //         $this->loadRelationsFor([$value]);
    //     }
    //     elseif(is_array($value))
    //     {
    //         $this->loadRelationsFor($value);
    //     }
    //     elseif($value instanceof Arr)
    //     {
    //         $this->loadRelationsFor($value->toArray());
    //     }

    //     return $value;
    // }


    /**
     * بارگیری کردن رابطه ها
     *
     * @param array $models
     * @return void
     */
    public function loadRelationsFor(array $models, ?array $with = null, bool $split = true)
    {
        // Split models by types
        if($split)
        {
            $models = $this->splitModelsByType($models);
            if(count($models) != 1)
            {
                foreach($models as $modelGroup)
                {
                    $this->loadRelationsFor($modelGroup, $with, false);
                }
                return;
            }
            else
            {
                $models = first($models) ?? [];
            }
        }

        $subs = [];
        if(is_null($with)) $with = $this->with;
        foreach($with as $name)
        {
            // Sub relation
            if(Text::contains($name, '.'))
            {
                $rel = Text::before($name, '.');
                $sub = Text::after($name, '.');
                @$subs[$rel][] = $sub;
                if(in_array($rel, $with))
                {
                    continue;
                }
                $name = $rel;
            }

            $this->loadRelationFor($name, $models);
        }

        // Sub with
        foreach($subs as $rel => $subNames)
        {
            $subModels = [];
            foreach($models as $model)
            {
                if($model->$rel instanceof Arr)
                {
                    array_push($subModels, ...$model->$rel);
                }
                else
                {
                    $subModels[] = $model->$rel;
                }
            }

            $this->loadRelationsFor($subModels, $subNames);
        }
    }
    /**
     * بارگیری کردن یک رابطه
     *
     * @param string $name
     * @param array $models
     * @return void
     */
    public function loadRelationFor(string $name, array $models)
    {
        foreach($models as $i => $model)
        {
            if(isset($model->$name))
            {
                unset($models[$i]);
            }
        }

        if($models)
        {
            $this->getRelation($name, first($models))->getRelationsFor($name, array_values($models));
        }
    }

    /**
     * جدا سازی مدل ها بر اساس نوع آنها
     *
     * @param array $models
     * @return array
     */
    protected function splitModelsByType(array $models)
    {
        $split = [];
        foreach($models as $model)
        {
            @$split[get_class($model)][] = $model;
        }

        return $split;
    }

    /**
     * گرفتن رابطه از مدل خروجی
     *
     * @param string $name
     * @return Relation\Relation
     */
    public function getRelation(string $name, ?Table\Table $model = null)
    {
        $out = $model ?? app($this->output);
        if(!method_exists($out, $name) || !(($relation = $out->$name()) instanceof Relation\Relation))
        {
            throw new InvalidArgumentException("Relation '$name' not found for '" . typeOf($model) . "'");
        }

        return $relation;
    }

    /**
     * گرفتن انتخاب ها
     *
     * @param string|array|null $select
     * @return array
     */
    protected function getSelects($select = null)
    {
        if(is_null($select))
        {
            return $this->selects ?: ['*'];
        }
        elseif(is_array($select))
        {
            return array_map([$this, 'stringColumn'], $select);
        }
        else
        {
            return [ $this->stringColumn($select) ];
        }
    }

    protected function getSingleModelFrom(Table\Table $model)
    {
        return $model;
    }
    protected function getModelFrom(Table\Table $model)
    {
        if($this->auto_load_relation)
        {
            $this->loadRelationsFor([ $model ]);
        }
        return $this->getSingleModelFrom($model);
    }
    protected function getModelsFrom(array $models)
    {
        if($this->auto_load_relation)
        {
            $this->loadRelationsFor($models);
        }
        foreach($models as $i => $model)
        {
            $models[$i] = $this->getSingleModelFrom($model);
        }
        
        return $models;
    }

    /**
     * گرفتن کل مقدار ها
     *
     * @param array|string $select
     * @return Arr<R|Table\Table>
     */
    public function all($select = null)
    {
        $res = $this->run('select', [
            'select' => $this->getSelects($select),
        ]);

        if(!$res->ok)
            return arr([]);

        return arr($this->getModelsFrom($res->fetchAllAs($this->output)));
    }

    /**
     * گرفتن کل مقدار ها بصورت مپ
     *
     * @param ?string $key
     * @return Map<R|Table\Table>
     */
    public function allAssoc($key = null)
    {
        if(!$key)
        {
            $out = $this->output;
            $key = $out::getPrimaryKey();
        }

        return $this->all()->assocBy($key);
    }

    /**
     * گرفتن یک ستون مشخص
     *
     * @param string $select
     * @return Arr
     */
    public function pluck(string $select)
    {
        $res = $this->run('select', [
            'select' => $this->getSelects($select),
        ]);

        if(!$res->ok)
            return arr([]);

        return arr($res->fetchPluck($select));
    }

    /**
     * گرفتن دو ستون خاص از تمامی ردیف های خروجی به عنوان کلید و مقدار آرایه
     *
     * @param string $key
     * @param string $value
     * @return Map
     */
    public function pluckAssoc(string $key, string $value)
    {
        $res = $this->run('select', [
            'select' => $this->getSelects([ $key, $value ]),
        ]);

        if(!$res->ok)
            return map([]);

        return map($res->fetchPluckAssoc($key, $value));
    }

    /**
     * گروه بندی و پلاک کردن از جدول
     * 
     * `$usersHasPost = Post::pluckGroup('user_id');`
     *
     * @deprecated
     * @param string $select
     * @return Arr
     */
    public function pluckGroup(string $select)
    {
        return $this->groupBy($select)->pluck($select);
    }

    /**
     * مقدار های یکتای یک ستون را می گیرد
     *
     * @return Arr
     */
    public function pluckUnique(string $select)
    {
        return $this->distinct()->pluck($select);
    }

    /**
     * ایجاد کوئری بدون اجرا شدن
     *
     * @return string
     */
    public function createQuery($oneRow = false)
    {
        if($oneRow)
            return $this->run('select', [
                'select' => $this->getSelects(null),
                'limit' => 1,
            ], true);
        else
            return $this->run('select', [
                'select' => $this->getSelects(null),
            ], true);
    }

    /**
     * گرفتن یک سلول
     *
     * @param string|null $select
     * @param mixed $default
     * @return mixed
     */
    public function getCell($select = null, $default = false)
    {
        $res = $this->run('select', [
            'select' => $this->getSelects($select),
            'limit' => 1,
        ]);

        if(!$res->ok)
            return $default;

        return $res->fetchCell();
    }

    /**
     * گرفتن اولین ردیف
     * 
     * Alias: get()
     *
     * @param array|string $select
     * @return R|Table\Table|false
     */
    public function first($select = null)
    {
        return $this->get($select);
    }

    /**
     * پیدا کردن یک مقدار
     *
     * @param mixed $value
     * @param string $findBy
     * @return R|Table\Table|false
     */
    public function find($value, $findBy = null)
    {
        if(is_null($findBy))
        {
            $findBy = $this->output::getPrimaryKey();
        }

        return $this->newQuery()->where($findBy, $value)->get();
    }

    /**
     * پیدا کردن یک مقدار
     *
     * @param mixed $value
     * @param mixed $default
     * @param string $findBy
     * @return R|Table\Table|false
     */
    public function findOr($value, $default, $findBy = null)
    {
        if(is_null($findBy))
        {
            $findBy = $this->output::getPrimaryKey();
        }

        return $this->newQuery()->where($findBy, $value)->getOr($default);
    }

    /**
     * پیدا کردن یک مقدار
     *
     * @param mixed $value
     * @param string $error
     * @param string $findBy
     * @return R|Table\Table|false
     */
    public function findOrError($value, string $error, $findBy = null)
    {
        if(is_null($findBy))
        {
            $findBy = $this->output::getPrimaryKey();
        }

        return $this->newQuery()->where($findBy, $value)->getOrError($error);
    }

    /**
     * گرفتن اولین ردیف
     *
     * @param array|string $select
     * @return R|Table\Table|false
     */
    public function get($select = null)
    {
        $res = $this->run('select', [
            'select' => $this->getSelects($select),
            'limit' => 1,
        ]);

        if(!$res->ok)
            return false;

        $model = $res->fetchAs($this->output);
        return $model ? $this->getModelFrom($model) : $model;
    }

    /**
     * گرفتن اولین ردیف و یا ساختن آن در صورت عدم وجود
     *
     * `$post->info()->getOrCreate([ 'hastags' => ['A', 'B'] ]);`
     * 
     * `$user->info()->getOrCreate(function() { return [ 'name' => UserInfo::$this->id; ]; });`
     * 
     * @param array|Closure $data
     * @return R|Table\Table|false
     */
    public function getOrCreate($data = [])
    {
        if($result = $this->get())
        {
            return $result;
        }

        if(!is_array($data))
            $data = $data();

        return $this->create($data);
    }

    /**
     * گرفتن اولین ردیف و یا اجرا شدن خطای کاربر در صورت عدم وجود
     * 
     * این خطا اگر هندل نشود، بصورت پیام به کاربر ارسال می شود
     *
     * @param string|null $message
     * @throws ExtraErrorMessage
     * @return R|Table\Table
     */
    public function getOrError($message = null)
    {
        if($result = $this->get())
        {
            return $result;
        }

        if(is_null($message))
        {
            $message = lang("erros.notfound");
        }
        
        throw new ExtraErrorMessage($message);
    }

    /**
     * گرفتن اولین ردیف و یا اجرا از تابع ورودی در صورت عدم وجود
     *
     * @param Closure|mixed $default
     * @return R|Table\Table|mixed
     */
    public function getOr($default)
    {
        if($result = $this->get())
        {
            return $result;
        }

        return value($default);
    }

    /**
     * کرفتن تعداد نتایج
     * 
     * اگر گروه بندی شده باشد، تعداد گروه ها را بر می گرداند
     *
     * @param string $of
     * @return int
     */
    public function count($of = '*')
    {
        if($this->groupBy)
        {
            return $this->newQuery()
                ->select("COUNT($of)")
                ->run('select', [
                    'select' => $this->getSelects(),
                ])
                ->fetchCount();
            // return Db::query()
            //     ->db($this->db_driver)
            //     ->from(
            //         $this
            //             ->newQuery()
            //             ->select("COUNT($of) as `inner_count`"),
            //         'query'
            //     )
            //     ->count();
        }

        $res = $this->run('select', [
            'select' => [ "COUNT($of) as `count`" ],
        ]);

        if(!$res->ok)
            return 0;
        
        return $res->fetch()['count'] ?? 0;
    }

    /**
     * بررسی وجود
     *
     * @return boolean
     */
    public function exists()
    {
        $res = $this->run('select', [
            'select' => [ "COUNT(*) as `count`" ],
            'limit' => 1,
        ]);

        if(!$res->ok)
            return false;
        
        return $res->fetch()['count'] ? true : false;
    }

    /**
     * حذف اولین ردیف
     *
     * @return bool
     */
    public function deleteFirst()
    {
        return $this->run('delete', [ 'limit' => 1 ])->ok;
    }

    /**
     * حذف ردیف ها
     *
     * @return bool
     */
    public function delete()
    {
        return $this->run('delete')->ok;
    }

    /**
     * مقدار های اینسرت/آپدیت
     *
     * @var array
     */
    protected $insert;

    /**
     * بروزرسانی ردیف ها
     *
     * @param array|Arrayable $data آرایه ای شامل کلید=نام ستون و مقدار=مقدار
     * @param bool $modify اگر ترو باشد، داده ها را با توحه به نوع ستون های مدل تبدیل می کند
     * @return bool
     */
    public function update(array|Arrayable $data, bool $modify = true)
    {
        if($data instanceof Arrayable)
        {
            $data = $data->toArray();
        }

        if(!$data)
            return false;

        $output = $this->output;
        if($modify)
        {
            $output::modifyOutArray($data);
        }
        $data = $output::onUpdateQueryStatic($data);
        $this->insert = $this->stringColumnMap($data);

        return $this->run('update')->ok;
    }

    /**
     * ایجاد ردیف
     *
     * @param array|Arrayable|QueryBuilder $data آرایه ای شامل کلید=نام ستون و مقدار=مقدار
     * @param bool $modify اگر ترو باشد، داده ها را با توحه به نوع ستون های مدل تبدیل می کند
     * @return R|Table\Table|boolean
     */
    public function insert($data = [], bool $modify = true)
    {
        if($data instanceof Arrayable)
        {
            $data = $data->toArray();
        }
        $output = $this->output;
        if($data instanceof QueryBuilder)
        {
            $this->insert = $data->createQuery();
        }
        elseif(is_array($data))
        {
            // Listener
            if($modify)
            {
                $output::modifyOutArray($data);
            }
            $data = $output::onCreateQuery($data);
            $this->insert = $this->stringColumnMap($data);
        }
        else
        {
            throw new MmbException("Array or QueryBuilder required in insert()");
        }

        $res = $this->run('insert');

        if(!$res->ok)
            return false;

        if(is_array($data))
        {
            $primary = $output::getPrimaryKey();
            if($primary && !isset($data[$primary]) && $value = $res->insertID()) {
                $data[$primary] = $value;
            }

            $object = new $output($data);
            $object->newCreated = true;
            $object->onCreate();
            
            return $object;
        }

        return true;
    }

    /**
     * ایجاد ردیف
     * 
     * این تابع مقدار های شرطی ثابت را هم به دیتا اضافه می کند
     * 
     * `$tag = Tag::query()->where('name', 'DEMO'); if(!$tag->exists()) $tag->create();`
     * 
     * `$user->posts()->create([ 'title' => "TITLE", 'text' => "TEXT" ]); // For relations`
     * 
     * @param array|Arrayable $data
     * @param bool $modify اگر ترو باشد، داده ها را با توحه به نوع ستون های مدل تبدیل می کند
     * @return R|Table\Table|false
     */
    public function create(array|Arrayable $data = [], bool $modify = true)
    {
        if($data instanceof Arrayable)
        {
            $data = $data->toArray();
        }
        foreach($this->where as $where)
        {
            if($where[0] == 'col' && $where[1] == 'AND' && $where[3] == '=')
            {
                if(!isset($data[$name = str_replace('`', '', $where[2])]))
                {
                    $data[$name] = $where[4];
                }
            }
        }

        return $this->insert($data, true);
    }

    /**
     * ایجاد چند ردیف همرمان
     * 
     * این تابع مقدار های شرطی ثابت را هم به دیتا اضافه می کند
     * 
     * `User::where('age', 10)->create([ ['name' => 'A'], ['name' => 'B'] ]);`
     * 
     * @param array|Arrayable $rows
     * @param bool $modify اگر ترو باشد، داده ها را با توحه به نوع ستون های مدل تبدیل می کند
     * @return bool
     */
    public function createMulti(array|Arrayable $rows = [], bool $modify = true)
    {
        if($rows instanceof Arrayable)
        {
            $rows = $rows->toArray();
        }

        $append = [];
        foreach($this->where as $where)
        {
            if($where[0] == 'col' && $where[1] == 'AND' && $where[3] == '=')
            {
                $append[str_replace('`', '', $where[2])] = $where[4];
            }
        }

        if($append)
        {
            foreach($rows as $i => $row)
            {
                if($row instanceof Arrayable)
                {
                    $row = $row->toArray();
                }
                elseif(!is_array($row))
                {
                    throw new InvalidArgumentException("Required array of array, given " . typeOf($row));
                }

                $rows[$i] = $row + $append;
            }
        }

        return $this->insertMulti($rows, $modify);
    }

    /**
     * ایجاد ردیف
     *
     * @param array|Arrayable $datas آرایه از `آرایه ای شامل کلید=نام ستون و مقدار=مقدار`
     * @param bool $modify اگر ترو باشد، داده ها را با توحه به نوع ستون های مدل تبدیل می کند
     * @return bool
     */
    public function insertMulti(array|Arrayable $datas, bool $modify = true)
    {
        if($datas instanceof Arrayable)
        {
            $datas = $datas->toArray();
        }

        if(!$datas)
            return true;

        // Listeners
        $output = $this->output;
        foreach($datas as $index => $data)
        {
            if($data instanceof Arrayable)
            {
                $data = $data->toArray();
            }
            if(!is_array($data))
            {
                throw new Exception("insertMulti() required array<array>");
            }

            if($modify)
            {
                $output::modifyOutArray($data);
            }
            $data = $output::onCreateQuery($data);
            $datas[$index] = $this->stringColumnMap($data);
        }

        $this->insert = array_values($datas);
        
        return $this->run('insert_multi')->ok;
    }

    /**
     * صفحه بندی
     *
     * @param integer $page
     * @param integer $perPage
     * @param string|null $error
     * @return Paginate<R>
     */
    public function paginate(int $page, int $perPage = 20, ?string $error = "صفحه یافت نشد")
    {
        return new Paginate($this, $page, $perPage, $error);
    }

    /**
     * ایندکس
     *
     * @var SingleIndex
     */
    protected $singleIndex;

    /**
     * افزودن ایندکس
     *
     * @param SingleIndex $index
     * @return bool
     */
    public function addIndex(SingleIndex $index)
    {
        $this->singleIndex = $index;
        return $this->run('addIndex')->ok;
    }

    /**
     * ستون ها
     *
     * @var QueryCol
     */
    protected $queryCol;

    /**
     * ساخت جدول جدید
     *
     * @param string $name
     * @param callable $column_initialize `function(\Mmb\Db\QueryCol $query) { }`
     * @return bool
     */
    public function createTable($name, $column_initialize = null)
    {
        $this->table = static::stringColumn($name);
        $this->queryCol = new QueryCol($name);
        if($column_initialize)
            $column_initialize($this->queryCol);

        if($this->run('createTable')->ok)
        {
            // Add foreign keys
            foreach($this->queryCol->getColumns() as $col)
            {
                if($col->foreign_key)
                {
                    $this->addForeignKey($name, $col->name, $col->foreign_key);
                }
            }

            // Add index
            foreach($this->queryCol->getIndexs() as $index)
            {
                $this->addIndex($index);
            }

            $output = $this->output;
            $output::onCreateTable();

            return true;
        }

        return false;
    }

    /**
     * ساخت یا جدول
     *
     * @param string $name
     * @param callable $column_initialize `function(\Mmb\Db\QueryCol $query) { }`
     * @return bool
     */
    public function createOrEditTable($name, $column_initialize = null)
    {
        try
        {
            $before = $this->getTable($name);
        }
        catch(Exception $e)
        {
            return $this->createTable($name, $column_initialize);
        }

        $after = new QueryCol($name);
        if($column_initialize)
            $column_initialize($after);

        // Upgrade events (Before)
        $defaultAfterEvents = $after->fireInstallBefore($before);

        // Get old
        $before_cols = [];
        foreach($before->getColumns() as $col)
        {
            $before_cols[$col->name] = $col;
        }

        // Find changes
        $lastColumn = false;
        foreach($after->getColumns() as $newColumn)
        {
            if($oldColumn = $before_cols[$newColumn->name] ?? false)
            {
                // Exists
                unset($before_cols[$newColumn->name]);
            }
            elseif($oldName = $newColumn->searchNameIn($before_cols))
            {
                $oldColumn = $before_cols[$oldName];
                unset($before_cols[$oldName]);
            }
            else
            {
                // Not exists
                if($lastColumn)
                {
                    $newColumn->after($lastColumn->name);
                }
                else
                {
                    $newColumn->first();
                }
                $this->addColumn($name, $newColumn);
                $lastColumn = $newColumn;
                continue;
            }

            if(json_encode($newColumn) != json_encode($oldColumn))
            {
                // Can't handle
                // if($newColumn->autoIncrement || $newColumn->primaryKey) continue;

                // Edited
                $this->editColumn2($name, $oldColumn, $newColumn);
            }

            $lastColumn = $col;
        }

        // Removed columns
        foreach($before_cols as $col)
        {
            $this->removeColumn($name, $col->name);
        }

        // Upgrade events (After)
        $after->fireInstallAfter($defaultAfterEvents);

        return true;
    }

    /**
     * گرفتن اطلاعات جدول
     *
     * @param string $name
     * @return \Mmb\Db\QueryCol
     */
    public function getTable($name)
    {
        $this->table = $this->stringColumn($name);

        return $this->run('showColumns')->toQueryCol($name, $this->run('showIndexs'));
    }

    protected $colName;

    protected $col;

    
    /**
     * ویرایش ستون
     *
     * @param string $table
     * @param string $before_name
     * @param \Mmb\Db\SingleCol $col
     * @return bool
     */
    public function editColumn($table, $before_name, \Mmb\Db\SingleCol $col)
    {
        $this->table = $this->stringColumn($table);
        $this->colName = $this->stringColumn($before_name);
        $this->col = $col;

        return $this->run('editColumn')->ok;
    }

    /**
     * ویرایش ستون
     *
     * @param string $table
     * @param \Mmb\Db\SingleCol $old
     * @param \Mmb\Db\SingleCol $new
     * @return bool
     */
    public function editColumn2($table, \Mmb\Db\SingleCol $old, \Mmb\Db\SingleCol $new)
    {
        $this->table = $this->stringColumn($table);

        /** @var \Mmb\Db\SingleCol */
        $newCloned = clone $new;
        
        if($old->primaryKey != $new->primaryKey)
        {
            if(!$new->primaryKey) {
                // Remove autoincrement
                if($old->autoIncrement && !$new->autoIncrement) {
                    $old->autoIncrement = false;
                    $old->primaryKey = false;
                    $uniqOld = $old->unique;
                    $old->unique = false;
                    $this->editColumn($table, $old->name, $old);
                    $old->autoIncrement = true;
                    $old->primaryKey = true;
                    $old->unique = $uniqOld;
                }
                // Remove primary key
                $this->removePrimaryKey($table);
            }
        }
        else
            $newCloned->primaryKey = null;

        if($old->unique != $new->unique) {
            if(!$new->unique)
                $this->removeIndex($table, $old->name);
        }
        else
            $newCloned->unique = null;

        if($result = $this->editColumn($table, $old->name, $newCloned))
        {
            // Foreign key
            if($old->foreign_key && !$new->foreign_key)
            {
                $this->removeForeignKeyAndIndex($table, $old->foreign_key->constraint);
            }
            elseif(!$old->foreign_key && $new->foreign_key)
            {
                $this->addForeignKey($table, $new->name, $new->foreign_key);
            }
            elseif($old->foreign_key && $new->foreign_key)
            {
                if(
                    $old->foreign_key->table != $new->foreign_key->table ||
                    $old->foreign_key->column != $new->foreign_key->column ||
                    $old->foreign_key->constraint != $new->foreign_key->constraint
                )
                {
                    $this->removeForeignKeyAndIndex($table, $old->foreign_key->constraint);
                    $this->addForeignKey($table, $new->name, $new->foreign_key);
                }
            }
        }

        return $result;
    }

    /**
     * افزودن ستون
     *
     * @param string $table
     * @param \Mmb\Db\SingleCol $col
     * @return bool
     */
    public function addColumn($table, \Mmb\Db\SingleCol $col)
    {
        $this->table = $this->stringColumn($table);
        $this->col = $col;

        return $this->run('addColumn')->ok;
    }

    /**
     * حذف ستون
     *
     * @param string $table
     * @param string $col
     * @return bool
     */
    public function removeColumn($table, $col)
    {
        $this->table = $this->stringColumn($table);
        $this->colName = $this->stringColumn($col);

        return $this->run('removeColumn')->ok;
    }

    /**
     * حذف ایندکس
     *
     * @param string $table
     * @param string $col
     * @return bool
     */
    public function removeIndex($table, $col)
    {
        $this->table = $this->stringColumn($table);
        $this->colName = $this->stringColumn($col);

        return $this->run('removeIndex')->ok;
    }

    /**
     * حذف کلید اصلی
     *
     * @param string $table
     * @return bool
     */
    public function removePrimaryKey($table)
    {
        $this->table = $this->stringColumn($table);

        return $this->run('removePrimaryKey')->ok;
    }

    /**
     * حذف رابطه و ایندکس
     *
     * @param string $table
     * @param string $name
     * @return boolean
     */
    public function removeForeignKeyAndIndex($table, $col)
    {
        if($this->removeForeignKey($table, $col))
        {
            try {
                $this->removeIndex($table, $col);
            }
            catch(Exception$e) { }
            return true;
        }
        return false;
    }

    /**
     * حذف رابطه
     * 
     * @param string $table
     * @param string $name
     * @return boolean
     */
    public function removeForeignKey($table, $col)
    {
        $this->table = $this->stringColumn($table);
        $this->colName = $this->stringColumn($col);

        return $this->run('removeForeignKey')->ok;
    }

    /**
     * @var Key\Foreign
     */
    public $foreign_key;

    /**
     * افزودن رابطه
     * 
     * @param string $table
     * @param string $name
     * @return boolean
     */
    public function addForeignKey($table, $col, Key\Foreign $foreign)
    {
        $this->table = $this->stringColumn($table);
        $this->colName = $this->stringColumn($col);
        $this->foreign_key = $foreign;

        return $this->run('addForeignKey')->ok;
    }

    /**
     * پیدا کردن ریلیشن در مای اسکیوال
     *
     * @param string $col
     * @return Table\Table|false
     */
    public function findMySqlForeingKeyRelation($table, $col)
    {
        $dbname = $this->db_driver->getName();
        return $this->table('information_schema.KEY_COLUMN_USAGE')
                ->clearSelect()
                ->select('CONSTRAINT_NAME', 'constraint')
                ->select('REFERENCED_COLUMN_NAME', 'column')
                ->select('REFERENCED_TABLE_NAME', 'table')
                ->where('CONSTRAINT_SCHEMA', $dbname)
                ->where('TABLE_NAME', $table)
                ->where('COLUMN_NAME', $col)
                ->get();
    }

    /**
     * ایجاد یک کوئری خالی جدید با درایور این کوئری
     *
     * @return static<R>
     */
    public function newBlankQuery()
    {
        return Db::query()->db($this->db_driver);
    }

    /**
     * ایجاد یک کوئری جدید با ویژگی های این کوئری
     *
     * @return static<R>
     */
    public function newQuery()
    {
        return clone $this;
    }

    /**
     * ایجاد یک کوئری جدید با ویژگی های این کوئری
     *
     * @template Q
     * @param class-string<Q> $class
     * @return Q<R>
     */
    public function newQueryAs(string $class)
    {
        $query = new $class;
        foreach(get_object_vars($this) as $key => $value)
        {
            $query->$key = $value;
        }

        return $query;
    }

    /**
     * ایجاد یک کوئری جدید با ویژگی های این کوئری
     * 
     * نام یک کلاس مدل را می دهید و با آن کوئری می سازد
     *
     * @template Q
     * @param class-string<Q> $class
     * @return QueryBuilder<Q>
     */
    public function newQueryFrom(string $class)
    {
        $query = $class::query();
        foreach(get_object_vars($this) as $key => $value)
        {
            if(in_array($key, [ 'where', 'select', 'having' ]))
            {
                array_push($query->$key, ...$value);
            }
            else
            {
                $query->$key = $value;
            }
        }
        $query
            ->table($class::getTableName())
            ->output($class);

        return $query;
    }

    public function __call($name, $args)
    {
        // Where
        if(startsWith($name, 'where', true))
        {
            $col = Text::snake(substr($name, 5));
            return $this->where($col, ...$args);
        }
        if(startsWith($name, 'andWhere', true))
        {
            $col = Text::snake(substr($name, 8));
            return $this->andWhere($col, ...$args);
        }
        if(startsWith($name, 'orWhere', true))
        {
            $col = Text::snake(substr($name, 7));
            return $this->orWhere($col, ...$args);
        }

        // Custom methods
        if($this->invokeCustomMethod($name, $args, $value))
        {
            return $value;
        }

        // Scopes
        $scope = 'scope' . $name;
        if(method_exists($this->output, $scope))
        {
            $out = $this->output;
            return $out::$scope($this, ...$args);
        }

        throw new BadMethodCallException("Call to undefined method '$name' on " . static::class);
    }

    /**
     * افزودن ` و ایمن سازی نام ستون
     *
     * @param string $column
     * @param boolean $splitTables
     * @return string
     */
    public static function stringColumn(string $column, bool $splitTables = true)
    {
        $column = str_replace('`', '``', $column);
        if($splitTables)
            $column =  '`' . str_replace('.', '`.`', $column) . '`';
        else
            $column = "`{$column}`";
        $column = str_replace('`*`', '*', $column);
        return $column;
    }

    /**
     * افزودن ` به کلید های آرایه
     *
     * @param array|Traversable $map
     * @return array
     */
    public static function stringColumnMap(array|Traversable $map)
    {
        $res = [];
        foreach($map as $key => $value)
        {
            $res[static::stringColumn($key)] = $value;
        }
        return $res;
    }

    // /**
    //  * ایمن کردن رشته
    //  *
    //  * @param string $string
    //  * @return string
    //  */
    // public function stringSafe($string)
    // {
    //     if($string === false) return 0;
    //     if($string === true) return 1;
    //     if($string === null) return 'NULL';

    //     return '"' . addslashes($string) . '"';
    // }

}
