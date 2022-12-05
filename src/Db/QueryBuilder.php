<?php

namespace Mds\Mmb\Db; #auto

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
    



    /**
     * اجرای کوئری
     *
     * @param string $type
     * @return QueryResult
     */
    private function run($type) {

        $driver = Driver::defaultStatic();

        $compilerClass = $driver->queryCompiler;
        $compiler = new $compilerClass($type);

        $compiler->table = $this->table;
        $compiler->where = $this->where;
        $compiler->limit = $this->limit;
        $compiler->offset = $this->offset;
        $compiler->order = $this->order;
        $compiler->groupBy = $this->groupBy;
        $compiler->select = $this->select;
        $compiler->insert = $this->insert;
        $compiler->queryCol = $this->queryCol;
        $compiler->col = $this->col;
        $compiler->colName = $this->colName;

        $compiler->start($type);

        return $driver->query($compiler);

    }

    /**
     * ستون های مورد نظر برای انتخاب
     *
     * @var string[]
     */
    private $select;

    /**
     * گرفتن کل مقدار ها
     *
     * @param array|string $select
     * @return Table\Table[]
     */
    public function all($select = ['*']) {

        if(!is_array($select))
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
     * گرفتن اولین ردیف
     *
     * @param array|string $select
     * @return Table\Table|false
     */
    public function get($select = ['*']) {

        if(!is_array($select))
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
    public function exists() {

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
    public function delete() {

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

        $this->insert = $data;

        return $this->run('update')->ok;

    }

    /**
     * ایجاد ردیف
     *
     * @param array $data آرایه ای شامل کلید=نام ستون و مقدار=مقدار
     * @return Table\Table|false
     */
    public function insert(array $data) {

        if(!$data)
            return false;

        $this->insert = $data;
        $res = $this->run('insert');

        if(!$res->ok)
            return false;

        $output = $this->output;

        $primary = $output::getPrimaryKey();
        if($primary && !isset($data[$primary]) && $value = $res->insertID()) {
            $data[$primary] = $value;
        }

        $object = new $output($data);
        $object->newCreated = true;
        
        return $object;

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
     * @param callable $column_initialize `function(\Mds\Mmb\Db\QueryCol $query) { }`
     * @return bool
     */
    public function createTable($name, $column_initialize = null) {

        $this->table = $name;
        $this->queryCol = new QueryCol;
        if($column_initialize)
            $column_initialize($this->queryCol);

        return $this->run('createTable')->ok;

    }

    /**
     * ساخت یا جدول
     *
     * @param string $name
     * @param callable $column_initialize `function(\Mds\Mmb\Db\QueryCol $query) { }`
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
     * @return \Mds\Mmb\Db\QueryCol
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
     * @param \Mds\Mmb\Db\SingleCol $col
     * @return bool
     */
    public function editColumn($table, $before_name, \Mds\Mmb\Db\SingleCol $col) {

        $this->table = $table;
        $this->colName = $before_name;
        $this->col = $col;

        return $this->run('editColumn')->ok;

    }

    /**
     * ویرایش ستون
     *
     * @param string $table
     * @param \Mds\Mmb\Db\SingleCol $old
     * @param \Mds\Mmb\Db\SingleCol $new
     * @return bool
     */
    public function editColumn2($table, \Mds\Mmb\Db\SingleCol $old, \Mds\Mmb\Db\SingleCol $new) {

        $this->table = $table;

        /** @var \Mds\Mmb\Db\SingleCol */
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
     * @param \Mds\Mmb\Db\SingleCol $col
     * @return bool
     */
    public function addColumn($table, \Mds\Mmb\Db\SingleCol $col) {

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
