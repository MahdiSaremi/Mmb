<?php
#auto-name
namespace Mmb\Controller\FormV2\Inputs;

use Closure;
use Mmb\Controller\FormV2\Input;
use Mmb\Tools\ATool;

class ConfirmInput extends Input
{

    public function initialize()
    {
        $this
            ->onlyOptions()
            ->options(fn() => $this->addOptionMore($this->getConfirmText(), '@next'));
        parent::initialize();
    }

    /**
     * تنظیم تنظیمات
     *
     * @param string $confirm متن دکمه تایید
     * @return $this
     */
    public function settings($confirm)
    {
        return $this->setConfirmText($confirm);
    }

    private $confirm_text = "تایید";
    /**
     * تنظیم متن دکمه تایید
     *
     * @param string $text
     * @return $this
     */
    public function setConfirmText($text)
    {
        $this->confirm_text = $text;
        return $this;
    }
    /**
     * گرفتن متن دکمه تایید
     *
     * @return string
     */
    public function getConfirmText()
    {
        return $this->confirm_text;
    }

}
