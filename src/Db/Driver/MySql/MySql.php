<?php

namespace Mmb\Db\Driver\MySql; #auto

class MySql extends \Mmb\Db\Driver {

    public $queryCompiler = Query::class;

    /**
     * @var \mysqli
     */
    private $db;

    public function reset() {
        $this->db = null;
    }

    public function connect() {

        $host = config('database.host');
        $dbname = config('database.name');
        $username = config('database.username');
        $password = config('database.password');

        $this->db = new \mysqli($host, $username, $password, $dbname);

        if($this->db->error) {
            throw new \Exception("MySql unable to connect: " . $this->db->connect_error);
        }

    }

    /**
     * @param Query $queryCompiler
     * @return Result
     */
    public function query($queryCompiler)
    {
        if(!$this->db)
            $this->connect();
        
        $state = $this->db->prepare($queryCompiler->query);
        
        if(!$state)
            throw new \Exception("Error on query '{$queryCompiler->query}': " . $this->db->error);

        $ok = $state->execute();
        
        if($state->errno) {
            throw new \Exception("Error on query '{$queryCompiler->query}': " . $state->error);
        }

        return new Result($ok, $state, $this->db);

    }

}
