<?php
#auto-name
namespace Mmb\Big;

use DivisionByZeroError;
use JsonSerializable;
use Mmb\Tools\ATool;
use Mmb\Tools\Text;

class BigNumber implements JsonSerializable
{

    protected $base;
    protected $digits;
    protected $decimals;
    protected $sign;

    public function __construct($value = null)
    {
        $this->setNumber($value);
    }

    public static function from($value)
    {
        return new static($value);
    }

    /**
     * تنظیم عدد
     *
     * @param mixed $value
     * @return void
     */
    public function setNumber($value)
    {
        if(is_null($value))
        {
            $this->setZero();
        }
        elseif(is_int($value))
        {
            if($value == 0)
            {
                $this->setZero();
                return;
            }
            elseif($value > 0)
            {
                $this->sign = true;
            }
            else
            {
                $this->sign = false;
                $value = -$value;
            }
            $this->base = $value % 1000;
            $this->digits = [];
            $this->decimals = [];
            while(($value = intval($value / 1000)) > 0)
            {
                $this->digits[] = $value % 1000;
            }
        }
        elseif(is_string($value))
        {
            if(!$value || $value == "0")
            {
                $this->setZero();
                return;
            }
            else
            {
                // Sign
                if(@$value[0] == '-')
                {
                    $this->sign = false;
                    $value = substr($value, 1);
                }
                elseif(@$value[0] == '+')
                {
                    $this->sign = true;
                    $value = substr($value, 1);
                }
                else
                {
                    $this->sign = true;
                }

                $exp = explode(".", $value);

                // Digits
                if($exp[0] != '')
                {
                    $req = 3 - (strlen($exp[0]) % 3);
                    if($req == 3) $req = 0;
                    $digits = str_split(str_repeat('0', $req) . $exp[0], 3);
                    if(is_numeric(end($digits)))
                    {
                        $this->base = intval(end($digits));
                    }
                    else
                    {
                        $this->setZero();
                        return;
                    }
                    $this->digits = [];
                    for($i = count($digits) - 2; $i >= 0; $i--)
                    {
                        if(!is_numeric($digits[$i]))
                        {
                            $this->setZero();
                            return;
                        }
                        $this->digits[] = intval($digits[$i]);
                    }
                }
                else
                {
                    $this->digits = [];
                    $this->base = 0;
                }

                // Decimals
                if(count($exp) == 1)
                {
                    $decimals = [];
                }
                elseif(count($exp) == 2)
                {
                    if($exp[1] != '')
                    {
                        $req = 3 - (strlen($exp[1]) % 3);
                        if($req == 3) $req = 0;
                        $decimals = str_split($exp[1] . str_repeat('0', $req), 3);
                    }
                    else
                    {
                        $decimals = [ ];
                    }
                }
                else
                {
                    $this->setZero();
                    return;
                }
                $this->decimals = [];
                foreach($decimals as $decimal)
                {
                    if(!is_numeric($decimal))
                    {
                        $this->setZero();
                        return;
                    }
                    $this->decimals[] = intval($decimal);
                }

                $this->clearZero();
            }
        }
        elseif($value instanceof BigNumber)
        {
            $this->sign = $value->sign;
            $this->base = $value->base;
            $this->digits = $value->digits;
            $this->decimals = $value->decimals;
        }
        else
        {
            $this->setZero();
        }
    }

    /**
     * تنظیم صفر
     *
     * @return void
     */
    public function setZero()
    {
        $this->base = 0;
        $this->digits = [ ];
        $this->decimals = [ ];
        $this->sign = null;
    }

    /**
     * حذف ستون های صفر اضافی
     *
     * @return void
     */
    public function clearZero()
    {
        for($i = count($this->digits) - 1; $i >= 0; $i--)
        {
            if($this->digits[$i] == 0)
            {
                unset($this->digits[$i]);
            }
            else break;
        }
        for($i = count($this->decimals) - 1; $i >= 0; $i--)
        {
            if($this->decimals[$i] == 0)
            {
                unset($this->decimals[$i]);
            }
            else break;
        }
        if(!$this->digits && !$this->decimals && !$this->base)
        {
            $this->sign = null;
        }
        elseif($this->sign === null)
        {
            $this->sign = true;
        }
    }

    protected function getIndex($index)
    {
        if($index == 0)
        {
            return $this->base;
        }
        elseif($index > 0)
        {
            return $this->digits[$index - 1] ?? 0;
        }
        else
        {
            return $this->decimals[-$index - 1] ?? 0;
        }
    }

    protected function existsIndex($index)
    {
        if($index > 0)
        {
            return isset($this->digits[$index - 1]);
        }

        return true;
    }

    protected function setIndex($index, $value)
    {
        $nextAdd = null;
        if($value >= 1000)
        {
            $nextAdd = intval($value / 1000);
            $value %= 1000;
        }

        if($index == 0)
        {
            $this->base = $value;
        }
        elseif($index > 0)
        {
            $j = $index - 1;
            if($j > count($this->digits))
            {
                for($i = count($this->digits); $i < $j; $i++)
                {
                    $this->digits[$i] = 0;
                }
            }
            $this->digits[$j] = $value;
        }
        else
        {
            $j = -$index - 1;
            if($j > count($this->decimals))
            {
                for($i = count($this->decimals); $i < $j; $i++)
                {
                    $this->decimals[$i] = 0;
                }
            }
            $this->decimals[$j] = $value;
        }

        if($nextAdd)
        {
            $this->setIndex($index + 1, $nextAdd);
        }
    }

    protected function addIndex($index, $value)
    {
        $value += $this->getIndex($index);

        $nextAdd = null;
        if($value >= 1000)
        {
            $nextAdd = intval($value / 1000);
            $value %= 1000;
        }

        $this->setIndex($index, $value);

        if($nextAdd)
        {
            $this->addIndex($index + 1, $nextAdd);
        }
    }

    protected function subIndex($index, $value)
    {
        $value = $this->getIndex($index) - $value;

        if($value < 0)
        {
            if(!$this->existsIndex($index + 1))
            {
                throw new \Exception("Error on substract number");
            }
            $this->subIndex($index + 1, 1);
            $value += 1000;
        }

        $this->setIndex($index, $value);
    }

    protected function shiftAll($index)
    {
        if($index > 0)
        {
            for($i = 0; $i < $index; $i++)
            {
                ATool::insert($this->digits, 0, $this->base);
                $this->base = $this->decimals[0] ?? 0;
                ATool::remove($this->decimals, 0);
            }
        }
        elseif($index < 0)
        {
            for($i = 0; $i < -$index; $i++)
            {
                ATool::insert($this->decimals, 0, $this->base);
                $this->base = $this->digits[0] ?? 0;
                ATool::remove($this->digits, 0);
            }
        }
        $this->clearZero();
    }

    protected function cutDecimal($maxDecimal)
    {
        $index = ceil($maxDecimal / 3) - 1;
        array_splice($this->decimals, $index + 1, count($this->decimals) - $index - 1, []);
        if(isset($this->decimals[$index]))
        {
            $dec = $this->decimals[$index];
            $dec = Text::minEnd(substr(Text::minStart($dec, 3, '0'), 0, $maxDecimal % 3 ?: 3), 3, '0');
            $this->decimals[$index] = intval($dec);
        }
        $this->clearZero();
    }

    protected $tempMaxIndex;
    protected function getMaxIndex()
    {
        if($this->digits)
        {
            return count($this->digits);
        }
        if($this->base)
        {
            return 0;
        }
        foreach($this->decimals as $index => $decimal)
        {
            if($decimal)
            {
                return -$index - 1;
            }
        }
        return 0;
    }

    protected $tempMinIndex;
    protected function getMinIndex()
    {
        if($this->decimals)
        {
            return -count($this->decimals);
        }
        if($this->base)
        {
            return 0;
        }
        foreach($this->digits as $index => $digit)
        {
            if($digit)
            {
                return $index + 1;
            }
        }
        return 0;
    }

    public function add($number2)
    {
        if(!($number2 instanceof BigNumber))
            $number2 = new BigNumber($number2);

        if($this->sign === null)
        {
            return new BigNumber($number2);
        }
        if($number2->sign === null)
        {
            return new BigNumber($this);
        }

        $result = new BigNumber;

        if($this->sign === $number2->sign)
        {
            // Add
            $result->setNumber($this);
            $result->addIndex(0, $number2->base);
            foreach($number2->digits as $i => $digit)
                $result->addIndex($i + 1, $digit);
            foreach($number2->decimals as $i => $decimal)
                $result->addIndex(-$i - 1, $decimal);
        }
        else
        {
            // Substract
            $compare = $this->compareIgnoreSign($number2);
            if($compare == 0)
            {
                return $result;
            }
            if($compare == 1)
            {
                $result->setNumber($this);
            }
            else
            {
                $result->setNumber($number2);
                $number2 = $this;
            }

            $result->subIndex(0, $number2->base);
            foreach($number2->digits as $i => $digit)
                $result->subIndex($i + 1, $digit);
            foreach($number2->decimals as $i => $decimal)
                $result->subIndex(-$i - 1, $decimal);
        }

        $result->clearZero();
        return $result;
    }

    public function substract($number2)
    {
        if(!($number2 instanceof BigNumber))
            $number2 = new BigNumber($number2);
        if($number2 == $this)
            $number2 = new BigNumber($this);

        if($number2->sign !== null)
            $number2->sign = !$number2->sign;

        $result = $this->add($number2);

        if($number2->sign !== null)
            $number2->sign = !$number2->sign;

        return $result;
    }

    public function multiply($number2)
    {
        if(!($number2 instanceof BigNumber))
            $number2 = new BigNumber($number2);

        $result = new BigNumber;

        $a_min = -count($this->decimals);
        $a_max = count($this->digits);
        $b_min = -count($number2->decimals);
        $b_max = count($number2->digits);

        for($a = $a_min; $a <= $a_max; $a++)
        {
            for($b = $b_min; $b <= $b_max; $b++)
            {
                $result->addIndex($a + $b, $this->getIndex($a) * $number2->getIndex($b));
            }
        }

        $result->sign = $this->sign == $number2->sign;
        $result->clearZero();
        return $result;
    }

    public function division($number2, $maxDecimal = null)
    {
        return $this->divisionRun($number2, $maxDecimal);
    }

    protected function divisionRun($number2, $maxDecimal = null, ?BigNumber &$remeaning = null)
    {
        if(!($number2 instanceof BigNumber))
            $number2 = new BigNumber($number2);

        if($this->sign === null)
        {
            return new BigNumber;
        }
        if($number2->sign === null)
        {
            throw new DivisionByZeroError("Division by zero");
        }

        $remeaning = new BigNumber($this);
        $result = new BigNumber;
        $divisor = new BigNumber($number2);

        if($remeaning->sign === false)
            $remeaning->sign = true;
        if($divisor->sign === false)
            $divisor->sign = true;

        $divisor->tempMaxIndex = $divisor->getMaxIndex();
        $divisor->tempMinIndex = $divisor->getMinIndex();
        $divisor->divisionShiftIndex = 0;
        $this->divisionCalc($remeaning, $divisor, $result);
        
        if($maxDecimal === null)
            $maxDecimal = static::$maxDecimal;
        $maxDecimalPart = ceil($maxDecimal / 3);
        while($remeaning->sign !== null && count($result->decimals) < $maxDecimalPart)
        {
            $divisor->divisionShiftIndex--;
            $divisor->tempMaxIndex--;
            $divisor->shiftAll(-1);
            $this->divisionCalc($remeaning, $divisor, $result);
        }
        $result->cutDecimal($maxDecimal);
        
        $result->sign = $this->sign === $number2->sign;
        $result->clearZero();
        return $result;
    }

    protected $divisionShiftIndex;
    protected function divisionCalc(BigNumber &$remeaning, BigNumber $divisor, BigNumber $result)
    {
        $r_max = $remeaning->getMaxIndex();
        if($r_max > $divisor->tempMaxIndex)
        {
            $divisor->shiftAll(1);
            $divisor->tempMaxIndex++;
            $divisor->divisionShiftIndex++;
            $this->divisionCalc($remeaning, $divisor, $result);
            $divisor->divisionShiftIndex--;
            $divisor->tempMaxIndex--;
            $divisor->shiftAll(-1);
        }

        for($ten = 100; $ten >= 1; $ten /= 10)
        {
            $calc = null;
            $calcX = null;
            for($i = 1; $i < 10; $i++)
            {
                $temp = $divisor->multiply($i * $ten);
                if($temp->isBiggerThan($remeaning))
                {
                    break;
                }
                $calc = $temp;
                $calcX = $i * $ten;
            }
            if($calc)
            {
                $index = $divisor->divisionShiftIndex;
                $result->addIndex($index, $calcX);
                $remeaning = $remeaning->substract($calc);
            }
        }
    }

    public function mod($number2)
    {
        if(!($number2 instanceof BigNumber))
            $number2 = new BigNumber($number2);

        if($this->sign === null)
        {
            return 0;
        }

        if(!$number2->digits && !$number2->decimals && $number2->sign === true)
        {
            switch($number2->base)
            {
                case 1:
                    $result = new BigNumber($this);
                    $result->base = 0;
                    $result->digits = [];
                    $result->clearZero();
                    return $result;

                case 2:
                case 4:
                case 5:
                case 10:
                case 20:
                case 50:
                case 100:
                case 200:
                case 500:
                    $result = new BigNumber($this);
                    $result->base %= $number2->base;
                    $result->digits = [];
                    $result->clearZero();
                    return $result;

                case 3:
                    return $this->mod3($this);

            }
        }

        $this->divisionRun($number2, 0, $remeaning);
        if($remeaning->sign !== null)
            $remeaning->sign = $this->sign;
        return $remeaning;
    }

    protected function mod3(BigNumber $number)
    {
        if(!$number->digits)
        {
            return new BigNumber(($number->base % 3) * ($this->sign === false ? -1 : 1));
        }

        $sum = new BigNumber;
        foreach($number->digits as $digit)
        {
            $digit = "$digit";
            $sum->addIndex(0, ($digit[0] ?? 0) + ($digit[1] ?? 0) + ($digit[2] ?? 0));
        }
        $digit = "{$number->base}";
        $sum->addIndex(0, ($digit[0] ?? 0) + ($digit[1] ?? 0) + ($digit[2] ?? 0));
        return $this->mod3($sum);
    }

    public function reverseSign()
    {
        $new = new BigNumber($this);
        $new->sign = !$this->sign;
        $new->clearZero();
        return $new;
    }

    public function absolute()
    {
        $new = new BigNumber($this);
        $new->sign = true;
        $new->clearZero();
        return $new;
    }
    
    public function isEqualsTo($number)
    {
        if(!($number instanceof BigNumber))
            $number = new BigNumber($number);

        if($this->sign === null || $number->sign === null)
        {
            return $this->sign === null && $number->sign === null;
        }
        if($this->sign != $number->sign)
        {
            return false;
        }

        $max = max(count($this->digits), count($number->digits));
        for($i = $max - 1; $i >= 0; $i--)
        {
            if(($this->digits[$i] ?? 0) != ($number->digits[$i] ?? 0))
                return false;
        }
        if($this->base != $number->base)
            return false;
        $max = max(count($this->decimals), count($number->decimals));
        for($i = 0; $i < $max; $i++)
        {
            if(($this->decimals[$i] ?? 0) != ($number->decimals[$i] ?? 0))
                return false;
        }

        return true;
    }

    public function isNotEqualsTo($number)
    {
        return !$this->isEqualsTo($number);
    }

    public function isBiggerThan($number)
    {
        return $this->compare($number) == 1;
    }

    public function isEqualsOrBiggerThan($number)
    {
        return $this->compare($number) != -1;
    }

    public function isSmallerThan($number)
    {
        return $this->compare($number) == -1;
    }

    public function isEqualsOrSmallerThan($number)
    {
        return $this->compare($number) != 1;
    }

    public function compare($number)
    {
        if(!($number instanceof BigNumber))
            $number = new BigNumber($number);

        if($this->sign === null)
        {
            if($number->sign === true)
                return -1;
            if($number->sign === false)
                return 1;
            return 0;
        }
        if($number->sign === null)
        {
            if($this->sign === true)
                return 1;
            if($this->sign === false)
                return -1;
            return 0;
        }
        if($number->sign != $this->sign)
        {
            return $this->sign ? 1 : -1;
        }

        if($this->sign)
        {
            $current = $this;
        }
        else
        {
            $current = $number;
            $number = $this;
        }

        $max = max(count($current->digits), count($number->digits));
        for($i = $max - 1; $i >= 0; $i--)
        {
            $a = $current->digits[$i] ?? 0;
            $b = $number->digits[$i] ?? 0;
            if($a > $b)
                return 1;
            if($a < $b)
                return -1;
        }
        if($current->base > $number->base)
            return 1;
        if($current->base < $number->base)
            return -1;
        $max = max(count($current->decimals), count($number->decimals));
        for($i = 0; $i < $max; $i++)
        {
            $a = $current->decimals[$i] ?? 0;
            $b = $number->decimals[$i] ?? 0;
            if($a > $b)
                return 1;
            if($a < $b)
                return -1;
        }

        return 0;
    }

    public function compareIgnoreSign($number)
    {
        if(!($number instanceof BigNumber))
            $number = new BigNumber($number);

        $max = max(count($this->digits), count($number->digits));
        for($i = $max - 1; $i >= 0; $i--)
        {
            $a = $this->digits[$i] ?? 0;
            $b = $number->digits[$i] ?? 0;
            if($a > $b)
                return 1;
            if($a < $b)
                return -1;
        }
        if($this->base > $number->base)
            return 1;
        if($this->base < $number->base)
            return -1;
        $max = max(count($this->decimals), count($number->decimals));
        for($i = 0; $i < $max; $i++)
        {
            $a = $this->decimals[$i] ?? 0;
            $b = $number->decimals[$i] ?? 0;
            if($a > $b)
                return 1;
            if($a < $b)
                return -1;
        }

        return 0;
    }

    /**
     * تبدیل عدد به رشته
     *
     * @return string
     */
    public function __toString()
    {
        // Digits
        $stringDigits = Text::minStart($this->base, 3, "0");
        for($i = 0; $i < count($this->digits); $i++)
        {
            $stringDigits = Text::minStart($this->digits[$i], 3, "0") . $stringDigits;
        }
        $stringDigits = ltrim($stringDigits, "0") ?: "0";

        // Decimals
        $stringDecimals = "";
        for($i = 0; $i < count($this->decimals); $i++)
        {
            $stringDecimals .= Text::minStart($this->decimals[$i], 3, "0");
        }
        $stringDecimals = rtrim($stringDecimals, "0");

        return ($this->sign === false ? '-' : '') . ($stringDigits) . ($stringDecimals ? '.' . $stringDecimals : '');
    }

    public function format($decimalsCount = null, string $decimalSeparator = ".", string $thousandsSeparator = ",")
    {
        // Digits
        $stringDigits = Text::minStart($this->base, 3, "0");
        for($i = 0; $i < count($this->digits); $i++)
        {
            $stringDigits = Text::minStart($this->digits[$i], 3, "0") . $thousandsSeparator . $stringDigits;
        }
        $stringDigits = ltrim($stringDigits, "0") ?: "0";

        // Decimals
        $stringDecimals = "";
        for($i = 0; $i < count($this->decimals); $i++)
        {
            $stringDecimals .= Text::minStart($this->decimals[$i], 3, "0");
            if($decimalsCount !== null)
                if(strlen($stringDecimals) >= $decimalsCount)
                    break;
        }
        if($decimalsCount !== null)
        {
            $stringDecimals = Text::minEnd(substr($stringDecimals, 0, $decimalsCount), $decimalsCount, "0");
        }
        else
        {
            $stringDecimals = rtrim($stringDecimals, "0");
        }

        return ($this->sign === false ? '-' : '') . ($stringDigits) . ($stringDecimals ? $decimalSeparator . $stringDecimals : '');
    }

    public function jsonSerialize()
    {
        return $this->__toString();
    }

    public $_s;
    public function __sleep()
    {
        $this->_s = $this->__toString();
        return [ '_s' ];
    }
    public function __wakeup()
    {
        $this->setNumber($this->_s);
    }
    
    public static $maxDecimal = 27;
    public static function setDivisionMaxDecimal($maxDecimal)
    {
        static::$maxDecimal = $maxDecimal;
    }

}
