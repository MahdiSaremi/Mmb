<?php

namespace Mmb\Update\Message\Data; #auto

use Mmb\Mmb;
use Mmb\MmbBase;
use Mmb\Tools\Text;

class Media extends MmbBase implements \Mmb\Update\Interfaces\IMsgDataID
{
    
    /**
     * آیدی فایل
     *
     * @var string
     */
    public $id;

    /**
     * نوع رسانه
     *
     * @var string
     */
    public string $type;

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
    public $permofer;

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
    function __construct(string $type, array $args, ?Mmb $mmb = null)
    {
        parent::__construct($args, $mmb);

        $this->initFrom($args, [
            'file_id' => 'id',
            'file_unique_id' => 'uniqueID',
            'file_size' => 'size',
            'file_name' => 'name',
            'thumb' => fn($thumb) => $this->thumb = new Media('photo', $thumb, $this->_base),
            'mime_type' => 'mime',
            'duration' => 'duration',
            
            'width' => 'width',
            'height' => 'height',
            'permofer' => 'permofer',
            'title' => 'title',
            'length' => 'duration',
        ]);
        if($this->name)
        {
            $this->ext = Text::afterLast($this->name, '.');
        }
        $this->type = $type;
    }
    
    /**
     * دانلود کردن فایل
     *
     * @param string $path محل دانلود
     * @return bool
     */
    function download($path)
    {
        return $this->_base->getFile($this->id)->download($path);
    }

    /**
     * دریافت اطلاعات فایل
     *
     * @return TelFile|false
     */
    public function getFile()
    {
        return $this->_base->getFile($this->id);
    }

	/**
	 * گرفتن آیدی پیام
	 *
	 * @return string
	 */
	function IMsgDataID()
    {
        return $this->id;
	}

}
