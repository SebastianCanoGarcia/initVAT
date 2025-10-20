<?php
namespace App\Models;

class Database
{
    private static $instance = null;
    private $pdo;

    private function __construct()
    {
        $host = 'localhost';
        $db = 'localdb';
        $user = 'root';
        $pass = 'password';
        $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
        $this->pdo = new \PDO($dsn, $user, $pass, [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
        ]);
    }

    public static function getInstance()
    {
        if (self::$instance === null) self::$instance = new self();
        return self::$instance;
    }

    public function getPdo() { return $this->pdo; }
}
