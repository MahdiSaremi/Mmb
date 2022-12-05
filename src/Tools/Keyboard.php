<?php

namespace Mds\Mmb\Tools; #auto

class Keyboard
{

        
    /**
     * ساخت کیبورد
     *
     * @param array $key دکمه ها
     * @param bool|null $inline اینلاین بودن
     * @param boolean $resize ریسایز خودکار
     * @param boolean $encode انکد کردن نتیجه
     * @param boolean $once کیبورد یکباره
     * @param boolean $selective سلکتیو
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
