<?php

namespace Mmb\Db\Key; #auto

class Foreign extends \Mmb\Db\SingleKey {

    use On;

    /**
     * ستون مورد نظر
     *
     * @var string
     */
    public $target;

    public function __construct($target)
    {
        $this->target = $target;
    }

    public $refrenceTable;
    public $refrenceColumn;
    /**
     * تنظیم رفرنس
     *
     * @param string $table نام جدول
     * @param string $col نام ستون مورد نظر
     * @return $this
     */
    public function refrences($table, $column) {
        $this->refrenceTable = $table;
        $this->refrenceColumn = $column;
        return $this;
    }

}
