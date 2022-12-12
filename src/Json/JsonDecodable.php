<?php

namespace Mmb\Json; #auto

trait JsonDecodable
{

    /**
     * تبدیل جیسون/آرایه به شی
     * 
     * @param string|array $value
     * @return static
     */
    public static function fromJson($value)
    {
        if(is_string($value))
            $value = json_decode($value, true);

        $cast = cast($value, static::class);
        if($cast)
        {
            $cast->loadedFromJson();
        }
        return $cast;
    }

    public function loadedFromJson() { }
    
}
