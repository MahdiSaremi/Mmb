<?php
#auto-name
namespace Mmb\Controller\FormV2\Inputs;

use Closure;
use Mmb\Controller\FormV2\Input;
use Mmb\Tools\ATool;

class MultiSelectInput extends Input
{

    public function initialize()
    {
        $this
            ->onlyOptions()
            ->options(fn() => $this->getEndTextAlign() == self::ALIGN_TOP && $this->addOptionMore($this->getEndText(), 'end'));
        parent::initialize();
        $this
            ->options(fn() => $this->getEndTextAlign() == self::ALIGN_BOTTOM && $this->addOptionMore($this->getEndText(), 'end'))
            ->click(function()
            {
                if(!$this->clickedOnMore)
                {
                    $this->toggle($this->getValue());
                }
            })
            ->clickOn('end', fn() => $this->forgotValue() || $this->form->next())
            ->reinputCycle();
    }

    /**
     * تنظیم تنظیمات انتخاب چندتایی
     *
     * @param string|Closure|null $selectEmoji
     * @param string|Closure|null $endText
     * @param string|Closure|null $endTextAlign
     * @return $this
     */
    public function settings(string|Closure|null $selectEmoji = null, string|Closure|null $endText = null, string|Closure|null $endTextAlign = null)
    {
        if(!is_null($selectEmoji))
        {
            $this->selectEmoji($selectEmoji);
        }
        if(!is_null($endText))
        {
            $this->endText($endText);
        }
        if(!is_null($endTextAlign))
        {
            $this->endTextAlign($endTextAlign);
        }
        return $this;
    }
    
    private $select_emoji = "☑️";
    /**
     * تنظیم اموجی انتخاب شده ها
     *
     * @param string|Closure $emoji
     * @return $this
     */
    public function selectEmoji(string|Closure $emoji)
    {
        $this->select_emoji = $emoji;
        return $this;
    }
    /**
     * گرفتن اموجی انتخاب شده ها
     *
     * @return string
     */
    public function getSelectEmoji()
    {
        if($this->select_emoji instanceof Closure)
        {
            $callback = $this->select_emoji;
            return $callback();
        }

        return $this->select_emoji;
    }
    
    private $end_text = "پایان";
    /**
     * تنظیم متن دکمه پایان
     *
     * @param string|Closure $text
     * @return $this
     */
    public function endText(string|Closure $text)
    {
        $this->end_text = $text;
        return $this;
    }
    /**
     * گرفتن متن دکمه پایان
     *
     * @return string
     */
    public function getEndText()
    {
        if($this->end_text instanceof Closure)
        {
            $callback = $this->end_text;
            return $callback();
        }

        return $this->end_text;
    }
    
    
    public const ALIGN_TOP = 'top';
    public const ALIGN_BOTTOM = 'bottom';
    private $end_text_align = 'top';
    /**
     * تنظیم چینش دکمه پایان
     * 
     * `'top'` or `'bottom'`
     *
     * @param string|Closure $align
     * @return $this
     */
    public function endTextAlign(string|Closure $align)
    {
        $this->end_text_align = $align;
        return $this;
    }
    /**
     * گرفتن چینش دکمه پایان
     *
     * @return string
     */
    public function getEndTextAlign()
    {
        if($this->end_text_align instanceof Closure)
        {
            $callback = $this->end_text_align;
            return $callback();
        }

        return $this->end_text_align;
    }
    
    private $save_as = null;
    /**
     * تنظیم نام اینپوت برای ذخیره سازی انتخاب ها
     *
     * @param string|Closure $name
     * @return $this
     */
    public function saveAs(string|Closure $name)
    {
        $this->save_as = $name;
        return $this;
    }
    /**
     * گرفتن نام اینپوت برای ذخیره سازی انتخاب ها
     *
     * @return string
     */
    public function getSaveAs()
    {
        if($this->save_as instanceof Closure)
        {
            $callback = $this->save_as;
            return $callback();
        }

        return $this->save_as;
    }
    
    /**
     * گرفتن انتخاب ها
     *
     * @return array
     */
    public function getSelects()
    {
        return $this->form->get($this->getSaveAs() ?? '_' . $this->name) ?? [];
    }
    /**
     * تنظیم انتخاب ها
     *
     * @param array $value
     * @return void
     */
    public function setSelects(array $value)
    {
        $this->form->set($this->getSaveAs() ?? '_' . $this->name, $value);
    }

    /**
     * انتخاب کردن یک گزینه
     *
     * @param mixed $value
     * @return void
     */
    public function select($value)
    {
        $selects = $this->getSelects();
        $selects[] = $value;
        $this->setSelects($selects);
    }

    /**
     * برداشتن انتخاب یک گزینه
     *
     * @param mixed $value
     * @return void
     */
    public function unselect($value)
    {
        $selects = $this->getSelects();
        ATool::remove2($selects, $value);
        $this->setSelects($selects);
    }

    /**
     * بررسی می کند یک گزینه انتخاب شده است یا خیر
     *
     * @param mixed $value
     * @return boolean
     */
    public function isSelected($value)
    {
        $selects = $this->getSelects();
        return array_search($value, $selects) !== false;
    }

    /**
     * توگل کردن یک گزینه
     * 
     * اگر انتخاب شده بود، از انتخاب برمیدارد و اگر انتخاب نشده بود، انتخاب می کند
     *
     * @param mixed $value
     * @return void
     */
    public function toggle($value)
    {
        if($this->isSelected($value))
        {
            $this->unselect($value);
        }
        else
        {
            $this->select($value);
        }
    }

    protected function opFilter(array $op)
    {
        $op = parent::opFilter($op);

        if(array_key_exists('value', $op))
        {
            if($this->isSelected($op['value']))
            {
                $op['text'] = $this->getSelectEmoji() . " " . $op['text'];
            }
        }
        elseif(!array_key_exists('more', $op))
        {
            if($this->isSelected($op['text']))
            {
                $op['value'] = $op['text'];
                $op['text'] = $this->getSelectEmoji() . " " . $op['text'];
            }
        }

        return $op;
    }

}
