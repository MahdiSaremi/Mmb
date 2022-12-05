<?php

namespace Mds\Mmb\Tools; #auto

class InlineResult
{

    public static function makeResult($results)
    {
        $r = [];
        foreach($results as $res){
            $r[] = self::makeSingle($res);
        }
        return json_encode($r);
    }

    public static function makeSingle($data)
    {
        if(($data = filterArray($data, [
            'id' => "id",
            'title' => "title",
            'msg' => "msg",
            'message' => "msg",
            'thumb' => "thumb",
            'cache' => "thumb",
            'des' => "des",
            'description' => "des",
            'photo' => "photo",
            'gif' => "gif",
            'mpeg4' => "mpeg4",
            'video' => "video",
            'audio' => "audio",
            'voice' => "voice",
            'doc' => "doc",
            'document' => "doc",
            'file' => "doc",
            //'location' => "location',"
            'contact' => "contact",
            'first' => 'first',
            'last' => 'last',
            'name' => "name",
        ])) === false)
            mmb_error_throw("Invalid inline query results data");
        $id = $data['id'] ?? rand(100000, 999999);
        $type = '';
        $media = '';
        $media_id = false;
        if(isset($data['photo'])){
            $type = 'photo';
            $media = $data['photo'];
        }
        elseif(isset($data['gif'])){
            $type = 'gif';
            $media = $data['gif'];
        }
        elseif(isset($data['mpeg4'])){
            $type = 'mpeg4_gif';
            $media = $data['mpeg4'];
        }
        elseif(isset($data['video'])){
            $type = 'video';
            $media = $data['video'];
        }
        elseif(isset($data['audio'])){
            $type = 'audio';
            $media = $data['audio'];
        }
        elseif(isset($data['voice'])){
            $type = 'voice';
            $media = $data['voice'];
        }
        elseif(isset($data['doc'])){
            $type = 'doc';
            $media = $data['doc'];
        }
        elseif(isset($data['contact'])){
            $type = 'contact';
        }
        else{
            $type = 'article';
        }
        if($media){
            if(is_string($media) && strpos($media, "://") === false){
                $media_id = true;
            }
        }
    
        $res = [
            'id' => $id,
            'type' => $type,
            'description' => $data['des'] ?? ""
        ];
        if($type == 'article'){
            $res['title'] = $data['title'] ?? "Untitled";
        }
        if($media){
            if(isset($data['title']))
                $res['title'] = $data['title'];
            if($type == 'mpeg4_gif'){
                $res['mpeg4' . ($media_id ? '_file_id' : '_url')] = $media;
            }
            else{
                $res[$type . ($media_id ? '_file_id' : '_url')] = $media;
            }
        }
        elseif($type == 'contact'){
            $res['contact'] = $data['contact'];
            if($data['name']){
                $f = $data['name'];
                $_ = strpos($f, " ");
                if($_ === false){
                    $l = null;
                }
                else{
                    $l = substr($f, $_ + 1);
                    $f = substr($f, 0, $l);
                }
            }
            else{
                if(isset($data['first'])){
                    $f = $data['first'];
                    $l = $data['last'] ?? null;
                }
                elseif(isset($data['last'])){
                    $f = $data['last'];
                    $l = null;
                }
                else{
                    $f = "Untitled";
                    $l = null;
                }
            }
            $res['first_name'] = $f;
            $res['last_name'] = $l;
        }
    
        $msg = $data['msg'] ?? [];
        if(($msg = filterArray($msg, [
            'text' => "text",
            'caption' => "text",
            'mode' => "mode",
            'parse_mode' => "mode",
            'parsemode' => "mode",
            'diswebpre' => "disw",
            "disable_web_page_preview" => "disw",
            'key' => 'key',
        ])) === false)
            mmb_error_throw("Invalid inline query results message data");
        if($media){
            $res['caption'] = $msg['text'] ?? "";
            if($_ = $msg['parse_mode'] ?? null){
                $res['parse_mode'] = $_;
            }
        }
        elseif($type == 'article'){
            $cn = [
                'message_text' => $msg['text'] ??  "Untitled"
            ];
            if($_ = $msg['disw'] ?? null){
                $cn['disable_web_page_preview'] = $_;
            }
            if($_ = $msg['parse_mode'] ?? null){
                $cn['parse_mode'] = $_;
            }
            $res['input_message_content'] = $cn;
        }
        if($_ = $msg['key'] ?? false)
            $res['reply_markup'] = Keyboard::makeKey($_, true, true, false);
    
        if($media){
            if(!$media_id){
                $res['thumb_url'] = $data['thumb'] ?? $media;
            }
        }
        elseif(isset($data['thumb'])){
            $res['thumb_url'] = $data['thumb'];
        }
    
        return $res;
    }
    
}
