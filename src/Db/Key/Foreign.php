<?php

namespace Mmb\Db\Key; #auto

class Foreign extends \Mmb\Db\SingleKey {

    use On;

    public $table;
    public $column;
    public $constraint;

    public function __construct($table_name, $column_name, $constraint)
    {
        $this->table = $table_name;
        $this->column = $column_name;
        $this->constraint = $constraint;
    }

}
