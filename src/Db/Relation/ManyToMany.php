<?php

namespace Mmb\Db\Relation; #auto

class ManyToMany extends Relation
{

    public function getRelationValue()
    {
        return $this->all();
    }
    
}
