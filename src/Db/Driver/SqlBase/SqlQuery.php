<?php

namespace Mmb\Db\Driver\SqlBase; #auto

class SqlQuery extends \Mmb\Db\QueryCompiler {

    /**
     * درخواست نهایی
     *
     * @var string
     */
    public $query = '';

    public function joins()
    {
        if(!$this->joins)
            return '';
        
        $res = "";
        foreach($this->joins as $join)
        {
            $type = $join[0] ? $join[0] . ' JOIN' : 'JOIN';
            $res .= " $type $join[1]";
            if($join[2])
            {
                $res .= " ON " . $this->conditionQuery($join[2]);
            }
        }
        return $res;
    }

    /**
     * Where
     *
     * @return string
     */
    public function where()
    {
        if(!$this->where)
            return '';

        return 'WHERE ' . $this->conditionQuery($this->where);
    }

    /**
     * Where
     *
     * @return string
     */
    public function having()
    {
        if(!$this->having)
            return '';

        return 'HAVING ' . $this->conditionQuery($this->having);
    }

    public function conditionQuery(array $wheres)
    {
        if(!$wheres)
            return '1';

        $query = '';
        foreach($wheres as $i => $where)
        {

            $type = $where[0];
            $operator = $where[1];
            if($i) $query .= " $operator ";

            switch($type) {

                case 'col':
                    $query .= "$where[2] $where[3] " . $this->safeString($where[4]);
                break;

                case 'colcol':
                    $query .= "$where[2] $where[3] $where[4]";
                break;

                case 'raw':
                    $query .= '(' . $this->safeQueryReplace($where[2], ...$where[3]) . ')';
                break;

                case 'raw-exists':
                    $query .= 'EXISTS (' . $this->safeQueryReplace($where[2], ...$where[3]) . ')';
                break;

                case 'raw-operator':
                    $query .= '(' . $where[2] . ') ' . $where[3] . ' ' . $this->safeString($where[4]);
                break;

                case 'in':
                    if($where[3])
                    {
                        $query .= "$where[2] IN (" . join(", ", array_map([$this, 'safeString'], $where[3])) . ")";
                    }
                    else
                    {
                        $query .= "0";
                    }
                break;

                case 'notin':
                    if($where[3])
                    {
                        $query .= "$where[2] NOT IN (" . join(", ", array_map([$this, 'safeString'], $where[3])) . ")";
                    }
                    else
                    {
                        $query .= "1";
                    }
                break;

                case 'isnull':
                    $query .= "$where[2] IS NULL";
                break;

                case 'isnotnull':
                    $query .= "$where[2] IS NOT NULL";
                break;

                case 'inner':
                    $query .= "(" . $this->conditionQuery($where[2]) . ")";
                break;

                case 'inner-not':
                    $query .= "NOT (" . $this->conditionQuery($where[2]) . ")";
                break;

            }

        }

        return $query;
    }

    /**
     * Order by
     *
     * @return string
     */
    public function order()
    {
        if(!$this->order)
            return '';

        $query = 'ORDER BY';

        foreach($this->order as $order)
        {
            foreach($order[0] as $x => $col)
            {

                if($x) $query .= ",";
                $query .= " $col";

            }

            if($order[1])
                $query .= " " . $order[1];
        }

        return $query;
    }

    /**
     * Order by
     *
     * @return string
     */
    public function group()
    {
        if(!$this->groupBy)
            return '';

        $query = 'GROUP BY';

        foreach($this->groupBy as $x => $group) {

            if($x) $query .= ",";
            $query .= " $group";

        }

        return $query;
    }

    public function limit()
    {
        if(!$this->limit)
            return '';

        $query = 'LIMIT ';

        if($this->offset)
            $query .= $this->offset . ', ' . $this->limit;
        else
            $query .= $this->limit;

        return $query;
    }

    // public function table()
    // {
    //     return '`' . preg_replace('/\s*\.\s*/', '`.`', $this->table) . '`';
    // }

}
