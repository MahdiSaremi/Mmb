<?php

namespace Mmb\Db\Table; #auto

use Mmb\Db\Driver;
use Mmb\Db\QueryBuilder;
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

       return (new QueryBuilder)->createOrEditTable(static::getTable(), [ static::class, 'generate' ]);

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
                -> table( static::getTable() )
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
     * حذف این ردیف
     *
     * @return bool
     */
    public function delete() {

        return static::queryThis()
                ->delete();

    }

}
