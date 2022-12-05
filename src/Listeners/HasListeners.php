<?php

namespace Mmb\Listeners; #auto

trait HasListeners
{

    private $listeners = [];

    public function listen($name, $callback)
    {
        @$this->listeners[$name][] = $callback;
    }

    public function invokeListen($name, array $args = [])
    {
        $listeners = $this->listeners[$name] ?? [];

        foreach($listeners as $listener)
        {
            Listeners::callMethod($listener, $args);
        }
    }
    
}
