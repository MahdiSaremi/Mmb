<?php

namespace Mmb\Db\Driver\MySql; #auto

use Exception;
use Mmb\Db\QueryResult;
use Mmb\Db\SingleIndex;

class Result extends \Mmb\Db\QueryResult {

    /**
     * خروجی
     *
     * @var \mysqli_stmt
     */
    public $state;

    /**
     * دیتابیس
     *
     * @var MySql
     */
    public $db;

    /**
     * نتیجه
     *
     * @var \mysqli_result
     */
    public $res;

    public function __construct($ok, \mysqli_stmt $state, MySql $db)
    {
        $this->ok = $ok;
        $this->state = $state;
        $this->db = $db;
        $this->res = $state->get_result();
    }

    public function reset()
    {
        $this->res->data_seek(0);
    }

    public function fetch()
    {
        return $this->res->fetch_assoc();
    }

    public function fetchAll()
    {
        return $this->res->fetch_all(MYSQLI_ASSOC);
    }

    public function fetchCell()
    {
        return $this->res->fetch_row()[0];
    }

    public function fetchCount()
    {
        return $this->res->num_rows;
    }

    public function insertID()
    {
        return $this->state->insert_id;
    }


    public function toQueryCol($table, ?QueryResult $indexs = null)
    {
        $qcol = new \Mmb\Db\QueryCol($table);

        while($row = $this->fetch()) {

            $ctype = $row['Type'];
            
            if(preg_match('/^(\w+)(|\(\d+\))\s*(|unsigned)$/', $ctype, $type))
            {
                // Column
                $col = $qcol->createColumn($row['Field'], $type[1]);
                
                // Unsigned
                if($type[3])
                    $col->unsigned();

                // Len
                if($type[2])
                    $col->len(+trim($type[2], '()'));
            }
            elseif(preg_match('/^enum\((.*)\)$/i', $ctype, $type))
            {
                // Column
                $col = $qcol->createColumn($row['Field'], 'enum');
                
                // $enums = array_map(function($enum)
                // {

                // }, explode(',', $type[1]));
            }
            else
            {
                $col = $qcol->createColumn($row['Field'], $ctype);
            }

            // Nullable
            if($row['Null'])
                $col->nullable();

            // Key
            if($row['Key'] == "PRI")
                $col->primaryKey();
            elseif($row['Key'] == "UNI")
                $col->unique();
            elseif($row['Key'] == "MUL")
            {
                try
                {
                    $foreign = $this->db->query()->findMySqlForeingKeyRelation($table, $col->name);
                    if($foreign)
                    {
                        $col->foreignKey($foreign->table, $foreign->column, $foreign->constraint);
                    }
                }
                catch(Exception$e)
                { }
            }

            // Default
            $col->default($row['Default']);

            // Auto increment
            $extra = $row['Extra'];
            if(strpos($extra, "auto_increment") !== false)
                $col->autoIncrement();

            // On
            preg_match_all('/on (\w+) (.*)/', $extra, $ons);
            foreach($ons[1] as $i => $on) {
                
                $on = "on$on";
                if(method_exists($col, $on))
                    $col->$on($ons[2][$i]);
                    
            }

        }
    
        // Indexs
        if($indexs)
        {
            $indexsOf = [];
            while($indexRow = $indexs->fetch())
            {
                $indexName = $indexRow['Key_name'];
                $column = $indexRow['Column_name'];

                if(isset($indexsOf[$indexName]))
                {
                    $indexsOf[$indexName][1][] = $column;
                }
                else
                {
                    $type = '';
                    if($indexRow['Index_type'] == 'BTREE')
                    {
                        if($indexName == 'PRIMARY')
                        {
                            $type = 'PRIMARY';
                        }
                        elseif(!$indexRow['Non_unique'])
                        {
                            $type = 'UNIQUE';
                        }
                    }
                    elseif($indexRow['Index_type'] == 'FULLTEXT')
                    {
                        $type = 'FULLTEXT';
                    }

                    $indexsOf[$indexName] = [ $type, [$column] ];
                }
            }

            foreach($indexsOf as $name => $indexData)
            {
                [$type, $columns] = $indexData;
                $qcol->addIndex($type, $columns, $name);
            }
        }

        return $qcol;
    }

    public function __destruct()
    {
        if($this->res)
            $this->res->close();
    }

}
