<?php
#auto-name
namespace Mmb\Pay;
use Mmb\Exceptions\MmbException;

class PayException extends MmbException
{

    public $error_id;

    public function __construct($error, $error_id)
    {
        $this->error_id = $error_id;
        parent::__construct($error);
    }
    
}
