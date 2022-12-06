<?php

namespace Mmb\Controller\Form; #auto

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

    public function startForm()
    {
        $class = $this->form;
        $form = new $class($this);
        return $form->_start() ?: $this;
    }

    public function handle()
    {
        $class = $this->form;
        $form = new $class($this);
        return $form->_next();
    }

    public function getValue(FormInput $input, &$form_option, &$skip, &$cancel)
    {
        if($this->key && $btn = FormKey::findMatch(upd(), $this->key))
        {
            $form_option = true;
            if(array_key_exists('skip', $btn))
            {
                $skip = true;
            }
            elseif(array_key_exists('cancel', $btn))
            {
                $cancel = true;
            }
            elseif(array_key_exists('value', $btn))
            {
                return $btn['value'];
            }

            return $btn['text'];
        }

        return $input->applyFilters(upd());
    }

}
