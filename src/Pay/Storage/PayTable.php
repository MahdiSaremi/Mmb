<?php
#auto-name
namespace Mmb\Pay\Storage;

use Mmb\Db\QueryCol;
use Mmb\Db\Table\Table;

class PayTable extends Table
{

    public static function generate(QueryCol $table)
    {
        $table->id();
        $table->text('driver');
        $table->text('data');
    }

    public static function getTable()
    {
        return 'temp_pays_info';
    }
    
}
