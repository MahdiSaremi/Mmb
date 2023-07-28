<?php

namespace Mmb\Controller\Form; #auto

use Mmb\Exceptions\MmbException;

class FilterError extends MmbException
{

    /**
     * نام خطا
     *
     * @var string
     */
    public $name;

    /**
     * ورودی های خطا
     *
     * @var array
     */
    public $args;

    public function __construct($message, $name = null, array $args = [])
    {
        parent::__construct($message);
        $this->name = $name;
        $this->args = $args;
    }
    
}
