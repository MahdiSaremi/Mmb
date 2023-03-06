<?php
#auto-name
namespace Mmb\Tools\Advanced;

use Mmb\Listeners\Listeners;
use Mmb\Tools\AdvancedValue;

class AdvancedFunc implements AdvancedValue
{

    /**
     * @var callable
     */
    public $callable;
    
    public function __construct($callable)
    {
        $this->callable = $callable;
    }
    
    private $initialized = false;
    private $value;

	public function getValue()
    {
        if($this->initialized)
            return $this->value;

        $this->initialized = true;
        return $this->value = Listeners::callMethod($this->callable);
	}

}
