<?php

namespace Mmb\Db; #auto

class QueryBuilder {

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
    public function table(string $table) {

        $this->table = $table;

        return $this;

    }


    /**
     * شرط ها
     *
     * @var array
     */
    private $where = [];
    /**
     * افزودن شرط بصورت کد
     *
     * @param string $where
     * @param mixed ...$args
     * @return $this
     */
    public function whereRaw($where, ...$args) {

        $this->where[] = [ 'raw', 'AND', $where, $args ];

        return $this;

    }

    /**
     * افزودن شرط بصورت کد
     *
     * @param string $where
     * @param mixed ...$args
     * @return $this
     */
    public function andWhereRaw($where, ...$args) {
        
        $this->where[] = [ 'raw', 'AND', $where, $args ];

        return $this;

    }

    /**
     * افزودن شرط بصورت کد
     *
     * @param string $where
     * @param mixed ...$args
     * @return $this
     */
    public function orWhereRaw($where, ...$args) {
        
        $this->where[] = [ 'raw', 'OR', $where, $args ];

        return $this;

    }

    /**
     * افزودن شرط نال بودن
     *
     * @param string $col
     * @return $this
     */
    public function whereIsNull($col) {

        $this->where[] = [ 'isnull', 'AND', $col ];

        return $this;

    }

    /**
     * افزودن شرط نال بودن
     *
     * @param string $col
     * @return $this
     */
    public function andWhereIsNull($col) {
        
        $this->where[] = [ 'isnull', 'AND', $col ];

        return $this;

    }

    /**
     * افزودن شرط نال بودن
     *
     * @param string $col
     * @return $this
     */
    public function orWhereIsNull($col) {
        
        $this->where[] = [ 'isnull', 'OR', $col ];

        return $this;

    }

    /**
     * افزودن شرط بین ستون و مقدار
     *
     * @param string $col ستون موردنظر
     * @param string $operator نوع مقایسه / مقدار مقایسه
     * @param string $value مقدار مقایسه
     * @return $this
     */
    public function where($col, $operator, $value = null) {

        if(count(func_get_args()) == 2) {
            
            $value = $operator;
            $operator = '=';

        }
        
        $this->where[] = [ 'col', 'AND', $col, $operator, $value ];

        return $this;

    }

    /**
     * افزودن شرط برابری مقدار ها
     *
     * @param array $col_value ستون ها و مقدار مورد نیاز
     * @return $this
     */
    public function wheres(array $col_value) {

        foreach($col_value as $col => $value)
        {
            $this->where[] = [ 'col', 'AND', $col, '=', $value ];
        }

        return $this;

    }

    /**
     * افزودن شرط بین ستون و مقدار
     *
     * @param string $col ستون موردنظر
     * @param string $operator نوع مقایسه / مقدار مقایسه
     * @param string $value مقدار مقایسه
     * @return $this
     */
    public function andWhere($col, $operator, $value = null) {

        if(count(func_get_args()) == 2) {
            
            $value = $operator;
            $operator = '=';

        }
        
        $this->where[] = [ 'col', 'AND', $col, $operator, $value ];

        return $this;

    }

    /**
     * افزودن شرط بین ستون و مقدار
     *
     * @param string $col ستون موردنظر
     * @param string $operator نوع مقایسه / مقدار مقایسه
     * @param string $value مقدار مقایسه
     * @return $this
     */
    public function orWhere($col, $operator, $value = null) {

        if(count(func_get_args()) == 2) {
            
            $value = $operator;
            $operator = '=';

        }
        
        $this->where[] = [ 'col', 'OR', $col, $operator, $value ];

        return $this;

    }

    /**
     * افزودن شرط بین دو ستون
     *
     * @param string $col ستون موردنظر
     * @param string $operator نوع مقایسه / ستون مقایسه
     * @param string $col2 ستون مقایسه
     * @return $this
     */
    public function whereCol($col, $operator, $col2 = null) {

        if(count(func_get_args()) == 2) {
            
            $col2 = $operator;
            $operator = '=';

        }
        
        $this->where[] = [ 'colcol', 'AND', $col, $operator, $col2 ];

        return $this;

    }

    /**
     * افزودن شرط بین دو ستون
     *
     * @param string $col ستون موردنظر
     * @param string $operator نوع مقایسه / ستون مقایسه
     * @param string $col2 ستون مقایسه
     * @return $this
     */
    public function andWhereCol($col, $operator, $col2 = null) {

        if(count(func_get_args()) == 2) {
            
            $col2 = $operator;
            $operator = '=';

        }
        
        $this->where[] = [ 'colcol', 'AND', $col, $operator, $col2 ];

        return $this;

    }

    /**
     * افزودن شرط بین دو ستون
     *
     * @param string $col ستون موردنظر
     * @param string $operator نوع مقایسه / ستون مقایسه
     * @param string $col2 ستون مقایسه
     * @return $this
     */
    public function orWhereCol($col, $operator, $col2 = null) {

        if(count(func_get_args()) == 2) {
            
            $col2 = $operator;
            $operator = '=';

        }
        
        $this->where[] = [ 'colcol', 'OR', $col, $operator, $col2 ];

        return $this;

    }

    /**
     * افزودن شرط در آرایه بودن
     *
     * @param string $col ستون موردنظر
     * @param string $array آرایه مقایسه
     * @return $this
     */
    public function whereIn($col, $array) {

        $this->where[] = [ 'in', 'AND', $col, $array ];

        return $this;

    }

    /**
     * افزودن شرط در آرایه بودن
     *
     * @param string $col ستون موردنظر
     * @param string $array آرایه مقایسه
     * @return $this
     */
    public function andWhereIn($col, $array) {

        $this->where[] = [ 'in', 'AND', $col, $array ];

        return $this;

    }

    /**
     * افزودن شرط در آرایه بودن
     *
     * @param string $col ستون موردنظر
     * @param string $array آرایه مقایسه
     * @return $this
     */
    public function orWhereIn($col, $array) {

        $this->where[] = [ 'in', 'OR', $col, $array ];

        return $this;

    }


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
    public function orderBy($cols, $sortType = null) {

        if(!is_array($cols))
            $cols = [ $cols ];

        $this->order[] = [ $cols, $sortType ];

        return $this;

    }

    /**
     * مرتب سازی نزولی بر اساس
     *
     * @param string|array $cols
     * @return $this
     */
    public function orderDescBy($cols) {

        return $this->orderBy($cols, 'DESC');

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
    public function limit($limit, $offset = null) {

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
    public function offset($offset) {

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
    public function groupBy($by) {

        $this->groupBy = is_array($by) ? $by : [ $by ];

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
     * @param string $class
     * @return $this
     */
    public function output($class) {

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
     * @return QueryResult
     */
    private function run($type) {

        $driver = $this->db_driver ?: Driver::defaultStatic();

        $compilerClass = $driver->queryCompiler;
        $compiler = new $compilerClass($type);

        foreach (get_object_vars($this) as $name => $value)
            $compiler->$name = $value;
            
        $compiler->start($type);

        return $driver->runQuery($compiler);

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
            $raw .= " as `$as`";
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
        $raw = "`$col`";
        if($as !== null)
            $raw .= " as `$as`";
        $this->selects[] = $raw;

        return $this;
    }

    /**
     * افزودن ستون انتخابی
     *
     * @param string $query
     * @param string $as
     * @return $this
     */
    public function selectSub($query, $as)
    {
        $this->selects[] = "($query) as `$as`";
        
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
     * @return Table\Table[]
     */
    public function all($select = null) {

        if($select === null)
            $select = $this->selects ?: ['*'];
        elseif(!is_array($select))
            $select = [ $select ];
        $this->select = $select;

        $res = $this->run('select');

        if(!$res->ok)
            return [];


        return $res->fetchAllAs($this->output);

    }

    /**
     * گرفتن یک ستون مشخص
     *
     * @param string $select
     * @return mixed[]
     */
    public function pluck($select) {

        $this->select = [ $select ];

        $res = $this->run('select');

        if(!$res->ok)
            return [];


        return $res->fetchPluck($select);

    }

    /**
     * گرفتن دو ستون خاص از تمامی ردیف های خروجی به عنوان کلید و مقدار آرایه
     *
     * @param string $key
     * @param string $value
     * @return array
     */
    public function pluckAssoc($key, $value) {

        $this->select = [ $key, $value ];

        $res = $this->run('select');

        if(!$res->ok)
            return [];


        return $res->fetchPluckAssoc($key, $value);

    }

    /**
     * گرفتن اولین ردیف
     *
     * @param array|string $select
     * @return Table\Table|false
     */
    public function get($select = null) {

        if($select === null)
            $select = $this->selects ?: ['*'];
        elseif(!is_array($select))
            $select = [ $select ];
        $this->select = $select;

        $this->limit(1);

        $res = $this->run('select');

        if(!$res->ok)
            return false;

        
        return $res->fetchAs($this->output);

    }

    /**
     * کرفتن تعداد نتایج
     *
     * @param string $of
     * @return int
     */
    public function count($of = '*') {

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

        $this->limit(1);

        $res = $this->run('select');

        if(!$res->ok)
            return 0;
        
        return $res->fetch()['count'] ? true : false;
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
     * @param array $data آرایه ای شامل کلید=نام ستون و مقدار=مقدار
     * @return bool
     */
    public function update(array $data) {

        if(!$data)
            return false;

        $output = $this->output;
        $data = $output::onUpdateQueryStatic($data);
        $this->insert = $data;

        return $this->run('update')->ok;

    }

    /**
     * ایجاد ردیف
     *
     * @param array $data آرایه ای شامل کلید=نام ستون و مقدار=مقدار
     * @return Table\Table|false
     */
    public function insert(array $data = []) {

        // Listener
        $output = $this->output;
        $data = $output::onCreateQuery($data);

        $this->insert = $data;
        $res = $this->run('insert');

        if(!$res->ok)
            return false;

        $primary = $output::getPrimaryKey();
        if($primary && !isset($data[$primary]) && $value = $res->insertID()) {
            $data[$primary] = $value;
        }

        $object = new $output($data);
        $object->newCreated = true;
        $object->onCreate();
        
        return $object;

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
     * @param array $data
     * @return Table\Table|false
     */
    public function create(array $data = [])
    {
        
        foreach($this->where as $where)
        {
            if($where[0] == 'col' && $where[1] == 'AND' && $where[3] == '=')
            {
                $data[$where[2]] = $where[4];
            }
        }

        return $this->insert($data);

    }

    /**
     * ایجاد ردیف
     *
     * @param array $datas آرایه از `آرایه ای شامل کلید=نام ستون و مقدار=مقدار`
     * @return bool
     */
    public function insertMulti(array $datas) {

        if(!$datas)
            return true;

        // Listeners
        $output = $this->output;
        foreach($datas as $index => $data)
            $datas[$index] = $output::onCreateQuery($data);

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
        $this->table = $name;
        $this->queryCol = new QueryCol;
        if($column_initialize)
            $column_initialize($this->queryCol);

        if($this->run('createTable')->ok)
        {
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

        try {
            $before = $this->getTable($name);
        }
        catch(\Exception$e){
            return $this->createTable($name, $column_initialize);
        }

        $after = new QueryCol;
        if($column_initialize)
            $column_initialize($after);

        // Get old
        $before_cols = [];
        foreach($before->getColumns() as $col) {
            $before_cols[$col->name] = $col;
        }

        // Find changes
        $last = false;
        foreach($after->getColumns() as $col) {

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
        foreach($before_cols as $col) {

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
    public function getTable($name) {

        $this->table = $name;

        return $this->run('getTable')->toQueryCol();

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
    public function editColumn($table, $before_name, \Mmb\Db\SingleCol $col) {

        $this->table = $table;
        $this->colName = $before_name;
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
    public function editColumn2($table, \Mmb\Db\SingleCol $old, \Mmb\Db\SingleCol $new) {

        $this->table = $table;

        /** @var \Mmb\Db\SingleCol */
        $newCloned = unserialize(serialize($new));
        
        if($old->primaryKey != $new->primaryKey) {
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
    
        return $this->editColumn($table, $old->name, $newCloned);

    }

    /**
     * افزودن ستون
     *
     * @param string $table
     * @param \Mmb\Db\SingleCol $col
     * @return bool
     */
    public function addColumn($table, \Mmb\Db\SingleCol $col) {

        $this->table = $table;
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
    public function removeColumn($table, $col) {

        $this->table = $table;
        $this->colName = $col;

        return $this->run('removeColumn')->ok;

    }

    /**
     * حذف ایندکس
     *
     * @param string $table
     * @param string $col
     * @return bool
     */
    public function removeIndex($table, $col) {

        $this->table = $table;
        $this->colName = $col;

        return $this->run('removeIndex')->ok;

    }

    /**
     * حذف کلید اصلی
     *
     * @param string $table
     * @return bool
     */
    public function removePrimaryKey($table) {

        $this->table = $table;

        return $this->run('removePrimaryKey')->ok;

    }

}
