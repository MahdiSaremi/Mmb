<?php

namespace Mds\Mmb\Db\Driver\Sqlite; #auto

class Sqlite extends \Mds\Mmb\Db\Driver {

    public function safeString($string)
    {
        return "'" . str_replace("'", "''", $string) . "'";
    }

}
