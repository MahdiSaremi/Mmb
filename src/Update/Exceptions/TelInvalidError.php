<?php
#auto-name
namespace Mmb\Update\Exceptions;

class TelInvalidError extends TelBadRequestError
{

    public $arg;

    public static function match($text)
    {
        if(preg_match('/^Bad Request: invalid ([\w_\s]+)$/', $text, $match))
        {
            $exp = new static($text, static::$error_code);
            $exp->arg = $match[1];
            throw $exp;
        }
    }
    
}
