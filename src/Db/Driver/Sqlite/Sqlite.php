<?php

namespace Mmb\Db\Driver\Sqlite; #auto

class Sqlite extends \Mmb\Db\Driver {

    public function safeString($string)
    {
        return "'" . str_replace("'", "''", $string) . "'";
    }

}
