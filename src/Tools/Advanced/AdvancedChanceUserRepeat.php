<?php
#auto-name
namespace Mmb\Tools\Advanced;

use Mmb\Mapping\Arrayable;
use Mmb\Update\User\UserInfo;

class AdvancedChanceUserRepeat extends AdvancedChance
{
    
    /**
     * @var array
     */
    public $array;
    public $name;
    public $remember;
    public $focusScale;
    
    public function __construct(array|Arrayable $array, $name, $rememberTime, $focusScale)
    {
        if($array instanceof Arrayable)
        {
            $array = $array->toArray();
        }

        $this->array = $array;
        $this->name = $name;
        $this->remember = $rememberTime;
        $this->focusScale = $focusScale;
    }


	protected function getArray()
    {
        $id = optional(UserInfo::$this)->id . '.' . $this->name;
        $repeat = AdvUserRepatStorage::get($id);
        if($repeat === null)
            $repeat = [ 0, 0 ];
        elseif(time() - $this->remember > $repeat[1])
            $repeat = [ 0, 0 ];

        $repeat[0]++;
        $repeat[1] = time();

        AdvUserRepatStorage::set($id, $repeat);

        $array = $this->array;
        $count = count($array);
        $over = 0;
        if($repeat[0] >= $count)
        {
            $index = $count - 1;
            $over = $repeat[0] - $count;
        }
        else
        {
            $index = $repeat[0] - 1;
        }

        $newArray = [];
        foreach($array as $i => $value)
        {
            $newArray[] = [ $value, ($count - abs($index - $i)) * ($i == $index ? $this->focusScale : 1) + ($i == $index ? $over : 0) ];
        }

        return $newArray;
	}
	
	protected function selectced($value)
    {
	}

}
