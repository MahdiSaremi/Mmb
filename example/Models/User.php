<?php

namespace Models; #auto

use Mmb\Controller\StepHandler\StepHandler;
use Mmb\Db\QueryCol;
use Mmb\Db\Table\Table;

class User extends Table
{
    
    /** @var User */
    public static $this;
    
    public static function getTable()
    {
        return 'users';
    }


    /** @var int */
    public $id;

    public function modifyDataIn(array &$data)
    {
        StepHandler::modifyIn($data['step']);
    }

    public function modifyDataOut(array &$data)
    {
        StepHandler::modifyOut($data['step']);
    }

    public static function generate(QueryCol $table)
    {
        $table->unsignedBigint('id')->primaryKey();
        StepHandler::column($table, 'step');
    }

    public static function createUser($id)
    {
        return self::create([
            'id' => $id,
            'step' => null,
        ]);
    }
    
}
