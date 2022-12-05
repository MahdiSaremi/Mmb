<?php

namespace Mds\Mmb\Tools; #auto

use Mds\Mmb\Update\Message\Data\Poll;

class Keys
{
    
    /**
     * ساخت تک دکمه درخواست شماره
     *
     * @param string $text
     * @return array
     */
    public static function reqContact($text){
        return ['text' => $text, 'contact' => true];
    }

    /**
     * ساخت تک دکمه درخواست موقعیت
     *
     * @param string $text
     * @return array
     */
    public static function reqLocation($text){
        return ['text' => $text, 'location' => true];
    }

    /**
     * ساخت تک دکمه درخواست ساخت نظرسنجی
     *
     * @param string $text
     * @param string $type
     * @return array
     */
    public static function reqPoll($text, $type = Poll::TYPE_REGULAR){
        return ['text' => $text, 'poll' => ['type' => $type]];
    }

    /**
     * ساخت حالت حذف دکمه ها
     *
     * @return string
     */
    public static function removeKey(){
        return '{"remove_keyboard": true}';
    }

    /**
     * ساخت حالت ریپلای اجباری
     *
     * @return string
     */
    public static function forceRep($placeholder = null, $selective = null){
        $ar = [
            'force_reply' => true
        ];
        if($placeholder)
            $ar['input_field_placeholder'] = $placeholder;
        if($selective !== null)
            $ar['selective'] = $selective;
        return json_encode($ar);
    }
}
