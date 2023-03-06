<?php

namespace Mmb\Tools; #auto

use Mmb\Update\Message\Data\Poll;

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

    /**
     * ساخت کلید شیشه ای لینک دار
     * 
     * @param string $text
     * @param string $url
     * @return array
     */
    public static function url($text, $url)
    {
        return [ 'text' => $text, 'url' => $url ];
    }

    /**
     * ساخت کلید شیشه ای هدایت کننده اینلاین
     * 
     * @param string $text
     * @param string $inline
     * @return array
     */
    public static function inline($text, $inline)
    {
        return [ 'text' => $text, 'inline' => $inline ];
    }

    /**
     * ساخت کلید شیشه ای هدایت کننده اینلاین در این چت
     * 
     * @param string $text
     * @param string $inline
     * @return array
     */
    public static function inlineThis($text, $inline)
    {
        return [ 'text' => $text, 'inlineThis' => $inline ];
    }

    /**
     * ساخت کیبورد
     *
     * @param array $key دکمه ها
     * @param bool|null $inline اینلاین بودن
     * @param bool $resize ریسایز خودکار
     * @param bool $encode انکد کردن نتیجه
     * @param bool $once کیبورد یکباره
     * @param bool $selective سلکتیو
     * @return string|array
     */
    public static function makeKey($key, $inline=null, $resize=true, $encode=true, $once=false, $selective=false)
    {
        if(isset($key['key'])){
            return self::makeKey(
                $key['key'],
                $key['inline'] ?? null,
                $key['resize'] ?? true,
                $key['encode'] ?? true,
                $key['once'] ?? false,
                $key['selective'] ?? false
            );
        }
        if(($key = filterArray3D($key, [
            'text',
            'data'=>"callback_data",
            'text'=>"text",
            'callback_data'=>"callback_data",
            'url'=>"url",
            'switch_inline_query'=>"switch_inline_query",
            'inline'=>"switch_inline_query",
            'switch_inline_query_current_chat' => 'switch_inline_query_current_chat',
            'inline_this' => 'switch_inline_query_current_chat',
            'inlinethis' => 'switch_inline_query_current_chat',
            'inlineThis' => 'switch_inline_query_current_chat',
            'request_contact' => 'request_contact',
            'contact' => "request_contact",
            "request_location" => "request_location",
            "location" => "request_location",
            "request_poll" => "requset_poll",
            "poll" => "request_poll"
        ], null, true, true, false)) === false)
            mmb_error_throw("Invalid keyboard");
        if($inline === null){
            if($key != null)
                $inline = @isset($key[0][0]['callback_data']) || @isset($key[0][0]['url']) || @isset($key[0][0]['switch_inline_query']) || @isset($key[0][0]['switch_inline_query_current_chat']);
        }
        $a = [($inline?"inline_":"")."keyboard" => $key];
        if(!$inline && $resize) $a['resize_keyboard'] = $resize;
        if($once) $a['one_time_keyboard'] = true;
        if($selective) $a['selective'] = true;
        if($encode)
            $a = json_encode($a);
        return $a;
    }

}
