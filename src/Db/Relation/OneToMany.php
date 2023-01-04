<?php

namespace Mmb\Db\Relation; #auto

class OneToMany extends Relation
{

    public function getRelationValue()
    {
        return $this->all();
    }
    
}
