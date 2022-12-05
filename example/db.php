<?php

use Mds\Mmb\Db\Table\Table;

require __DIR__ . '/load.php';


/**
 * Generate tables
 * 
 */




Table::createOrEditTables([
    
    \Models\User::class,

]);


echo "Database updated!";
