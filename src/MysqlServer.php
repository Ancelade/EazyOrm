<?php


namespace Ancelade\EazyOrm;


use PDO;

class MysqlServer
{
    /**
     * @var MysqlServer[]
     */
    public static array $connections = [];

    public string $host;
    public string $username;
    public string $password;
    public string $database;
    public int $port;


    /**
     *
     * Define an instance of your mysql server
     * @param string $host
     * @param string $username
     * @param string $password
     * @param string $database
     * @param int $port
     */
    public function __construct(string $host, string $username, string $password, string $database, int $port = 3306)
    {
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
        $this->database = $database;
        $this->port = $port;
    }

    /**
     * Add a new server on this ORM
     * @param string $name your connection name
     * @param MysqlServer $config You instance of MysqlServer
     */
    public static function addConfig(string $name, MysqlServer $config) : void
    {
        static::$connections[$name] = $config;
    }

    /**
     * Get a native pdo object
     * @return PDO
     */
    public function getPdo() : PDO
    {
        return new PDO('mysql:host=' . $this->host . ':' . $this->port . ';
        dbname=' . $this->database, $this->username, $this->password,
            [
                PDO::ATTR_PERSISTENT => true, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);
    }

}
