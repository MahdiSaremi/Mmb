<?php
#auto-name
namespace Mmb\Controller\FormV2\Fix;
use Mmb\Controller\FormV2\Form2;
use Mmb\Controller\FormV2\Input;

class FixedForm2Form extends Form2
{

    public function __construct(
        public FixedForm2 $fix
    )
    {
        parent::__construct($fix->handler);
    }

    public function form()
    {
        $this->fix->loadProperties();
        foreach($this->fix->getInputList() as $name)
        {
            $this->required($name, $this->fix->getTypeOf($name));
        }
    }

    public function onInputInitializing(Input $input)
    {
        $this->fix->fireInput($input);
    }

    public function saveStep()
    {
        $this->fix->saveProperties();
        parent::saveStep();
    }

    public function onBack($message = null)
    {
        return $this->fix->fireBack($this, $message);
    }

    public function onCancel()
    {
        return $this->fix->fireCancel($this);
    }

    public function onFinish()
    {
        return $this->fix->fireFinish($this);
    }
    
}
