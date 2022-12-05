<?php

namespace Mds\Mmb\Db\Table; #auto

class Unknown extends Table {

	/**
	 * گرفتن نام تیبل
	 *
	 * @return string
	 */
	public static function getTable() {

        throw new \Mds\Mmb\Exceptions\MmbException("Unknown table");

	}

}
