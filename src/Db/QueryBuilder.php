<?php

namespace Mmb\Db; #auto

use BadMethodCallException;
use Closure;
use Exception;
use Mmb\Exceptions\MmbException;
use Mmb\ExtraThrow\ExtraErrorMessage;
use Mmb\Listeners\HasCustomMethod;
use Mmb\Mapping\Arr;
use Mmb\Mapping\Arrayable;
use Mmb\Mapping\Map;
use Mmb\Tools\Text;

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
    private $table;
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

    use QueryHasWhere;
    use QueryHasHaving;

    /**
     * مرتب سازی بر اساس
     *
     * @var array
     */
    private $order = [];
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
    private $limit = false;
    /**
     * محل شروع
     *
     * @var int|false
     */
    private $offset = false;

    /**
     * محدود کردن تعداد انتخاب
     *
     * @param int $limit
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
     * @param int $offset
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
    private $output = Table\Unknown::class;
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


    private $db_driver;
    /**
     * تنظیم دیتابیس مربوطه
     *
     * @param Driver $driver
     * @return $this
     */
    public function db(Driver $driver)
    {
        $this->db_driver = $driver;
        return $this;
    }

    /**
     * اجرای کوئری
     *
     * @param string $type
     * @return QueryResult|string
     */
    private function run($type, $local = [], $exportStringQuery = false)
    {
        $driver = $this->db_driver ?: Driver::defaultStatic();

        $compilerClass = $driver->queryCompiler;
        $compiler = new $compilerClass($type);

        foreach (get_object_vars($this) as $name => $value)
            $compiler->$name = $value;
        foreach ($local as $name => $value)
            $compiler->$name = $value;
            
        $compiler->start($type);

        if($exportStringQuery)
        {
            return $compiler->query;
        }

        return $driver->runQuery($compiler);
    }

    private $joins = [];

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
    
    /**
     * دیتا های مورد نظر برای انتخاب
     *
     * @var string[]
     */
    private $selects = [];
    
    /**
     * ستون های مورد نظر برای انتخاب
     *
     * @var string[]
     */
    private $select = [];

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
     * حذف مقدار های ثبت شده انتخابی
     *
     * @return $this
     */
    public function clearSelect()
    {
        $this->selects = [];
        return $this;
    }

    /**
     * گرفتن کل مقدار ها
     *
     * @param array|string $select
     * @return Arr<R|Table\Table>
     */
    public function all($select = null)
    {
        if($select === null)
            $select = $this->selects ?: ['*'];
        elseif(!is_array($select))
            $select = [ $this->stringColumn($select) ];
        else
            $select = array_map([$this, 'stringColumn'], $select);
        $this->select = $select;

        $res = $this->run('select');

        if(!$res->ok)
            return arr([]);

        return arr($res->fetchAllAs($this->output));
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
        // $result = [];
        // foreach($all as $row)
        // {
        //     $result[$row->$key] = $row;
        // }

        // return map($result);
    }

    /**
     * گرفتن یک ستون مشخص
     *
     * @param string $select
     * @return Arr
     */
    public function pluck($select)
    {
        $this->select = [ $this->stringColumn($select) ];

        $res = $this->run('select');

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
    public function pluckAssoc($key, $value)
    {
        $this->select = [ $this->stringColumn($key), $this->stringColumn($value) ];

        $res = $this->run('select');

        if(!$res->ok)
            return map([]);

        return map($res->fetchPluckAssoc($key, $value));
    }

    /**
     * گروه بندی و پلاک کردن از جدول
     * 
     * `$usersHasPost = Post::pluckGroup('user_id');`
     *
     * @param string $select
     * @return Arr
     */
    public function pluckGroup($select)
    {
        return $this->groupBy($select)->pluck($select);
    }

    /**
     * ایجاد کوئری بدون اجرا شدن
     *
     * @return string
     */
    public function createQuery($oneRow = false)
    {
        $this->select = $this->selects ?: ['*'];
        if($oneRow)
            return $this->run('select', [ 'limit' => $oneRow ], true);
        else
            return $this->run('select', [], true);
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
        if($select === null)
            $select = $this->selects ?: ['*'];
        elseif(!is_array($select))
            $select = [ $this->stringColumn($select) ];
        else
            $select = array_map([$this, 'stringColumn'], $select);
        $this->select = $select;

        $res = $this->run('select', [ 'limit' => 1 ]);

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
     * گرفتن اولین ردیف
     *
     * @param array|string $select
     * @return R|Table\Table|false
     */
    public function get($select = null)
    {
        if($select === null)
            $select = $this->selects ?: ['*'];
        elseif(!is_array($select))
            $select = [ $this->stringColumn($select) ];
        else
            $select = array_map([$this, 'stringColumn'], $select);
        $this->select = $select;

        $res = $this->run('select', [ 'limit' => 1 ]);

        if(!$res->ok)
            return false;

        return $res->fetchAs($this->output);
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
     * @param Closure|mixed $callback
     * @return R|Table\Table|mixed
     */
    public function getOr($callback)
    {
        if($result = $this->get())
        {
            return $result;
        }

        if($callback instanceof Closure)
        {
            $callback = $callback();
        }

        return $callback;
    }

    /**
     * کرفتن تعداد نتایج
     *
     * @param string $of
     * @return int
     */
    public function count($of = '*')
    {
        $this->select = [ "COUNT($of) as `count`" ];

        $res = $this->run('select');

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
        $this->select = [ 'COUNT(*) as `count`' ];

        $res = $this->run('select', [ 'limit' => 1 ]);

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
    private $insert;

    /**
     * بروزرسانی ردیف ها
     *
     * @param array|Arrayable $data آرایه ای شامل کلید=نام ستون و مقدار=مقدار
     * @return bool
     */
    public function update(array|Arrayable $data)
    {
        if($data instanceof Arrayable)
        {
            $data = $data->toArray();
        }

        if(!$data)
            return false;

        $output = $this->output;
        $output::modifyOutArray($data);
        $data = $output::onUpdateQueryStatic($data);
        $this->insert = $this->stringColumnMap($data);

        return $this->run('update')->ok;
    }

    /**
     * ایجاد ردیف
     *
     * @param array|Arrayable|QueryBuilder $data آرایه ای شامل کلید=نام ستون و مقدار=مقدار
     * @return R|Table\Table|boolean
     */
    public function insert($data = [])
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
            $output::modifyOutArray($data);
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
     * @return R|Table\Table|false
     */
    public function create(array|Arrayable $data = [])
    {
        if($data instanceof Arrayable)
        {
            $data = $data->toArray();
        }
        foreach($this->where as $where)
        {
            if($where[0] == 'col' && $where[1] == 'AND' && $where[3] == '=')
            {
                $data[str_replace('`', '', $where[2])] = $where[4];
            }
        }

        return $this->insert($data);
    }

    /**
     * ایجاد ردیف
     *
     * @param array|Arrayable $datas آرایه از `آرایه ای شامل کلید=نام ستون و مقدار=مقدار`
     * @return bool
     */
    public function insertMulti(array|Arrayable $datas)
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

            $data = $output::onCreateQuery($data);
            $datas[$index] = $this->stringColumnMap($data);
        }

        $this->insert = $datas;
        
        return $this->run('insert_multi')->ok;
    }


    /**
     * ستون ها
     *
     * @var QueryCol
     */
    private $queryCol;

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
        $this->queryCol = new QueryCol;
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
    public function createOrEditTable($name, $column_initialize = null) {

        try
        {
            $before = $this->getTable($name);
        }
        catch(Exception $e)
        {
            return $this->createTable($name, $column_initialize);
        }

        $after = new QueryCol;
        if($column_initialize)
            $column_initialize($after);

        // Get old
        $before_cols = [];
        foreach($before->getColumns() as $col)
        {
            $before_cols[$col->name] = $col;
        }

        // Find changes
        $last = false;
        foreach($after->getColumns() as $col)
        {
            if($col2 = $before_cols[$col->name] ?? false) {
                // Exists
                unset($before_cols[$col->name]);
            }
            else {
                // Not exists
                if($last)
                    $col->after($last->name);
                else
                    $col->first();
                $this->addColumn($name, $col);
                $last = $col;
                continue;
            }

            if(json_encode($col) != json_encode($col2)) {
                
                // Can't handle
                // if($col->autoIncrement || $col->primaryKey) continue; // !

                // Edited
                $this->editColumn2($name, $col2, $col);

            }

            $last = $col;
        }

        // Removed columns
        foreach($before_cols as $col)
        {
            $this->removeColumn($name, $col->name);
        }

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

        return $this->run('getTable')->toQueryCol($name);
    }

    private $colName;

    private $col;

    
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
    
        return $this->editColumn($table, $old->name, $newCloned);
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
     * حذف رابطه
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

    public function stringColumn($column, $splitTables = true)
    {
        $column = str_replace('`', '``', $column);
        if($splitTables)
            $column =  '`' . str_replace('.', '`.`', $column) . '`';
        else
            $column = "`{$column}`";
        $column = str_replace('`*`', '*', $column);
        return $column;
    }

    public function stringColumnMap($map)
    {
        $res = [];
        foreach($map as $key => $value)
        {
            $res[$this->stringColumn($key)] = $value;
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
