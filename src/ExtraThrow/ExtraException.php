<?php
#auto-name
namespace Mmb\ExtraThrow;
use Exception;

abstract class ExtraException extends Exception
{

    /**
     * متدی که اگر هندل نشوند اجرا می شود
     *
     * @return void
     */
    public abstract function invoke();

}
