<?php

// namespace App\Database;

// use PDO;
// use PDOException;

// class Database extends PDO
// {
//     protected $db;

//     public function __construct()
//     {
//         $this->connect();
//     }

//     private function connect()
//     {
//         $host = $_ENV['DB_HOST'];
//         $dbname = $_ENV['DB_NAME'];
//         $username = $_ENV['DB_USERNAME'];
//         $password = $_ENV['DB_PASSWORD'];

//         $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";

//         try {
//             $this->db = new PDO($dsn, $username, $password);
//             $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
//         } catch (PDOException $e) {
//             die("Connection failed: " . $e->getMessage());
//         }
//     }
// }




namespace App\Database;

use PDO;
use PDOException;

class Database extends PDO
{
    protected $db;

    public function __construct()
    {
        try {
            $this->connect();
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    private function connect()
    {
        $host = $_ENV['DB_HOST'];
        $dbname = $_ENV['DB_NAME'];
        $username = $_ENV['DB_USERNAME'];
        $password = $_ENV['DB_PASSWORD'];

        $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";

        parent::__construct($dsn, $username, $password);
        $this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
}
