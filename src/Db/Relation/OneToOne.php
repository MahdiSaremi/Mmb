<?php
#auto-name
namespace Mmb\Db\Relation;

/**
 * @template R
 * @extends Relation<R>
 */
class OneToOne extends Relation
{

    public function getDefRelationValue()
    {
        return $this->get();
    }

}
