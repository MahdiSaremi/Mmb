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

    /**
     * دکمه های آپشن
     * @param int $colCount
     * @return ATool\AEach
     */
    public function options()
    {
        return aEach($this->form->getOptions());
    }

    /**
     * دکمه رد کردن
     * @param string $text
     * @return array|null
     */
    public function skip($text)
    {
        if (!$this->form->running_input->skipable)
            return null;

        return [ 'text' => $text, 'skip' => true ];
    }

    /**
     * دکمه لغو کردن
     * @param string $text
     * @return array
     */
    public function cancel($text)
    {
        return [ 'text' => $text, 'cancel' => true ];
    }

    /**
     * پردازش کیبورد غیر عادی
     * @param array $key
     * @return array
     */
    public static function parse(array $key)
    {
        return aParse($key);
    }

    /**
     * پیدا کردن دکمه کلیک شده
     * @param Upd $upd
     * @param array $key
     * @return array|null
     */
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
        // elseif ($callback = $upd->callback)
        // {
        //     // ...
        // }
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

    /**
     * تبدیل به کیبورد
     * @param array $key
     * @return array
     */
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

                $single = [ 'text' => @$btn['text'] ];

                if (isset($btn['contact']))
                    $single['contact'] = $btn['contact'];

                if (isset($btn['location']))
                    $single['location'] = $btn['location'];

                if (isset($btn['poll']))
                    $single['poll'] = $btn['poll'];

                $keyr[] = $single;
            }

            if ($keyr)
                $res[] = $keyr;
        }
        return $res;
    }

}
