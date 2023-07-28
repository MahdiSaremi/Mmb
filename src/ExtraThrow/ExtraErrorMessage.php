<?php
#auto-name
namespace Mmb\ExtraThrow;

class ExtraErrorMessage extends ExtraException
{

    public function invoke()
    {
        $message = $this->getMessage();
        if($message)
        {
            responseIt($message);
        }
    }

}
