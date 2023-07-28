<?php
#auto-name
namespace Mmb\Controller\Form;

abstract class ConfirmForm extends Form
{

    public function form()
    {
        $this->required('confirm');
    }

    public function confirm(FormInput $input)
    {
        $input
            ->onlyOptions()
            ->options(function() use($input) {
                return [
                    [ $input->option($this->getConfirmText(), true) ]
                ];
            })
            ->request(function() {
                $this->onRequest($this->getRequestText());
            });
    }

    /**
     * گرفتن متن دکمه تایید
     *
     * @return string
     */
    public abstract function getConfirmText();

    /**
     * گرفتن متن درخواست تایید
     *
     * @return string
     */
    public abstract function getRequestText();
    
}
