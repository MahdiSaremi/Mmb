<?php

namespace Mmb\Db\Driver\MySql; #auto

class MySql extends \Mmb\Db\Driver {

    public $queryCompiler = Query::class;

    /**
     * @var \mysqli
     */
    private $db;

    public function reset()
    {
        $this->db = null;
    }

    /**
     * هاست پیشفرض
     * 
     * @var string|null
     */
    public static $defaultHost = null;
    /**
     * یوزرنیم پیشفرض
     * 
     * @var string|null
     */
    public static $defaultUsername = null;
    /**
     * رمز عبور پیشفرض
     * 
     * @var string|null
     */
    public static $defaultPassword = null;
    /**
     * دیتابیس پیشفرض
     * 
     * @var string|null
     */
    public static $defaultDbname = null;
    /**
     * پورت پیشفرض
     * 
     * @var string|null
     */
    public static $defaultPort = null;

    private $host;
    private $username;
    private $password;
    private $dbname;
    private $port;

    /**
     * تنظیم اتصال به دیتابیس
     * 
     * @param string $host
     * @param string $dbname
     * @param string $username
     * @param string $password
     * @param int $port
     * @return void
     */
    public function connect($host, $username, $password, $dbname = null, $port = null)
    {
        $this->host = $host;
        $this->dbname = $dbname;
        $this->username = $username;
        $this->password = $password;
        $this->port = $port;
    }

    /**
     * اتصال به دیتابیس
     * 
     * هاست/نام کاربری/رمز/پورت از مقدار پیشفرض عمومی گرفته می شود
     * 
     * @param string $dbname
     * @return void
     */
    public function connectDb($dbname)
    {
        $this->dbname = $dbname;
    }

    /**
     * اتصال اجباری و فوری به دیتابیس تنظیم شده
     * 
     * @throws \Exception
     * @return void
     */
    public function connectForce()
    {

        $host = $this->host ?? static::$defaultHost;
        $dbname = $this->dbname ?? static::$defaultDbname;
        $username = $this->username ?? static::$defaultUsername;
        $password = $this->password ?? static::$defaultPassword;
        $port = $this->port ?? static::$defaultPort;

        $this->db = new \mysqli($host, $username, $password, $dbname, $port);

        if($this->db->error) {
            throw new \Exception("MySql unable to connect: " . $this->db->connect_error);
        }

    }

    /**
     * @param Query $queryCompiler
     * @return Result
     */
    public function runQuery($queryCompiler)
    {
        if(!$this->db)
            $this->connectForce();
        
        $state = $this->db->prepare($queryCompiler->query);
        
        if(!$state)
            throw new \Exception("Error on query '{$queryCompiler->query}': " . $this->db->error);

        $ok = $state->execute();
        
        if($state->errno) {
            throw new \Exception("Error on query '{$queryCompiler->query}': " . $state->error);
        }

        return new Result($ok, $state, $this->db);

    }

    public function config($configPrefix = 'database')
    {
        static::$defaultHost = config("$configPrefix.host");
        static::$defaultUsername = config("$configPrefix.username");
        static::$defaultPassword = config("$configPrefix.password");
        static::$defaultDbname = config("$configPrefix.name");
        static::$defaultPort = config("$configPrefix.port");
    }

}
