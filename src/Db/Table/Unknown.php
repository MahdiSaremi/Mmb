<?php

namespace Mmb\Db\Table; #auto

class Unknown extends Table {

	/**
	 * گرفتن نام تیبل
	 *
	 * @return string
	 */
	public static function getTable() {

        throw new \Mmb\Exceptions\MmbException("Unknown table");

	}

}
