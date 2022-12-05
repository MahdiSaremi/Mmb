<?php

namespace Mds\Mmb\Db\Key; #auto

trait On {

    public $onDelete;

    /**
     * زمانی که حذف می شود
     *
     * @param string $raw
     * @return $this
     */
    public function onDelete($raw) {
        $this->onDelete = $raw;
        return $this;
    }

    public function onDeleteCascade() {
        return $this->onDelete('CASCADE');
    }

    
    public $onUpdate;

    /**
     * زمانی که بروز می شود
     *
     * @param string $raw
     * @return $this
     */
    public function onUpdate($raw) {
        $this->onUpdate = $raw;
        return $this;
    }

}
