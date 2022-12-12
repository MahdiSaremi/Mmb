<?php

namespace Mmb\Controller\StepHandler; #auto

use Mmb\Update\User\User;

trait HasStep
{

    public function modifyStepIn(&$data)
    {
        if (optional(User::$this)->id == @$data['id'])
            StepHandler::modifyIn($data['step']);
    }

    public function modifyStepOut(&$data)
    {
        if (optional(User::$this)->id == @$data['id'])
            StepHandler::modifyOut($data['step']);
    }

    public static function stepColumn(\Mmb\Db\QueryCol $table)
    {
        StepHandler::column($table, 'step');
    }
    
}
