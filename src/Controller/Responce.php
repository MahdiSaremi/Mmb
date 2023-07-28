<?php
#auto-name
namespace Mmb\Controller;

use Closure;
use Mmb\Update\Message\Msg;

/**
 * @deprecated 0
 */
class Responce
{

    public static function setResponce($callback, $message = null)
    {
        Response::setResponse($callback, $message);
    }

    public static function setMessage($message)
    {
        Response::setMessage($message);
    }

    public static function responce($text, array $args = [])
    {
        return Response::response($text, $args);
    }

    public static function responceIt($text, array $args = [])
    {
        return Response::responseIt($text, $args);
    }

    public static function defaultResponce(array $args)
    {
        return Response::defaultResponse($args);
    }
    
}
