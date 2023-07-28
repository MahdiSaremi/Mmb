<?php

namespace Mmb\Db\Table; #auto

use Mmb\Exceptions\MmbException;

class Unknown extends Table
{

	/**
	 * گرفتن نام تیبل
	 *
	 * @return string
	 */
	public static function getTable()
	{
        throw new MmbException("Unknown table can't build query");
	}

    public function __construct($data)
    {
		$this->allData = $data;
    }

    public function __set($name, $value)
    {
        throw new MmbException("Try to set property on Unknown object");
    }

}
