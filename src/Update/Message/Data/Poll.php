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
    public static function this()
    {
        return static::$this;
    }


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
    public $explanEntities;
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

    function __construct(array $args, ?Mmb $mmb = null)
    {
        parent::__construct($args, $mmb);

        if($this->_base->loading_update && !static::$this)
            self::$this = $this;

        $this->initFrom($args, [
            'id' => 'id',
            'question' => 'text',
            'total_voter_count' => 'votersCount',
            'options' => fn($options) => $this->options = array_map(fn($option) => new PollOption($option, $this, $this->_base), $options),
            'is_closed' => 'isClosed',
            'is_anonymous' => 'isAnonymous',
            'type' => 'type',
            'allows_multiple_answers' => 'multiple',
            'correct_option_id' => 'correct',
            'explanation' => 'explan',
            'explanation_entities' => fn($explanEntities) => $this->explanEntities = array_map(fn($entity) => new Entity($entity, $this->_base), $explanEntities),
            'open_period' => 'openPreiod',
            'close_date' => 'closeDate',
        ]);
    }
}
