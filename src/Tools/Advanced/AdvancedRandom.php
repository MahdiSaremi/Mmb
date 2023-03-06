<?php
#auto-name
namespace Mmb\Tools\Advanced;

use Mmb\Tools\AdvancedValue;

class AdvancedRandom implements AdvancedValue
{

    /**
     * @var array
     */
    public $array;
    
    public function __construct(array $array)
    {
        $this->array = $array;
    }

    
	public function getValue()
    {
        return $this->array[array_rand($this->array)];
	}
}
