<?php
#auto-name
namespace Mmb\Controller\FormV2;
use Mmb\Controller\StepHandler\StepHandler;

class Form2Handler extends StepHandler
{

    /**
     * اسم اینپوت فعلی
     *
     * @var ?string
     */
    public ?string $input = null;

    /**
     * اسم فرم
     *
     * @var string
     */
    public ?string $form;

    /**
     * تنظیم نام کلاس فرم
     *
     * @param string $form
     * @return void
     */
    public function setForm(string $form)
    {
        $this->form = $form;
    }
    
    /**
     * نقشه دکمه ها
     *
     * @var array|null
     */
    public ?array $options = null;

    /**
     * لیست مقادیر اینپوت ها
     *
     * @var array
     */
    public array $inputs = [];

    /**
     * مقدار تنظیم شده برای اینپوت را می دهد
     *
     * @param string $input
     * @return mixed
     */
    public function getValueOf(string $input)
    {
        return $this->inputs[$input] ?? null;
    }
    /**
     * بررسی می کند اینپوت مورد نظر تنظیم شده است
     *
     * @param string $input
     * @return boolean
     */
    public function issetValueOf(string $input)
    {
        return array_key_exists($input, $this->inputs);
    }
    /**
     * یک اینپوت را فراموش می کند
     *
     * @param string $input
     * @return void
     */
    public function forgotValueOf(string $input)
    {
        unset($this->inputs[$input]);
    }
    /**
     * تنظیم مقدار اینپوت
     *
     * @param string $input
     * @param mixed $value
     * @return void
     */
    public function setValueOf(string $input, $value)
    {
        $this->inputs[$input] = $value;
    }
    public function addValues(array $values)
    {
        $this->inputs = array_replace($this->inputs, $values);
    }

    public function handle()
    {
        if(class_exists($this->form))
        {
            // $clone = clone $this;
            // $form = $clone->form;
            // (new $form($clone))->continueForm();
            
            $form = $this->form;
            (new $form($this))->continueForm();
        }
    }

    public function __sleep()
    {
        return $this->getSleepNotNull();
    }
    
}
