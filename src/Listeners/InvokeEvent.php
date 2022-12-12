<?php

namespace Mmb\Listeners; #auto

interface InvokeEvent
{

    /**
     * @param string $name
     * @param array $args
     * @param mixed $result
     * @return true|void
     */
    public function eventInvoke($name, array $args, &$result);

}
