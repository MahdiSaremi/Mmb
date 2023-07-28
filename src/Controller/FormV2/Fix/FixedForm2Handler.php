<?php
#auto-name
namespace Mmb\Controller\FormV2\Fix;

use Mmb\Controller\FormV2\Form2Handler;
use Mmb\Calling\Caller;
use Mmb\Controller\StepHandler\StepHandler;
use Mmb\Exceptions\TypeException;

class FixedForm2Handler extends Form2Handler
{

    public $fixOn;

    /**
     * تنظیم متد فیکس فرم
     *
     * @param array|string $fixOn
     * @return void
     */
    public function setFixOn(array|string $fixOn)
    {
        $this->fixOn = $fixOn;
    }

    public function setForm($form)
    {
        // Ignore form
        $this->form = null;
    }
    
    public function handle()
    {
        if(is_null($this->fixOn))
        {
            return;
        }

        $fix = Caller::invoke2($this->fixOn);
        if(!($fix instanceof FixedForm2))
        {
            return;
        }
        $fix->handler = $this;

        $fix->getForm2()->continueForm();
    }
    
}
