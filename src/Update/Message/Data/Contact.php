<?php

namespace Mmb\Update\Message\Data; #auto

use Mmb\Mmb;
use Mmb\MmbBase;

class Contact extends MmbBase
{

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
     * @deprecated
     * @var int
     */
    public $userID;
    /**
     * ایدی عددی صاحب مخاطب
     *
     * @var int
     */
    public $userId;
    function __construct(array $args, ?Mmb $mmb = null)
    {
        parent::__construct($args, $mmb);

        $this->initFrom($args, [
            'phone_number' => 'num',
            'first_name' => 'firstName',
            'last_name' => 'lastName',
            'user_id' => 'userId',
        ]);

        $this->name = $this->firstName . ($this->lastName ? " " . $this->lastName : "");
        $this->userID = $this->userId;
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
