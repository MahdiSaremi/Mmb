<?php

namespace Mmb\Update\Message\Data; #auto

use Mmb\Mmb;
use Mmb\MmbBase;

class Media extends MmbBase implements \Mmb\Update\Interfaces\IMsgDataID
{
    /**
     * @var Mmb
     */
    private $_base;

    /**
     * آیدی فایل
     *
     * @var string
     */
    public $id;

    /**
     * آیدی یکتای فایل
     *
     * @var string
     */
    public $uniqueID;

    /**
     * حجم فایل
     *
     * @var int|null
     */
    public $size;

    /**
     * اسم فایل
     *
     * @var string|null
     */
    public $name;

    /**
     *
     * @var Media|null
     */
    public $thumb;

    /**
     * مایم تایپ
     *
     * @var string|null
     */
    public $mime;

    /**
     * طول رسانه(برای صوت، ویدیو، ...)
     *
     * @var int|null
     */
    public $duration;

    /**
     * عرض عکس، ویدیو یا گیف
     *
     * @var int|null
     */
    public $width;

    /**
     * ارتفاع عکس، ویدیو یا گیف
     *
     * @var int|null
     */
    public $height;

    /**
     * ایفا کننده ی صوت
     *
     * @var string|null
     */
    public $perfomer;

    /**
     * نام صوت
     *
     * @var string|null
     */
    public $title;

    /**
     * پسوند فایل
     *
     * @var string|null
     */
    public $ext;
    function __construct($type, $med, $base){

        $this->_base = $base;
        $this->id = $med['file_id'];
        $this->uniqueID = @$med['file_unique_id'];
        $this->size = @$med['file_size'];
        $this->name = @$med['file_name'];
        if($ext = $this->name){
            $ext = explode('.', $ext);
            $ext = end($ext);
            $this->ext = $ext;
        }
        if(isset($med['thumb']))
            $this->thumb = new Media("photo", $med['thumb'], $base);
        if(isset($med['mime_type']))
            $this->mime = $med['mime_type'];
        if(isset($med['duration']))
            $this->duration = $med['duration'];
        if($type == "photo"){
            $this->width = $med['width'];
            $this->height = $med['height'];
        }
        elseif($type == "audio"){
            $this->perfomer = @$med['permofer'];
            $this->title = @$med['title'];
        }
        elseif($type == "video"){
            $this->width = $med['width'];
            $this->height = $med['height'];
        }
        elseif($type == "anim"){
            $this->width = $med['width'];
            $this->height = $med['height'];
        }
        elseif($type == "videoNote"){
            $this->duration = $med['length'];
        }
    }
    
    /**
     * دانلود کردن فایل
     *
     * @param string $path محل دانلود
     * @return bool
     */
    function download($path){
        return $this->_base->getFile($this->id)->download($path);
    }

    /**
     * دریافت اطلاعات فایل
     *
     * @return TelFile|false
     */
    public function getFile(){
        return $this->_base->getFile($this->id);
    }

	/**
	 * گرفتن آیدی پیام
	 *
	 * @return int
	 */
	function IMsgDataID() {
        
        return $this->id;

	}

}
