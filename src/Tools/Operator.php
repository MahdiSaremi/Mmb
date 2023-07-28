<?php
#auto-name
namespace Mmb\Tools;

class Operator
{

    /**
     * Operator +
     *
     * @param mixed $a
     * @param mixed $b
     * @return mixed
     */
    public static function add($a, $b)
    {
        if(is_object($a))
        {
            if(method_exists($a, 'add'))
            {
                return $a->add($b);
            }
        }

        return $a + $b;
    }
    

    /**
     * Operator -
     *
     * @param mixed $a
     * @param mixed $b
     * @return mixed
     */
    public static function substract($a, $b)
    {
        if(is_object($a))
        {
            if(method_exists($a, 'substract'))
            {
                return $a->substract($b);
            }
        }

        return $a - $b;
    }
    
    /**
     * Operator *
     *
     * @param mixed $a
     * @param mixed $b
     * @return mixed
     */
    public static function multiply($a, $b)
    {
        if(is_object($a))
        {
            if(method_exists($a, 'multiply'))
            {
                return $a->multiply($b);
            }
        }

        return $a * $b;
    }
    
    /**
     * Operator /
     *
     * @param mixed $a
     * @param mixed $b
     * @return mixed
     */
    public static function division($a, $b)
    {
        if(is_object($a))
        {
            if(method_exists($a, 'division'))
            {
                return $a->division($b);
            }
        }

        return $a / $b;
    }
    
    /**
     * Operator %
     *
     * @param mixed $a
     * @param mixed $b
     * @return mixed
     */
    public static function mod($a, $b)
    {
        if(is_object($a))
        {
            if(method_exists($a, 'mod'))
            {
                return $a->mod($b);
            }
        }

        return $a % $b;
    }
    
    /**
     * Operator >
     *
     * @param mixed $a
     * @param mixed $b
     * @return mixed
     */
    public static function isBiggerThan($a, $b)
    {
        if(is_object($a))
        {
            if(method_exists($a, 'isBiggerThan'))
            {
                return $a->isBiggerThan($b);
            }
        }

        return $a > $b;
    }
    
    /**
     * Operator >=
     *
     * @param mixed $a
     * @param mixed $b
     * @return mixed
     */
    public static function isEqualsOrBiggerThan($a, $b)
    {
        if(is_object($a))
        {
            if(method_exists($a, 'isEqualsOrBiggerThan'))
            {
                return $a->isEqualsOrBiggerThan($b);
            }
        }

        return $a >= $b;
    }
    
    /**
     * Operator <
     *
     * @param mixed $a
     * @param mixed $b
     * @return mixed
     */
    public static function isSmallerThan($a, $b)
    {
        if(is_object($a))
        {
            if(method_exists($a, 'isSmallerThan'))
            {
                return $a->isSmallerThan($b);
            }
        }

        return $a < $b;
    }
    
    /**
     * Operator <=
     *
     * @param mixed $a
     * @param mixed $b
     * @return mixed
     */
    public static function isEqualsOrSmallerThan($a, $b)
    {
        if(is_object($a))
        {
            if(method_exists($a, 'isEqualsOrSmallerThan'))
            {
                return $a->isEqualsOrSmallerThan($b);
            }
        }

        return $a <= $b;
    }
    
    /**
     * Operator ==
     *
     * @param mixed $a
     * @param mixed $b
     * @return mixed
     */
    public static function isEqualsTo($a, $b)
    {
        if(is_object($a))
        {
            if(method_exists($a, 'isEqualsTo'))
            {
                return $a->isEqualsTo($b);
            }
        }

        return $a == $b;
    }
    
    /**
     * Operator !=
     *
     * @param mixed $a
     * @param mixed $b
     * @return mixed
     */
    public static function isNotEqualsTo($a, $b)
    {
        if(is_object($a))
        {
            if(method_exists($a, 'isNotEqualsTo'))
            {
                return $a->isNotEqualsTo($b);
            }
        }

        return $a != $b;
    }
    
    /**
     * Operator !
     *
     * @param mixed $a
     * @return mixed
     */
    public static function not($a)
    {
        if(is_object($a))
        {
            if(method_exists($a, 'not'))
            {
                return $a->not();
            }
        }

        return !$a;
    }
    
}
