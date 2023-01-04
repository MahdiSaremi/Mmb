<?php

namespace Mmb\Db\Relation; #auto

class OneToOne extends Relation
{

    public function getRelationValue()
    {
        return $this->get();
    }

}
