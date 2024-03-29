<?php

namespace Mmb\Db\Driver\MySql; #auto

class Query extends \Mmb\Db\Driver\SqlBase\SqlQuery {

    protected $supports = [ 'select', 'delete', 'update', 'insert', 'insert_multi', 'createTable', 'getTable', 'editColumn', 'editColumn2', 'addColumn', 'removeColumn', 'removeIndex', 'removePrimaryKey' ];

    /**
     * Select
     *
     * @return void
     */
    public function select() {

        $this->query = '';

        $this->query .= 'SELECT ';

        // Select items
        $this->query .= join(", ", $this->select);

        // From table
        $this->query .= ' FROM `' . $this->table . '`';

        // Where
        if($this->where)
            $this->query .= ' ' . $this->where();

        // Group by
        if($this->groupBy)
            $this->query .= ' ' . $this->group();

        // Order by
        if($this->order)
            $this->query .= ' ' . $this->order();

        // Limit & Offset
        if($this->limit)
            $this->query .= ' ' . $this->limit();

    }

    /**
     * Delete
     *
     * @return void
     */
    public function delete() {

        $this->query = '';
        
        $this->query .= 'DELETE ';

        // From table
        $this->query .= 'FROM `' . $this->table . '`';

        // Where
        if($this->where)
            $this->query .= ' ' . $this->where();

        // Order by
        if($this->order)
            $this->query .= ' ' . $this->order();

        // Limit & Offset
        if($this->limit)
            $this->query .= ' ' . $this->limit();
            
    }

    /**
     * Update
     *
     * @return void
     */
    public function update() {

        $this->query = '';
        
        $this->query .= 'UPDATE `' . $this->table . '`';

        // Values
        $this->query .= ' SET';
        $first = true;
        foreach($this->insert as $key => $value) {

            if($first) $first = false;
            else $this->query .= ', ';

            $this->query .= ' `' . $key . '`=' . $this->safeString($value);

        }

        // Where
        if($this->where)
            $this->query .= ' ' . $this->where();

        // Order by
        if($this->order)
            $this->query .= ' ' . $this->order();

        // Limit & Offset
        if($this->limit)
            $this->query .= ' ' . $this->limit();

    }

    /**
     * Insert
     *
     * @return void
     */
    public function insert() {

        $this->query = '';
        
        $this->query .= 'INSERT INTO `' . $this->table . '`';

        // Columns & Values
        $cols = "";
        $vals = "";
        $first = true;
        foreach($this->insert as $key => $value) {

            if($first) $first = false;
            else {
                $cols .= ', ';
                $vals .= ', ';
            }

            $cols .= '`' . $key . '`';
            $vals .= $this->safeString($value);

        }
        $this->query .= " ($cols) VALUES ($vals)";

    }

    /**
     * Insert Multi
     *
     * @return void
     */
    public function insert_multi() {

        $this->query = '';
        
        $this->query .= 'INSERT INTO `' . $this->table . '`';

        // Columns
        $cols = "";
        $first = true;
        foreach($this->insert[0] as $key => $value) {

            if($first) $first = false;
            else {
                $cols .= ', ';
            }

            $cols .= '`' . $key . '`';

        }
        $this->query .= " ($cols) VALUES ";

        // Values
        foreach($this->insert as $m => $row) {

            if($m)
                $this->query .= ", ";

            $vals = "";
            $first = true;
            foreach($row as $key => $value) {

                if($first) $first = false;
                else {
                    $vals .= ', ';
                }
                
                $vals .= $this->safeString($value);

            }
            $this->query .= "($vals)";

        }
    }

    /**
     * Create Table
     *
     * @return void
     */
    public function createTable() {

        $this->query = "CREATE TABLE `{$this->table}` (";

        $qcol = $this->queryCol;
        $first = true;
        foreach($qcol->getColumns() as $col) {

            if($first)
                $first = false;
            else
                $this->query .= ", ";

            $this->column($col);

        }

        $this->query .= ") ENGINE = InnoDB";

    }

    /**
     * ستون
     *
     * @param \Mmb\Db\SingleCol $col
     * @return void
     */
    public function column(\Mmb\Db\SingleCol $col, \Mmb\Db\SingleCol $old = null) {

        $this->query .= "`{$col->name}` {$col->type}";

        // Len
        if($col->len)
            $this->query .= "({$col->len})";

        // Unsigned
        if($col->unsigned)
            $this->query .= " UNSIGNED";

        // Nullable
        if(!$col->nullable)
            $this->query .= " NOT NULL";

        // Default value
        if($col->default) {
            if($col->defaultRaw)
                $this->query .= " DEFAULT {$col->default}";
            else
                $this->query .= " DEFAULT " . $this->safeString($col->default);
        }

        // Auto increment
        if($col->autoIncrement)
            $this->query .= " AUTO_INCREMENT";

        // Primary key
        if($col->primaryKey)
            $this->query .= " PRIMARY KEY";

        // Unique
        if($col->unique)
            $this->query .= " UNIQUE";

        // On
        if($col->onUpdate)
            $this->query .= " ON UPDATE {$col->onUpdate}";

        if($col->onDelete)
            $this->query .= " ON DELETE {$col->onDelete}";

        // Position
        if($col->after)
            $this->query .= " AFTER `{$col->after}`";
        elseif($col->first)
            $this->query .= " FIRST";
    }

    /**
     * Get table columns
     *
     * @return void
     */
    public function getTable() {

        $this->query = "SHOW COLUMNS FROM `{$this->table}`";

    }

    /**
     * Edit column
     *
     * @return void
     */
    public function editColumn() {

        $this->query = "ALTER TABLE `{$this->table}` CHANGE `{$this->colName}` ";

        $this->column($this->col);

    }

    /**
     * Edit column
     *
     * @return void
     */
    public function editColumn2() {

        $this->query = "ALTER TABLE `{$this->table}` CHANGE `{$this->colName}` ";

        $this->column($this->col[1], $this->col[0]);

    }

    /**
     * Add column
     *
     * @return void
     */
    public function addColumn() {

        $this->query = "ALTER TABLE `{$this->table}` ADD ";

        $this->column($this->col);

    }

    /**
     * Remove column
     *
     * @return void
     */
    public function removeColumn() {

        $this->query = "ALTER TABLE `{$this->table}` DROP `{$this->colName}`";

    }

    /**
     * Remove index
     *
     * @return void
     */
    public function removeIndex() {

        $this->query = "ALTER TABLE `{$this->table}` DROP INDEX `{$this->colName}`";

    }

    /**
     * Remove primary key
     *
     * @return void
     */
    public function removePrimaryKey() {

        $this->query = "ALTER TABLE `{$this->table}` DROP PRIMARY KEY";

    }

    /**
     * ایمن کردن رشته
     *
     * @param string $string
     * @return string
     */
    public function safeString($string)
    {
        
        if($string === false) return 0;
        if($string === true) return 1;

        return '"' . addslashes($string) . '"';
        
    }

}
