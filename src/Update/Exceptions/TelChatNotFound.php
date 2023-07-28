<?php
#auto-name
namespace Mmb\Update\Exceptions;

class TelChatNotFound extends TelBadRequestError
{
    
    public static function match($text)
    {
        return $text == "Bad Request: chat not found";
    }
    
}
