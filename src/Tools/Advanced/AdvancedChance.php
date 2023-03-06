<?php
#auto-name
namespace Mmb\Tools\Advanced;
use Mmb\Tools\AdvancedValue;

abstract class AdvancedChance implements AdvancedValue
{

    protected abstract function getArray();

    protected abstract function selectced($value);
    
    public function getValue()
    {
        $array = $this->getArray();
        $sum_chance = array_sum(array_map(function($x) { return @$x[1]; }, $array));
        
        $rand = rand(1, $sum_chance);
        foreach($array as $value_chance)
        {
            $value = $value_chance[0];
            $rand -= $value_chance[1];
            if($rand <= 0)
            {
                $this->selectced($value);
                return $value;
            }
        }
    }

}
