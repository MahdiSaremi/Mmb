<?php

namespace Mmb\Update\Message\Data; #auto

use Mmb\Mmb;
use Mmb\MmbBase;

class Contact extends MmbBase
{

    /**
     * @var Mmb
     */
    private $_base;

    /**
     * شماره کاربر
     *
     * @var string
     */
    public $num;
    /**
     * نام کوچک مخاطب
     *
     * @var string
     */
    public $firstName;
    /**
     * نام بزرگ مخاطب
     *
     * @var string
     */
    public $lastName;
    /**
     * نام کامل مخاطب
     *
     * @var string
     */
    public $name;
    /**
     * ایدی عددی صاحب مخاطب
     *
     * @var int
     */
    public $userID;
    function __construct($con, $base)
    {
        $this->_base = $base;
        $this->num = $con['phone_number'];
        $this->firstName = @$con['first_name'];
        $this->lastName = @$con['last_name'];
        $this->name = $this->firstName . ($this->lastName ? " " . $this->lastName : "");
        $this->userID = @$con['user_id'];
    }

    /**
     * Check number valid
     * بررسی اعتبار شماره یا کد کشور
     *
     * @param string $country
     * @return boolean
     */
    public function isValid($country = '98')
    {
        return (bool)preg_match('/^(00|\+|)' . $country . '/', $this->num);
    }
}
