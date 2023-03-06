<?php
#auto-name
namespace Mmb\Tools\Advanced;

use Mmb\Tools\AdvancedValue;

class AdvancedRandomChance implements AdvancedValue
{

    /**
     * @var array
     */
    public $array;
    
    public function __construct(array $array)
    {
        $this->array = $array;
    }


    private $sum_chance;
    
	public function getValue()
    {
        if($this->sum_chance === null)
        {
            $this->sum_chance = array_sum(array_map(function($x) { return @$x[1]; }, $this->array));
        }
        
        $rand = rand(1, $this->sum_chance);
        foreach($this->array as $value_chance)
        {
            $value = $value_chance[0];
            $rand -= $value_chance[1];
            if($rand <= 0)
            {
                return $value;
            }
        }
	}
}
