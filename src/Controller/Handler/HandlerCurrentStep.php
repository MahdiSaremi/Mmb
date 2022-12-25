<?php

namespace Mmb\Controller\Handler; #auto

use Mmb\Controller\StepHandler\Handlable;
use Mmb\Controller\StepHandler\StepHandler;

class HandlerCurrentStep extends Handler
{
    
    public function __construct()
    {
        $this->break();
    }

    public function check()
    {
        return parent::check() && StepHandler::get();
    }
    
	/**
	 * مدیریت آپدیت
	 * @return Handlable|null
	 */
	public function handle()
    {

        return StepHandler::get()->handle();

	}

}
