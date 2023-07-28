<?php

namespace Mmb\Db\Table; #auto

use ArrayAccess;
use JsonSerializable;
use Mmb\Calling\DynCall;
use Mmb\Db\QueryBuilder;
use Mmb\Db\QueryCol;
use Mmb\Db\Relation\ManyToMany;
use Mmb\Db\Relation\OneToMany;
use Mmb\Db\Relation\OneToOne;
use Mmb\Db\Relation\Relation;
use Mmb\Exceptions\MmbException;
use Mmb\Mapping\Arr;
use Mmb\Mapping\Arrayable;
use Mmb\Tools\Text;

class Table implements JsonSerializable, ArrayAccess
{
    
    /**
     * دیتای قبلی
     *
     * @var array
     */
    public $oldData;

    /**
     * کل دیتا
     *
     * @var array
     */
    public $allData;

    /**
     * نام های تغییر یافته
     *
     * @var array
     */
    public $changedCols;

    /**
     * آیا تازه ساخته شده است
     * 
     * تنها زمانی که با تابع کریت یا اینسرت ساخته شود این مقدار ترو می شود
     *
     * @var boolean
     */
    public $newCreated = false;
    
    public function __construct($data)
    {
        $this->allData = $data;
        $this->oldData = $data;

        $this->modifyDataIn($data);

        foreach($this->getGenerator()->getColumns() as $col)
        {
            if(array_key_exists($col->name, $data))
            {
                $this->allData[$col->name]
                    = $col->dataIn($data[$col->name], $this);
            }
        }

        $this->changedCols = [];
    }

    /**
     * گرفتن دیتای جدید
     *
     * @return array
     */
    public final function getNewData()
    {
        $res = [];

        foreach($this->getGenerator()->getColumns() as $col)
        {
            if(array_key_exists($col->name, $this->allData))
            {
                $res[$col->name]
                    = $col->dataOut($this->allData[$col->name], $this);
            }
        }

        $this->modifyDataOut($res);
        return $res;
    }

    /**
     * گرفتن دیتای تغییر یافته
     *
     * @return array
     */
    public final function getChangedData()
    {
        $res = [];

        foreach($this->getGenerator()->getColumns() as $col)
        {
            $newExists = array_key_exists($col->name, $this->allData);
            if($newExists)
            {
                $oldExists = array_key_exists($col->name, $this->oldData);
                // Check if changed with "$model->data = new;"
                if(!$oldExists || $col->always_save || in_array($col->name, $this->changedCols))
                {
                    $res[$col->name]
                        = $col->dataOut($this->allData[$col->name], $this);
                }
                // Check if changed output data
                elseif($col->hasOutModifier())
                {
                    $new = $col->dataOut($this->allData[$col->name], $this);
                    if($new !== $this->oldData[$col->name])
                    {
                        $res[$col->name]
                            = $col->dataOut($this->allData[$col->name], $this);
                    }
                }
            }
        }

        $this->modifyDataOut($res);
        return $res;
    }

    /**
     * گرفتن نام تیبل
     *
     * @return string
     */
    public static function getTable()
    {
        $exp = explode("\\", static::class);

        return Text::snake(end($exp)) . "s";
    }

    public static final function getTableName()
    {
        return static::$tablesPrefix . static::getTable();
    }

    public static $tablesPrefix = '';
    public static function setPrefix($prefix)
    {
        static::$tablesPrefix = $prefix;
    }

    /**
     * این تابع زمان ایجاد جدول صدا زده می شود تا اطلاعات آن را پر کند
     *
     * @param QueryCol $table
     * @return void
     */
    public static function generate(QueryCol $table)
    {
    }

    protected static $_generate_query_cols = [];
    /**
     * گرفتن جنریتور
     *
     * @return QueryCol
     */
    public static final function getGenerator()
    {
        if(isset(static::$_generate_query_cols[static::class]))
            return static::$_generate_query_cols[static::class];

        static::$_generate_query_cols[static::class] = $query = new QueryCol;
        static::generate($query);
        return $query;
    }
    
    /**
     * ایجاد یا ویرایش جدول
     *
     * @return bool
     */
    public static function createOrEditTable()
    {
       return (new QueryBuilder)->createOrEditTable(static::getTableName(), [ static::class, 'generate' ]);
    }

    /**
     * ایجاد یا ویرایش جدول ها
     *
     * @param array $tables آرایه ای از نام کلاس های جداول
     * @return bool
     */
    public static function createOrEditTables($tables)
    {
        foreach($tables as $table) {
            if(!$table::createOrEditTable())
                return false;
        }

        return true;
    }

    /**
     * گرفتن کلید یکتای جدول
     *
     * @return string
     */
    public static function getPrimaryKey()
    {
        return 'id';
    }

    /**
     * گرفتن مقدار یکتای جدول
     *
     * @return mixed
     */
    public function getPrimaryValue()
    {
        $primary = static::getPrimaryKey();
        return $this->$primary;
    }


    /**
     * مدیریت دیتا برای کلاس
     *
     * @param array $data
     * @return void
     */
    public function modifyDataIn(array &$data)
    {
    }

    /**
     * مدیریت دیتا برای ذخیره
     *
     * @param array $data
     * @return void
     */
    public function modifyDataOut(array &$data)
    {
    }

    /**
     * ایجاد یک کوئری
     * 
     * این تابع، ریشه تمام توابع ایجاد کوئری ست
     *
     * @template T of QueryBuilder
     * @param class-string<T> $class
     * @return T<static>
     */
    public static function createQuery(string $class)
    {
        return (new $class)
                ->table(static::getTableName())
                ->output(static::class);
    }

    /**
     * ایجاد یک کوئری بیلدر
     *
     * @return QueryBuilder<static>
     */
    public static function query()
    {
    return static::createQuery(QueryBuilder::class);
    }

    /**
     * ایجاد یک کوئری بیلدر با کلاس مورد نظر
     *
     * @template T of QueryBuilder
     * @param class-string<T> $class
     * @return T<static>|QueryBuilder<static>
     */
    public static function queryWith($class)
    {
        return static::createQuery($class);
    }

    /**
     * ایجاد یک کوئری بیلدر همراه با شرط این ردیف بودن
     *
     * @return QueryBuilder<static>
     */
    public function queryThis()
    {
        $primary = static::getPrimaryKey();

        return static::query()
                ->where($primary, $this->$primary);
    }



    use DynCall {
        __get as private __dyn_get;
        __set as private __dyn_set;
    }

    public static function __callStatic($name, $arguments)
    {
        return static::query()
                ->$name(...$arguments);
    }

    public function &__get($name)
    {
        if(array_key_exists($name, $this->allData))
        {
            return $this->allData[$name];
        }

        return $this->__dyn_get($name);

        // if(in_array($name, $this->getGenerator()->getColumnNames()))
        // {
        //     return null;
        // }
    }

    public function __set($name, $value)
    {
        if(array_key_exists($name, $this->allData) || in_array($name, $this->getGenerator()->getColumnNames()))
        {
            $this->allData[$name] = $value;
            if(!in_array($name, $this->changedCols))
                $this->changedCols[] = $name;
        }
        else
        {
            $this->__dyn_set($name, $value);
        }
    }

    public function __call($name, $arguments)
    {
        throw new \BadMethodCallException("Method '$name' is not exists in model " . static::class);
    }
    
    public function offsetExists($offset) : bool
    {
        return isset($this->allData[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->allData[$offset] ?? null;
    }
    
    public function offsetSet($offset, $value) : void
    {
        $this->__set($offset, $value);
    }

    public function offsetUnset($offset) : void
    {
        unset($this->allData[$offset]);
    }

    /**
     * گرفتن متعلق بودن به ...
     * 
     * رابطه یک به یک / چند به یک
     * 
     * `class PayHistory: public $user_id; function user() { return $this->belongsTo(User::class); }`
     * `class ServiceInformation: public $service_id; function service() { return $this->belongsTo(Service::class, 'service_id', 'id'); }`
     * 
     * @template T
     * @param class-string<T> $class نام کلاس مورد نظر
     * @param mixed $column نام ستونی که شامل آدرس است
     * @param mixed $primary_column نام ستونی در کلاس مورد نظر که آدرس را با آن تطابق میدهد
     * @return OneToOne<T>|QueryBuilder<T>
     */
    public function belongsTo($class, $column = null, $primary_column = null)
    {
        if($primary_column === null)
        {
            $primary_column = $class::getPrimaryKey();
        }

        if($column === null)
        {
            $column = Text::snake(Text::afterLast($class, "\\")) . "_" . $primary_column;
        }
        
        return $class::queryWith(OneToOne::class)
                ->where($primary_column, $this->$column);
    }
    
    /**
     * گرفتن متعلق بودن به ...
     * 
     * رابطه چند به چند
     * 
     * در این رابطه دو جدول داریم و یک جدول که آیدی جفت جدول ها را در خود دارد
     * 
     * `A: id`
     * `B: id`
     * `C: a_id & b_id`
     * 
     * @param string $class نام کلاس مورد نظر
     * @param mixed $currentColumn نام ستونی که به این جدول وصل است
     * @param mixed $currentPrimary نام ستونی که در این جدول است و به آن ربط داده شده است
     * @param mixed $targetColumn نام ستونی که به جدول تارگت وصل است
     * @param mixed $targetPrimary نام ستونی که در جدول تارگت است و به آن ربط داده شده است
     * @return ManyToMany|QueryBuilder
     */
    // public function belongsToMany($class, $middleClass, $currentColumn = null, $currentPrimary = null, $targetColumn, $targetPrimary = null)
    // {

    //     if($currentPrimary === null)
    //     {
    //         $currentPrimary = static::getPrimaryKey();
    //     }
    //     if($currentColumn === null)
    //     {
    //         $currentColumn = Text::snake(Text::afterLast(static::class, "\\")) . "_" . $currentPrimary;
    //     }

    //     if($targetPrimary === null)
    //     {
    //         $targetPrimary = $class::getPrimaryKey();
    //     }
    //     if($targetColumn === null)
    //     {
    //         $targetColumn = Text::snake(Text::afterLast($class, "\\")) . "_" . $currentPrimary;
    //     }
        
    //     return $class::queryWith(ManyToMany::class);
    //     // return static::query()
    //     //         ->table(static::getTableName())
    //     //         ->selectCol($class::getTableName() . ".*")

    // }

    /**
     * گرفتن ردیف هایی که به این ردیف متصلند
     * 
     * رابطه یک به چند
     * 
     * `class Article: public $hashtag_id; public $author_id;`
     * `class Hashtag: function user() { return $this->hasMany(Article::class); }`
     * `class User: public $service_id; function service() { return $this->hasMany(Service::class, 'author_id', 'id'); }`
     * 
     * @template T
     * @param class-string<T> $class نام کلاس مورد نظر
     * @param mixed $column نام ستونی در کلاس مورد نظر که شامل آدرس این کلاس است
     * @param mixed $primary_column نام ستونی در این کلاس که آدرس را با آن تطابق میدهد
     * @return OneToMany<T>|QueryBuilder<T>
     */
    public function hasMany($class, $column = null, $primary_column = null)
    {

        if($primary_column === null)
        {
            $primary_column = static::getPrimaryKey();
        }

        if($column === null)
        {
            $column = Text::snake(Text::afterLast(static::class, "\\")) . "_" . $primary_column;
        }
        
        return $class::queryWith(OneToMany::class)
                ->where($column, $this->$primary_column);

    }

    /**
     * گرفتن ردیفی که به این ردیف متصل است
     * 
     * رابطه یک به یک
     * 
     * `class User: function userinfo() { return $this->hasOne(UserInfo::class); }`
     * `class UserInfo: public $user_id; function user() { return $this->belongsTo(User::class); }`
     * 
     * @template T
     * @param class-string $class نام کلاس مورد نظر
     * @param mixed $column نام ستونی در کلاس مورد نظر که شامل آدرس این کلاس است
     * @param mixed $primary_column نام ستونی در این کلاس که آدرس را با آن تطابق میدهد
     * @return OneToOne<T>|QueryBuilder<T>
     */
    public function hasOne($class, $column = null, $primary_column = null)
    {
        if($primary_column === null)
        {
            $primary_column = static::getPrimaryKey();
        }

        if($column === null)
        {
            $column = Text::snake(Text::afterLast(static::class, "\\")) . "_" . $primary_column;
        }
        
        return $class::queryWith(OneToOne::class)
                ->where($column, $this->$primary_column);
    }


    public static function resetCache()
    {
        static::$findCaches = [];
    }

    protected static $findCaches = [];
    
    /**
     * پیدا کردن دیتا
     * 
     * این دیتا را در حافظه کوتاه خود ذخیره می کند و تا پایان پروسه اسکریپت به یاد خواهد داشت
     *
     * @param mixed $id
     * @param string $findBy
     * @return static|false
     */
    public static function findCache($id, $findBy = null)
    {
        if($findBy)
        {
            foreach(static::$findCaches[static::class] ?? [] as $cache)
            {
                if($cache->$findBy == $id)
                {
                    return $cache;
                }
            }
        }
        else
        {
            if($result = static::$findCaches[static::class][$id] ?? false)
            {
                return $result;
            }
        }

        $object = static::find($id, $findBy);
        if(!$object)
            return false;

        @static::$findCaches[static::class][$object->getPrimaryValue()] = $object;
        return $object;
    }
    
    /**
     * پیدا کردن دیتا
     *
     * @param mixed $id
     * @param string $findBy
     * @return static|false
     */
    public static function find($id, $findBy = null)
    {
        if(!$findBy)
            $findBy = static::getPrimaryKey();

        return static::query()
                ->where($findBy, $id)
                ->get();
    }

    /**
     * پیدا کردن دیتا
     *
     * @param string $col
     * @param string|mixed $operator
     * @param mixed $value
     * @return static|false
     */
    public static function findWhere($col, $operator, $value = null)
    {
        $query = static::query();

        if(count(func_get_args()) == 2)
        {
            $query->where($col, $operator);
        }
        else
        {
            $query->where($col, $operator, $value);
        }

        return $query->get();
    }

    /**
     * پیدا کردن دیتا
     *
     * @param array|Arrayable $wheres
     * @return static|false
     */
    public static function findWheres(array|Arrayable $wheres)
    {
        return static::query()->wheres($wheres)->get();
    }

    /**
     * پیدا کردن دیتا و یا اجرا از تابع ورودی در صورت عدم وجود
     *
     * @param mixed $id
     * @param Closure|mixed $callback
     * @param string $findBy
     * @return static|mixed
     */
    public static function findOr($id, $callback, $findBy = null)
    {
        if(!$findBy)
            $findBy = static::getPrimaryKey();

        return static::query()
                ->where($findBy, $id)
                ->getOr($callback);
    }

    /**
     * پیدا کردن دیتا و یا ساختن آن در صورت عدم وجود
     *
     * @param mixed $id
     * @param array|Closure $data
     * @param string $findBy
     * @return static|false
     */
    public static function findOrCreate($id, $data = [], $findBy = null)
    {
        if(!$findBy)
            $findBy = static::getPrimaryKey();

        return static::query()
                ->where($findBy, $id)
                ->getOrCreate($data);
    }

    /**
     * پیدا کردن دیتا و یا اجرا شدن خطای کاربر در صورت عدم وجود
     * 
     * این خطا اگر هندل نشود، بصورت پیام به کاربر ارسال می شود
     *
     * @param mixed $id
     * @param string $message
     * @param string $findBy
     * @return static|false
     */
    public static function findOrError($id, $message = null, $findBy = null)
    {
        if(!$findBy)
            $findBy = static::getPrimaryKey();

        return static::query()
                ->where($findBy, $id)
                ->getOrError($message);
    }


    /**
     * ساخت ردیف جدید
     *
     * @param array|Arrayable $data
     * @return static|false
     */
    public static function create(array|Arrayable $data)
    {
        return static::query()->insert($data);
    }

    /**
     * افزودن اطلاعات این کلاس به جدول و برگرداندن سطر ساخته شده
     *
     * @return static|false
     */
    public function copy()
    {
        $data = $this->getNewData();

        if($primary = static::getPrimaryKey())
            unset($data[$primary]);

        return static::create($data);
    }

    /**
     * ذخیره اطلاعات/تغییرات این کلاس در جدول
     *
     * @return bool
     */
    public function save($onlyChanges = true)
    {
        if($onlyChanges)
            $data = $this->getChangedData();
        else
            $data = $this->getNewData();
        if(!$data)
            return true;

        $ok = static::queryThis()
                ->update($data);

        if($ok)
        {
            $this->changedCols = [];
            $this->oldData = $this->allData;
        }
        return $ok;
    }

    /**
     * گرفتن تعداد
     *
     * @return int
     */
    public static function count()
    {
        return static::query()->count();
    }

    /**
     * گرفتن کل ردیف ها
     *
     * @return Arr<static>
     */
    public static function all()
    {
        return static::query()->all();
    }

    /**
     * گرفتن کل ردیف ها با شرط مشخص
     *
     * @param array $wheres
     * @return Arr<static>
     */
    public static function allWheres($wheres)
    {
        return static::query()->wheres($wheres)->all();
    }

    /**
     * گرفتن کل ردیف ها با شرط مشخص
     *
     * @param string $col
     * @param string|mixed $operator
     * @param mixed $value
     * @return Arr<static>
     */
    public static function allWhere($col, $operator, $value = null)
    {
        $query = static::query();

        if(count(func_get_args()) == 2)
        {
            $query->where($col, $operator);
        }
        else
        {
            $query->where($col, $operator, $value);
        }

        return $query->all();
    }

    /**
     * حذف این ردیف
     *
     * @return bool
     */
    public function delete()
    {
        return static::queryThis()
                ->delete();
    }

    /**
     * پاکسازی تمامی ردیف های دیتابیس
     *
     * @return boolean
     */
    public static function clear()
    {
        return static::query()->delete();
    }


    public static function modifyOutArray(array &$data)
    {
        foreach(static::getGenerator()->getColumns() as $col)
        {
            if(isset($data[$col->name]) && $col->hasOutModifier())
            {
                $data[$col->name] = $col->dataOut($data[$col->name], static::class);
            }
        }
    }

    /**
     * زمانی که یک ردیف جدید قرار است ایجاد شود صدا زده می شود
     *
     * @param array $data
     * @return array
     */
    public static function onCreateQuery(array $data)
    {
        return $data;
    }

    /**
     * زمانی که این ردیف ایجاد می شود صدا زده می شود
     *
     * @return void
     */
    public function onCreate()
    {
    }

    /**
     * زمانی که درخواست آپدیت ایجاد می شود صدا زده می شود
     *
     * @param array $data
     * @return array
     */
    public static function onUpdateQueryStatic(array $data)
    {
        return $data;
    }

    /**
     * زمانی که تیبل ایجاد می شود صدا زده می شود
     *
     * @return void
     */
    public static function onCreateTable()
    {
    }

    /**
     * ساخت کوئری جدید با این شرط
     *
     * @param string $col
     * @param string|mixed $operator
     * @param mixed $value
     * @return QueryBuilder<static>
     */
    public static function where($col, $operator, $value = null)
    {
        $query = static::query();

        if(count(func_get_args()) == 2)
        {
            return $query->where($col, $operator);
        }
        else
        {
            return $query->where($col, $operator, $value);
        }
    }

    public static function column($name)
    {
        return static::getTable() . '.' . $name;
    }

	public function jsonSerialize()
    {
        return $this->allData;
	}

}
