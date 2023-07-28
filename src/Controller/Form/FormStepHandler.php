<?php

namespace Mmb\Controller\Form; #auto

use Mmb\Controller\StepHandler\Handlable;
use Mmb\Controller\StepHandler\StepHandler;

class FormStepHandler extends StepHandler
{

    /**
     * کلاس فرم مرتبط
     * @var string
     */
    public $form;

    /**
     * آخرین کلید ها
     * @var array
     */
    public $key;

    /**
     * اینپوت فعلی
     * @var string
     */
    public $current;

    /**
     * لیست مقادیر اینپوت ها
     * @var array
     */
    public $inputs = [];

    public function __construct($class)
    {
        $this->form = $class;
    }

    /**
     * شروع کردن فرم
     * @return Handlable|null
     */
    public function startForm()
    {
        $class = $this->form;
        $form = new $class($this);
        return $form->_start() ?: ($form->canceled ? null : $this);
    }

    /**
     * اجرای فرم
     * @return Handlable|null
     */
    public function handle()
    {
        $class = $this->form;
        $form = new $class($this);
        return $form->_next();
    }

    /**
     * گرفتن مقدار وارد شده اینپوت
     * @param FormInput $input
     * @param bool $form_option
     * @param bool $skip
     * @param bool $cancel
     * @return mixed
     */
    public function getValue(FormInput $input, &$form_option, &$skip, &$cancel)
    {
        $ignore_filters = false;
        if($this->key && $btn = FormKey::findMatch(upd(), $this->key))
        {
            $form_option = true;
            if(array_key_exists('skip', $btn))
            {
                $skip = true;
                $ignore_filters = true;
            }
            elseif(array_key_exists('cancel', $btn))
            {
                $cancel = true;
                $ignore_filters = true;
            }
            elseif(array_key_exists('value', $btn))
            {
                return $btn['value'];
            }

            // return $btn['text'];
        }

        return $input->applyFilters(upd(), $form_option, $ignore_filters);
    }

}
