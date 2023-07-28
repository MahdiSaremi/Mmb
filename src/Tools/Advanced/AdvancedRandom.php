<?php
#auto-name
namespace Mmb\Tools\Advanced;

use Mmb\Mapping\Arrayable;
use Mmb\Tools\AdvancedValue;

class AdvancedRandom implements AdvancedValue
{

    /**
     * @var array
     */
    public $array;
    
    public function __construct(array|Arrayable $array)
    {
        if($array instanceof Arrayable)
        {
            $array = $array->toArray();
        }

        $this->array = $array;
    }

    
	public function getValue()
    {
        return $this->array[array_rand($this->array)];
	}
}
