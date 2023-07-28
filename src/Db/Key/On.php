<?php

namespace Mmb\Db\Key; #auto

trait On
{

    public $onDelete;

    /**
     * زمانی که حذف می شود
     *
     * @param string $raw
     * @return $this
     */
    public function onDelete($raw)
    {
        $this->onDelete = $raw;
        return $this;
    }

    /**
     * زمانی که ردیف مورد نظر حذف شود، این ردیف نیز حذف می شود
     *
     * @return $this
     */
    public function onDeleteCascade()
    {
        return $this->onDelete('CASCADE');
    }
    
    /**
     * زمانی که ردیف مورد نظر حذف شود، این ردیف نال می شود
     *
     * @return $this
     */
    public function onDeleteNull()
    {
        return $this->onDelete('SET NULL');
    }

    /**
     * زمانی که ردیف مورد نظر حذف شود، این ردیف به مقدار دیفالت بر می گردد
     *
     * @return $this
     */
    public function onDeleteDefault()
    {
        return $this->onDelete('SET DEFAULT');
    }

    /**
     * مانع حذف ردیف مورد نظر می شود
     *
     * @return $this
     */
    public function onDeleteNoAction()
    {
        return $this->onDelete('NO ACTION');
    }

    
    public $onUpdate;

    /**
     * زمانی که بروز می شود
     *
     * @param string $raw
     * @return $this
     */
    public function onUpdate($raw)
    {
        $this->onUpdate = $raw;
        return $this;
    }

    /**
     * زمانی که ردیف مورد نظر حذف شود، این ردیف نیز حذف می شود
     *
     * @return $this
     */
    public function onUpdateCascade()
    {
        return $this->onUpdate('CASCADE');
    }
    
    /**
     * زمانی که ردیف مورد نظر حذف شود، این ردیف نال می شود
     *
     * @return $this
     */
    public function onUpdateNull()
    {
        return $this->onUpdate('SET NULL');
    }

    /**
     * زمانی که ردیف مورد نظر حذف شود، این ردیف به مقدار دیفالت بر می گردد
     *
     * @return $this
     */
    public function onUpdateDefault()
    {
        return $this->onUpdate('SET DEFAULT');
    }

    /**
     * زمانی که ردیف مورد نظر حذف شود، هیچ اتفاقی نمی افتد
     *
     * @return $this
     */
    public function onUpdateNone()
    {
        return $this->onUpdate('NO ACTION');
    }

}
