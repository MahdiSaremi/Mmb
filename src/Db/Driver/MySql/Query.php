<?php

namespace Mmb\Db\Driver\MySql; #auto

use Mmb\Db\QueryBuilder;
use Mmb\Exceptions\TypeException;
use UnitEnum;

class Query extends \Mmb\Db\Driver\SqlBase\SqlQuery
{

    protected $supports = [ 'select', 'delete', 'update', 'insert', 'insert_multi', 'createTable', 'showColumns', 'showIndexs', 'editColumn', 'editColumn2', 'addColumn', 'removeColumn', 'removePrimaryKey', 'removeForeignKey', 'removeIndex', 'addIndex', 'addForeignKey' ];

    /**
     * Select
     *
     * @return void
     */
    public function select()
    {
        $this->query = '';

        $this->query .= 'SELECT ';

        // Unique select
        if($this->distinct)
            $this->query .= "DISTINCT ";

        // Select items
        $this->query .= join(", ", $this->select);

        // From table
        $this->query .= ' FROM ' . $this->table;

        // Join
        if($this->joins)
            $this->query .= $this->joins();

        // Where
        if($this->where)
            $this->query .= ' ' . $this->where();

        // Group by
        if($this->groupBy)
            $this->query .= ' ' . $this->group();

        // Having
        if($this->having)
            $this->query .= ' ' . $this->having();

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
    public function delete()
    {
        $this->query = '';
        
        $this->query .= 'DELETE ';

        // From table
        $this->query .= 'FROM ' . $this->table;

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
    public function update()
    {
        $this->query = '';
        
        $this->query .= 'UPDATE ' . $this->table;

        // Values
        $this->query .= ' SET';
        $first = true;
        foreach($this->insert as $key => $value)
        {
            if($first) $first = false;
            else $this->query .= ', ';

            $this->query .= ' ' . $key . '=' . $this->safeString($value);
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
    public function insert()
    {
        $this->query = '';
        
        $this->query .= 'INSERT INTO ' . $this->table;

        if(is_array($this->insert))
        {
            // Columns & Values
            $cols = "";
            $vals = "";
            $first = true;
            foreach($this->insert as $key => $value)
            {
                if($first) $first = false;
                else {
                    $cols .= ', ';
                    $vals .= ', ';
                }

                $cols .= $key;
                $vals .= $this->safeString($value);
            }

            $this->query .= " ($cols) VALUES ($vals)";
        }
        else
        {
            $this->query .= " " . $this->insert;
        }
    }

    /**
     * Insert Multi
     *
     * @return void
     */
    public function insert_multi()
    {
        $this->query = '';
        
        $this->query .= 'INSERT INTO ' . $this->table;

        // Columns
        $cols = "";
        $first = true;
        foreach(first($this->insert) as $key => $value)
        {
            if($first) $first = false;
            else {
                $cols .= ', ';
            }

            $cols .= $key;
        }
        $this->query .= " ($cols) VALUES ";

        // Values
        foreach($this->insert as $m => $row)
        {
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
    public function createTable()
    {
        $this->query = "CREATE TABLE {$this->table} (";

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
    public function column(\Mmb\Db\SingleCol $col, \Mmb\Db\SingleCol $old = null)
    {
        $this->query .= "`{$col->name}` {$col->type}";

        // Len
        if($col->len)
            $this->query .= "({$col->len})";

        // Inner
        elseif($inner = $col->getInnerValues())
            $this->query .= '(' . implode(', ', array_map($this->safeString(...), $inner)) . ')';

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
        if($col->primaryKey && (!$old || !$old->primaryKey))
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
    public function showColumns()
    {
        $this->query = "SHOW COLUMNS FROM {$this->table}";
    }

    /**
     * Get table columns
     *
     * @return void
     */
    public function showIndexs()
    {
        $this->query = "SHOW INDEXES FROM {$this->table}";
    }

    /**
     * Edit column
     *
     * @return void
     */
    public function editColumn()
    {
        $this->query = "ALTER TABLE {$this->table} CHANGE {$this->colName} ";

        $this->column($this->col);
    }

    /**
     * Edit column
     *
     * @return void
     */
    public function editColumn2()
    {
        $this->query = "ALTER TABLE {$this->table} CHANGE {$this->colName} ";

        $this->column($this->col[1], $this->col[0]);
    }

    /**
     * Add column
     *
     * @return void
     */
    public function addColumn()
    {
        $this->query = "ALTER TABLE {$this->table} ADD ";

        $this->column($this->col);
    }

    /**
     * Remove column
     *
     * @return void
     */
    public function removeColumn()
    {
        $this->query = "ALTER TABLE {$this->table} DROP {$this->colName}";
    }

    /**
     * Add index
     *
     * @return void
     */
    public function addIndex()
    {
        $this->query = "CREATE {$this->singleIndex->type} INDEX " . QueryBuilder::stringColumn($this->singleIndex->name);
        $this->query .= " ON {$this->table} (" . implode(", ", QueryBuilder::stringColumnMap($this->singleIndex->columns)) . ")";
    }

    /**
     * Remove index
     *
     * @return void
     */
    public function removeIndex()
    {
        $this->query = "ALTER TABLE {$this->table} DROP INDEX {$this->colName}";
    }

    /**
     * Remove foreign key
     *
     * @return void
     */
    public function removeForeignKey()
    {
        $this->query = "ALTER TABLE {$this->table} DROP FOREIGN KEY {$this->colName}";
    }

    /**
     * Add foreign key
     *
     * @return void
     */
    public function addForeignKey()
    {
        $this->query = "ALTER TABLE {$this->table} ADD ";
        $this->foreign();
    }

    public function foreign()
    {
        $foreign = $this->foreign_key;

        if($foreign->constraint)
            $this->query .= "CONSTRAINT `{$foreign->constraint}` ";

        $this->query .= "FOREIGN KEY ({$this->colName})";
        $this->table = $foreign->table;
        $this->query .= " REFERENCES {$this->table} (`{$foreign->column}`)";

        if($foreign->onDelete)
            $this->query .= " ON DELETE " . $foreign->onDelete;
        
        if($foreign->onUpdate)
            $this->query .= " ON UPDATE " . $foreign->onUpdate;
    }

    /**
     * Remove primary key
     *
     * @return void
     */
    public function removePrimaryKey() {

        $this->query = "ALTER TABLE {$this->table} DROP PRIMARY KEY";

    }

    /**
     * ایمن کردن رشته
     *
     * @param mixed $string
     * @return string
     */
    public function safeString($string)
    {
        if($string === false) return 0;
        if($string === true) return 1;
        if($string === null) return 'NULL';

        if(is_int($string) || is_float($string))
            $string = "$string";

        if($string instanceof UnitEnum)
        {
            $string = $string->value;
        }

        if(!is_string($string))
        {
            throw new TypeException("Query builder given object of '" . typeOf($string) . "', required string");
        }

        return '"' . addslashes($string) . '"';
    }

}
