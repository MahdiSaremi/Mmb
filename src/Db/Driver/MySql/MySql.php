<?php

namespace Mmb\Db\Driver\MySql; #auto

use Closure;
use Exception;
use Throwable;

class MySql extends \Mmb\Db\Driver
{

    public $queryCompiler = Query::class;

    /**
     * @var \mysqli
     */
    public $db;

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
            throw new Exception("MySql unable to connect: " . $this->db->connect_error);
        }
    }

    protected function getConnectedDb()
    {
        if(!$this->db)
            $this->connectForce();

        return $this->db;
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
            throw new Exception("Error on query '{$queryCompiler->query}': " . $this->db->error);

        $ok = $state->execute();
        
        if($state->errno) {
            throw new Exception("Error on query '{$queryCompiler->query}': " . $state->error);
        }

        return new Result($ok, $state, $this);
    }

    public function config($configPrefix = 'database')
    {
        static::$defaultHost = config("$configPrefix.host");
        static::$defaultUsername = config("$configPrefix.username");
        static::$defaultPassword = config("$configPrefix.password");
        static::$defaultDbname = config("$configPrefix.name");
        static::$defaultPort = config("$configPrefix.port");
    }

    public function getName()
    {
        return $this->dbname ?? static::$defaultDbname;
    }


    /**
     * ایجاد یک تراکنش
     * 
     * عملیات هایی که انجام می دهید، در انتها در دیتابیس تنظیم می شوند و اگر خطایی رخ دهد نیز تمامی عملیات ها کنسل می شوند
     *
     * همچنین اگر در تابع فالس(تنها مقدار دقیق فالس) ریترن شود، تغییرات ذخیره نخواهند شد
     * 
     * @param Closure $callback
     * @param int $flags `MYSQLI_TRANS_START_*`
     * @param string $name
     * @param MySql|null $db
     * @throws Exception
     * @return boolean
     */
    public static function transaction(Closure $callback, $flags = 0, $name = null, MySql $db = null)
    {
        $db = ($db ?? static::defaultStatic())->getConnectedDb();

        if($db->begin_transaction($flags, $name))
        {
            try
            {
                if($callback() !== false)
                {
                    $db->commit(0, $name);
                    return true;
                }
                else
                {
                    $db->rollback(0, $name);
                    return false;
                }
            }
            catch(Throwable $e)
            {
                $db->rollback(0, $name);
                throw $e;
            }
        }
        else
        {
            throw new Exception("Failed to start transaction");
        }
    }

    /**
     * ایجاد یک تراکنش با این کانکشن
     * 
     * عملیات هایی که انجام می دهید، در انتها در دیتابیس تنظیم می شوند و اگر خطایی رخ دهد نیز تمامی عملیات ها کنسل می شوند
     *
     * همچنین اگر در تابع فالس(تنها مقدار دقیق فالس) ریترن شود، تغییرات ذخیره نخواهند شد
     * 
     * @param Closure $callback
     * @param int $flags `MYSQLI_TRANS_START_*`
     * @param string $name
     * @throws Exception
     * @return boolean
     */
    public function runTransaction(Closure $callback, $flags = 0, $name = null)
    {
        return static::transaction($callback, $flags, $name, $this);
    }

}
