<?php

// Copyright (C): t.me/MMBlib

namespace Mmb\Update\Message\Data; #auto

use Mmb\Mmb;
use Mmb\MmbBase;

class Poll extends MmbBase
{
    
    /**
     * شی اصلی این کلاس
     * 
     * @var static
     */
    public static $this;


    /**
     * @var Mmb
     */
    private $_base;

    /**
     * آیدی
     *
     * @var string
     */
    public $id;
    /**
     * سوال
     *
     * @var string
     */
    public $text;
    /**
     * گزینه ها
     *
     * @var PollOption[]
     */
    public $options;
    /**
     * تعداد رای دهندگان
     *
     * @var int
     */
    public $votersCount;
    /**
     * آیا پول بسته است
     *
     * @var bool
     */
    public $isClosed;
    /**
     * آیا ناشناس است
     *
     * @var bool
     */
    public $isAnonymous;
    /**
     * نوع
     *
     * @var string
     */
    public $type;
    public const TYPE_REGULAR = 'regular';
    public const TYPE_QUIZ = 'quiz';

    /**
     * آیا چند گزینه ای فعال است
     *
     * @var bool
     */
    public $multiple;
    /**
     * ایندکس گزینه صحیح
     *
     * @var int
     */
    public $correct;
    /**
     * توضیحات
     *
     * @var string
     */
    public $explan;
    /**
     * موجودیت های توضیحات
     *
     * @var Entity[]
     */
    public $explanEntites;
    /**
     * زمان فعال بودن نظرسنجی
     *
     * @var int
     */
    public $openPreiod;
    /**
     * زمانی که نظرسنجی خودکار بسته می شود
     *
     * @var int
     */
    public $closeDate;

    /**
     * 
     *
     * @var Entity[]
     */
    public $explanation_entities;

    function __construct($b, Mmb $base){

        if($base->loading_update && !static::$this)
            self::$this = $this;

        $this->_base = $base;
        $this->id = $b['id'];
        $this->text = $b['question'];
        $this->options = [];
        $this->votersCount = $b['total_voter_count'];
        $_ = $b['options'];
        foreach($_ as $__){
            $this->options[] = new PollOption($__, $this, $base);
        }
        $this->isClosed = $b['is_closed'];
        $this->isAnonymous = $b['is_anonymous'];
        $this->type = $b['type'];
        $this->multiple = $b['allows_multiple_answers'];
        $this->correct = $b['correct_option_id'];
        $this->explan = $b['explanation'];
        $this->explanation_entities = [];
        $_ = @$b['explanation_entities'];
        if($_)
        foreach($_ as $__){
            $this->explanation_entities[] = new Entity($__, $base);
        }
        $this->openPreiod = @$b['open_period'];
        $this->closeDate = @$b['close_date'];

    }
}
