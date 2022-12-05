<?php

namespace Mds\Mmb\Controller\Handler; #auto

use Mds\Mmb\Controller\StepHandler\Handlable;
use Mds\Mmb\Controller\StepHandler\StepHandler;

class HandlerStep extends Handler
{

    /** @var StepHandler */
    private $handler;

    public function __construct(StepHandler $handler)
    {
        $this->handler = $handler;
        $this->break();
    }
    
	/**
	 * مدیریت آپدیت
	 * @return Handlable|null
	 */
	public function handle()
    {

        return $this->handler->handle();

	}

}
