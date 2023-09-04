<?php

namespace Mmb\Db\Table; #auto

use ArrayAccess;
use Closure;
use JsonSerializable;
use Mmb\Calling\DynCall;
use Mmb\Db\QueryBuilder;
use Mmb\Db\QueryCol;
use Mmb\Db\Relation\BelongsTo;
use Mmb\Db\Relation\BelongsToMany;
use Mmb\Db\Relation\ManyToMany;
use Mmb\Db\Relation\HasMany;
use Mmb\Db\Relation\HasOne;
use Mmb\Db\Relation\MorphMany;
use Mmb\Db\Relation\MorphOne;
use Mmb\Db\Relation\MorphTo;
use Mmb\Db\Relation\MorphToMany;
use Mmb\Db\Relation\Relation;
use Mmb\Exceptions\MmbException;
use Mmb\ExtraThrow\ExtraErrorMessage;
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
    
    public function __construct(array $data = [])
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
     * مشخص می کند چه چیز هایی به متد ویت اضافه شوند
     *
     * @var array
     */
    protected $with = [];

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

        static::$_generate_query_cols[static::class] = $query = new QueryCol(static::getTableName());
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
     * گرفتن شی عمومی
     *
     * @return static
     */
    public static function getInstance()
    {
        $instance = app(static::class);
        if(!$instance)
        {
            $instance = new static;
        }

        return $instance;
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
                ->output(static::class)
                ->with(...static::getInstance()->with);
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

    /**
     * حذف تمامی متغیر های کش محلی - مانند رابطه ها
     *
     * @return void
     */
    public function refresh()
    {
        $this->dynClear();
    }
    
    public function offsetExists($offset) : bool
    {
        return @$this->$offset !== null;
    }

    public function offsetGet($offset)
    {
        return $this->$offset ?? null;
    }
    
    public function offsetSet($offset, $value) : void
    {
        $this->$offset = $value;
    }

    public function offsetUnset($offset) : void
    {
        $this->forgot("$offset");
    }

    /**
     * فراموش کردن / نال کردن مقداری
     *
     * @param string $name
     * @param string ...$names
     * @return void
     */
    public function forgot(string $name, string ...$names)
    {
        foreach(func_get_args() as $name)
        {
            if(isset($this->allData[$name]))
            {
                $this->allData[$name] = null;
            }
            else
            {
                $this->dynForgot($name);
            }
        }
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
     * @return BelongsTo<T>
     */
    public function belongsTo(string $class, string $column = null, string $primary_column = null)
    {
        return BelongsTo::makeRelation($this, $class, $column, $primary_column);
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
     * @template R
     * @template M
     * @param class-string<R> $class
     * @param class-string<M> $middleClass
     * @param string $thisColumn
     * @param string|null $targetColumn
     * @param string|null $thisPrimary
     * @param string|null $targetPrimary
     * @return BelongsToMany<R,M>
     */
    public function belongsToMany(string $class, string $middleClass, string $thisColumn = null, string $targetColumn = null, string $thisPrimary = null, string $targetPrimary = null)
    {
        return BelongsToMany::makeRelation($this, $class, $middleClass, $thisColumn, $targetColumn, $thisPrimary, $targetPrimary);
    }

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
     * @return HasMany<T>
     */
    public function hasMany(string $class, string $column = null, string $primary_column = null)
    {
        return HasMany::makeRelation($this, $class, $column, $primary_column);
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
     * @param class-string<T> $class نام کلاس مورد نظر
     * @param mixed $column نام ستونی در کلاس مورد نظر که شامل آدرس این کلاس است
     * @param mixed $primary_column نام ستونی در این کلاس که آدرس را با آن تطابق میدهد
     * @return HasOne<T>
     */
    public function hasOne(string $class, string $column = null, string $primary_column = null)
    {
        return HasOne::makeRelation($this, $class, $column, $primary_column);
    }

    /**
     * ایجاد رابطه مورف
     * 
     * رابطه یک به یک
     *
     * @param string $name
     * @param string|null $type
     * @param string|null $id
     * @return MorphTo<Table>
     */
    public function morphTo(string $name, string $type = null, string $id = null)
    {
        return MorphTo::makeRelation($this, $name, $type, $id);
    }

    /**
     * ایجاد رابطه مورف
     * 
     * رابطه یک به چند
     *
     * @param string $class
     * @param string $name
     * @param string|null $type
     * @param string|null $id
     * @return MorphMany<Table>
     */
    public function morphMany(string $class, string $name, string $type = null, string $id = null)
    {
        return MorphMany::makeRelation($this, $class, $name, $type, $id);
    }

    /**
     * ایجاد رابطه مورف
     * 
     * رابطه یک به یک
     *
     * @template T
     * @param class-string<T> $class
     * @param string $name
     * @param string|null $type
     * @param string|null $id
     * @return MorphOne<T>
     */
    public function morphOne(string $class, string $name, string $type = null, string $id = null)
    {
        return MorphOne::makeRelation($this, $class, $name, $type, $id);
    }

    /**
     * ایجاد رابطه مورف
     * 
     * رابطه چند به چند
     *
     * @param string $class
     * @param string $name
     * @param string $localColumn
     * @param string|null $type
     * @param string|null $id
     * @return MorphToMany<Table>
     */
    public function morphToMany(string $class, string $name, string $localColumn = null, string $type = null, string $id = null)
    {
        return MorphToMany::makeRelation($this, $class, $name, $localColumn, $type, $id);
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
     * پیدا کردن دیتا - یا مقدار دلخواه
     * 
     * این دیتا را در حافظه کوتاه خود ذخیره می کند و تا پایان پروسه اسکریپت به یاد خواهد داشت
     *
     * @param mixed $id
     * @param mixed $or
     * @param string $findBy
     * @return static|mixed
     */
    public static function findCacheOr($id, $or, $findBy = null)
    {
        if($result = static::findCache($id, $findBy))
        {
            return $result;
        }

        return value($or);
    }

    /**
     * پیدا کردن دیتا - یا خطا
     * 
     * این دیتا را در حافظه کوتاه خود ذخیره می کند و تا پایان پروسه اسکریپت به یاد خواهد داشت
     *
     * @param mixed $id
     * @param string $message
     * @param string $findBy
     * @throws ExtraErrorMessage
     * @return static
     */
    public static function findCacheOrError($id, string $message, $findBy = null)
    {
        if($result = static::findCache($id, $findBy))
        {
            return $result;
        }

        error($message);
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
     * @param bool $modify اگر ترو باشد، داده ها را با توحه به نوع ستون های مدل تبدیل می کند
     * @return static|false
     */
    public static function create(array|Arrayable $data, bool $modify = true)
    {
        return static::query()->create($data, $modify);
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

        return static::create($data, false);
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
                ->update($data, false);

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

    /**
     * مقدار رابطه نسب شده را تنظیم می کند
     *
     * @param Table $model
     * @param string|null $column
     * @return void
     */
    public function setRelatedTo(Table $model, string $column = null)
    {
        $column ??= Text::snake(Text::afterLast(get_class($model), "\\")) . "_" . $model::getPrimaryKey();

        $this->$column = $model->getPrimaryValue();
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

    /**
     * بررسی می کند که آیا این مدل با مدل دیگری برابر است یا خیر
     * 
     * این بررسی بر اساس کلید اصلی انجام می شود
     *
     * @param Table|mixed $model_or_id
     * @return boolean
     */
    public function is($model_or_id)
    {
        if($model_or_id instanceof Table)
        {
            return $this === $model_or_id || $this->getPrimaryValue() == $model_or_id->getPrimaryValue();
        }
        else
        {
            return $this->getPrimaryValue() == $model_or_id;
        }
    }

}
