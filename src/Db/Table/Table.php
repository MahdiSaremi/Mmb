<?php

namespace Mmb\Db\Table; #auto

use Mmb\Db\Driver;
use Mmb\Db\QueryBuilder;
use Mmb\Db\Relation\OneToMany;
use Mmb\Db\Relation\OneToOne;
use Mmb\Db\Relation\Relation;
use Mmb\Listeners\HasListeners;
use Mmb\Listeners\HasStaticListeners;

class Table
{

    /**
     * دیتای قبلی
     *
     * @var array
     */
    public $oldData;

    /**
     * آیا تازه ساخته شده است
     * 
     * تنها زمانی که با تابع کریت یا اینسرت ساخته شود این مقدار ترو می شود
     *
     * @var boolean
     */
    public $newCreated = false;
    
    public final function __construct($data)
    {

        $this->oldData = $data;
        $this->modifyDataIn($data);
        
        foreach($data as $name => $value) {
            
            $this->$name = $value;

        }

    }

    /**
     * گرفتن دیتای جدید
     *
     * @return array
     */
    public final function getNewData() {

        $res = [];

        foreach($this->oldData as $name => $value) {
            $res[$name] = $this->$name;
        }

        $this->modifyDataOut($res);
        return $res;

    }

    /**
     * گرفتن دیتای تغییر یافته
     *
     * @return array
     */
    public final function getChangedData() {

        $data = $this->getNewData();

        foreach($this->oldData as $name => $value) {
            
            if($data[$name] === $value)
                unset($data[$name]);

        }

        return $data;

    }

    /**
     * گرفتن نام تیبل
     *
     * @return string
     */
    public static function getTable() {
        
        $exp = explode("\\", static::class);

        return end($exp) . "s";

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
     * @param \Mmb\Db\QueryCol $table
     * @return void
     */
    public static function generate(\Mmb\Db\QueryCol $table) {
    }

    /**
     * ایجاد یا ویرایش جدول
     *
     * @return bool
     */
    public static function createOrEditTable() {

       return (new QueryBuilder)->createOrEditTable(static::getTableName(), [ static::class, 'generate' ]);

    }

    /**
     * ایجاد یا ویرایش جدول ها
     *
     * @param array $tables آرایه ای از نام کلاس های جداول
     * @return bool
     */
    public static function createOrEditTables($tables) {

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
    public static function getPrimaryKey() {

        return 'id';

    }


    /**
     * مدیریت دیتا برای کلاس
     *
     * @param array $data
     * @return void
     */
    public function modifyDataIn(array &$data) {
    }

    /**
     * مدیریت دیتا برای ذخیره
     *
     * @param array $data
     * @return void
     */
    public function modifyDataOut(array &$data) {
    }


    /**
     * ایجاد یک کوئری بیلدر
     *
     * @return \Mmb\Db\QueryBuilder
     */
    public static function query() {

        return (new \Mmb\Db\QueryBuilder)
                -> table( static::getTableName() )
                -> output( static::class );

    }

    /**
     * ایجاد یک کوئری بیلدر با کلاس مورد نظر
     *
     * @return \Mmb\Db\QueryBuilder
     */
    public static function queryWith($class) {

        return (new $class)
                -> table( static::getTableName() )
                -> output( static::class );

    }

    /**
     * ایجاد یک کوئری بیلدر همراه با شرط این ردیف بودن
     *
     * @return \Mmb\Db\QueryBuilder
     */
    public function queryThis() {

        $primary = static::getPrimaryKey();

        return static::query()
                ->where($primary, $this->$primary);

    }

    public static function __callStatic($name, $arguments)
    {
        
        return static::query()
                ->$name(...$arguments);

    }

    protected $_invokesValue = [];

    public function __get($name)
    {

        if(array_key_exists($name, $this->_invokesValue))
        {
            return $this->_invokesValue[$name];
        }

        if(method_exists($this, $name))
        {
            $result = $this->$name();

            if($result instanceof Relation)
                $result = $result->getRelationValue();

            return $this->_invokesValue[$name] = $result;
        }

        error_log("Undefined property '$name'");

    }

    public function __call($name, $arguments)
    {
        
        throw new \BadMethodCallException("Method '$name' is not exists in model " . static::class);

    }

    
    /**
     * گرفتن متعلق بودن به ...
     * 
     * رابطه یک به یک / چند به یک
     * 
     * `class PayHistory: public $user_id; function user() { return $this->belongsTo(User::class); }`
     * `class ServiceInformation: public $service_id; function service() { return $this->belongsTo(Service::class, 'service_id', 'id'); }`
     * 
     * @param string $class نام کلاس مورد نظر
     * @param mixed $column نام ستونی که شامل آدرس است
     * @param mixed $primary_column نام ستونی در کلاس مورد نظر که آدرس را با آن تطابق میدهد
     * @return OneToOne|QueryBuilder
     */
    public function belongsTo($class, $column = null, $primary_column = null)
    {

        if($primary_column === null)
        {
            $primary_column = $class::getPrimaryKey();
        }

        if($column === null)
        {
            $e = explode("\\", strtolower($class));
            $column = end($e) . "_" . $primary_column;
        }
        
        return $class::queryWith(OneToOne::class)
                ->where($primary_column, $this->$column);

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
     * @param string $class نام کلاس مورد نظر
     * @param mixed $column نام ستونی در کلاس مورد نظر که شامل آدرس این کلاس است
     * @param mixed $primary_column نام ستونی در این کلاس که آدرس را با آن تطابق میدهد
     * @return OneToMany|QueryBuilder
     */
    public function hasMany($class, $column = null, $primary_column = null)
    {

        if($primary_column === null)
        {
            $primary_column = static::getPrimaryKey();
        }

        if($column === null)
        {
            $e = explode("\\", strtolower(static::class));
            $column = end($e) . "_" . $primary_column;
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
     * @param string $class نام کلاس مورد نظر
     * @param mixed $column نام ستونی در کلاس مورد نظر که شامل آدرس این کلاس است
     * @param mixed $primary_column نام ستونی در این کلاس که آدرس را با آن تطابق میدهد
     * @return OneToOne|QueryBuilder
     */
    public function hasOne($class, $column = null, $primary_column = null)
    {

        if($primary_column === null)
        {
            $primary_column = static::getPrimaryKey();
        }

        if($column === null)
        {
            $e = explode("\\", strtolower(static::class));
            $column = end($e) . "_" . $primary_column;
        }
        
        return $class::queryWith(OneToOne::class)
                ->where($column, $this->$primary_column);

    }


    /**
     * پیدا کردن دیتا
     *
     * @param mixed $id
     * @param string $findBy
     * @return static|false
     */
    public static function find($id, $findBy = null) {

        if(!$findBy)
            $findBy = static::getPrimaryKey();

        return static::query()
                ->where($findBy, $id)
                ->get();

    }

    /**
     * پیدا کردن دیتا
     *
     * @param mixed $id
     * @param string $findBy
     * @return static|false
     */
    public static function findWhere($col, $operator, $value = null) {

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
     * ساخت ردیف جدید
     *
     * @param array $data
     * @return static|false
     */
    public static function create(array $data) {

        $object = static::query()->insert($data);

        if(!$object)
            return false;

        return $object;

    }

    /**
     * افزودن اطلاعات این کلاس به جدول و برگرداندن سطر ساخته شده
     *
     * @return static|false
     */
    public function copy() {

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
    public function save($onlyChanges = true) {

        if($onlyChanges)
            $data = $this->getChangedData();
        else
            $data = $this->getNewData();

        if(!$data)
            return true;

        return static::queryThis()
                ->update($data);
        
    }

    /**
     * گرفتن تعداد
     *
     * @return int
     */
    public static function count() {

        return static::query()->count();

    }

    /**
     * گرفتن تعداد
     *
     * @return static[]
     */
    public static function all() {

        return static::query()->all();

    }

    /**
     * حذف این ردیف
     *
     * @return bool
     */
    public function delete() {

        return static::queryThis()
                ->delete();

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

}
