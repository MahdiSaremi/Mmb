<?php
#auto-name
namespace Mmb\Db\Relation;

use Mmb\Db\QueryBuilder;

/**
 * @template R
 * @extends QueryBuilder<R>
 */
class Relation extends QueryBuilder
{

    protected $_rel_method;
    protected $_rel_args;

    /**
     * تنظیم می کند که این رابطه در زمان کرفتن مقدار از چه تابعی استفاده کند
     *
     * @param string $method
     * @param mixed ...$args
     * @return $this
     */
    public function relationMethod($method, ...$args)
    {
        $this->_rel_method = $method;
        $this->_rel_args = $args;
        return $this;
    }

    public function getRelationValue()
    {
        if($this->_rel_method)
        {
            $method = $this->_rel_method;
            return $this->$method(...$this->_rel_args);
        }

        return $this->getDefRelationValue();
    }

    public function getDefRelationValue()
    {
        return $this;
    }

}
