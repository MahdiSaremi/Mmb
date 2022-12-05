<?php

namespace Mds\Mmb\Db; #auto

use Mds\Mmb\Exceptions\MmbException;
use Mds\Mmb\Handlers\Defaultable;

class Driver {

    use Defaultable;

    /**
     * @var string
     */
    public $queryCompiler;
    
    /**
     * ارسال کوئری با کامپایلر
     *
     * @param QueryCompiler $queryCompiler
     * @return QueryResult
     */
    public function query($queryCompiler) {

        throw new MmbException("No database driver found");

    }

}
