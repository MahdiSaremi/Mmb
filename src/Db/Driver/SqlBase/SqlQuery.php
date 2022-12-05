<?php

namespace Mmb\Db\Driver\SqlBase; #auto

class SqlQuery extends \Mmb\Db\QueryCompiler {

    /**
     * درخواست نهایی
     *
     * @var string
     */
    public $query = '';

    /**
     * Where
     *
     * @return string
     */
    public function where() {

        if(!$this->where)
            return '';

        $query = 'WHERE ';

        foreach($this->where as $i => $where) {

            $type = $where[0];
            $operator = $where[1];
            if($i) $query .= " $operator ";

            switch($type) {

                case 'col':
                    $query .= "`$where[2]` $where[3] " . $this->safeString($where[4]);
                break;

                case 'colcol':
                    $query .= "`$where[2]` $where[3] `$where[4]`";
                break;

                case 'raw':
                    $query .= $this->safeQueryReplace($where[2], ...$where[3]);
                break;

                case 'in':
                    $query .= "`$where[2]` in (" . join(", ", array_map([$this, 'safeString'], $where[3])) . ")";
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
    public function order() {

        if(!$this->order)
            return '';

        $query = 'ORDER BY';

        foreach($this->order as $order) {

            foreach($order[0] as $x => $col) {

                if($x) $query .= ",";
                $query .= " `$col`";

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
    public function group() {

        if(!$this->groupBy)
            return '';

        $query = 'GROUP BY';

        foreach($this->groupBy as $x => $group) {

            if($x) $query .= ",";
            $query .= " $group";

        }

        return $query;

    }

    public function limit() {

        if(!$this->limit)
            return '';

        $query = 'LIMIT ';

        if($this->offset)
            $query .= $this->offset . ', ' . $this->limit;
        else
            $query .= $this->limit;

        return $query;

    }

}
