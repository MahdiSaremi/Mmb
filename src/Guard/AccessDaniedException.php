<?php
#auto-name
namespace Mmb\Guard;

use Mmb\Controller\StepHandler\StepHandler;
use Mmb\ExtraThrow\ExtraException;

class AccessDaniedException extends ExtraException
{

    protected $callback;

    public function __construct($callback)
    {
        parent::__construct("Access danied");
        $this->callback = $callback;
    }
    
    /**
     * اجرای کد های نمایش عدم دسترسی
     *
     * @return void
     */
	public function invoke()
    {
        if($this->callback)
        {
            $callback = $this->callback;
            $result = $callback();
        }
        else
        {
            $result = app(Guard::class)->invokeNotAllowed();
        }
        if($result !== null)
        {
            StepHandler::set($result);
        }
	}

}
