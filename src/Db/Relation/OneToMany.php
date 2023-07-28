<?php
#auto-name
namespace Mmb\Db\Relation;

/**
 * @template R
 * @extends Relation<R>
 */
class OneToMany extends Relation
{

    public function getDefRelationValue()
    {
        return $this->all();
    }
    
}
