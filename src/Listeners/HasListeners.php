<?php

namespace Mmb\Listeners; #auto

use Closure;
use InvalidArgumentException;

trait HasListeners
{

    private $listeners = [];

    /**
     * شونده ای برای این ایونت تعریف می کند
     *
     * @param string $name
     * @param Closure|array|string $callback
     * @return void
     */
    public function listen(string $name, Closure|array|string $callback)
    {
        @$this->listeners[$name][] = $callback;
    }

    /**
     * شنونده های این ایونت را صدا می زند
     * 
     * نوع های پشتیبانی شده:
     * 
     * `null` : هیچ مقداری را بر نمی گرداند
     * 
     * `last` : آخرین مقدار را بر می گرداند
     * 
     * `first-true` : اولین مقداری که ترو (یا مشابه) باشد را بر می گرداند
     * 
     * `first-is-true` : اولین مقداری که دقیقا ترو باشد را بر می گرداند
     *
     * `first-false` : اولین مقداری که فالس (یا مشابه) باشد را بر می گرداند
     * 
     * `first-is-false` : اولین مقداری که دقیقا فالس باشد را بر می گرداند
     *
     * `first-not-null` : اولین مقداری که نال نباشد را بر می گرداند
     * 
     * @param string $name
     * @param array $args
     * @return mixed
     */
    public function invokeListeners(string $name, array $args = [], string $returnType = 'null')
    {
        $listeners = $this->listeners[$name] ?? [];

        switch($returnType)
        {
            case 'null':
                foreach($listeners as $listener)
                {
                    Listeners::callMethod($listener, $args);
                }
            break;

            case 'last':
                $last = null;
                foreach($listeners as $listener)
                {
                    $last = Listeners::callMethod($listener, $args);
                }
                return $last;

            case 'first-true':
                foreach($listeners as $listener)
                {
                    if($value = Listeners::callMethod($listener, $args))
                    {
                        return $value;
                    }
                }
            break;

            case 'first-is-true':
                foreach($listeners as $listener)
                {
                    if(Listeners::callMethod($listener, $args) === true)
                    {
                        return true;
                    }
                }
            break;

            case 'first-false':
                foreach($listeners as $listener)
                {
                    if(!($value = Listeners::callMethod($listener, $args)))
                    {
                        return $value;
                    }
                }
            break;

            case 'first-is-false':
                foreach($listeners as $listener)
                {
                    if(Listeners::callMethod($listener, $args) === false)
                    {
                        return false;
                    }
                }
            break;

            case 'first-not-null':
                foreach($listeners as $listener)
                {
                    if(!is_null($value = Listeners::callMethod($listener, $args)))
                    {
                        return $value;
                    }
                }
            break;

            default:
                throw new InvalidArgumentException("Unknown \$returnType value, given '{$returnType}'");
        }
    }
    
}
