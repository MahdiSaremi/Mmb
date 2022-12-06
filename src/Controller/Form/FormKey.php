<?php

namespace Mmb\Controller\Form; #auto

use Mmb\Tools\ATool;
use Mmb\Update\Upd;

class FormKey
{

    /**
     * @var Form
     */
    public $form;
    
    public function __construct(Form $form)
    {
        $this->form = $form;
    }

    public function options($colCount = 1)
    {
        return aEach(ATool::make2D($this->form->getOptions(), $colCount));
    }

    public function skip($text)
    {
        if (!$this->form->running_input->skipable)
            return null;

        return [ 'text' => $text, 'skip' => true ];
    }

    public function cancel($text)
    {
        return [ 'text' => $text, 'cancel' => true ];
    }

    public static function parse(array $key)
    {
        return aParse($key);
    }

    public static function findMatch(Upd $upd, array $key)
    {

        if ($msg = $upd->msg)
        {
            $text = $msg->text;
            $contact = $msg->contact;
            $location = $msg->location;
            $poll = $msg->poll;

            $check = $contact ? 'contact' : (
                $location ? 'location' : (
                    $poll ? 'poll' : 'text'
                )
            );
            $value = $check == 'text' ? $text : true;
        }
        elseif ($callback = $upd->callback)
        {
            // ...
        }
        else
        {
            return null;
        }

        foreach($key as $row)
        {
            if (!$row)
                continue;

            foreach($row as $btn)
            {
                if (!$btn)
                    continue;

                if (@$btn[$check] == $value)
                    return $btn;
            }
        }

        return null;
    }

    public static function toKey(array $key)
    {
        $res = [];
        foreach($key as $row)
        {
            $keyr = [];

            if (!$row)
                continue;

            foreach($row as $btn)
            {
                if (!$btn)
                    continue;

                $keyr[] = [ 'text' => @$btn['text'] ];
            }

            if ($keyr)
                $res[] = $keyr;
        }
        return $res;
    }

}
