<?php

namespace App\Models;

use App\Database\Database;
use PDOException;
use PDO;

class Telegram extends Database
{
    protected $table = 'telegram';
    protected $fillable = [
        'price',
        'usd',
        'created_at',
        'updated_at',
    ];
    protected $hidden = [];
    protected $db;

    public function __construct(Database $db)
    {
        parent::__construct(); // Initialize the database connection
        $this->db = $db;
    }

    public function getTelegramInfo() {
        
        $sql = "SELECT * FROM  {$this->table}";
        // Prepare and execute the query
        $stmt = $this->db->prepare($sql);

        $stmt->execute();

        // Fetch and return results
        return $stmt->fetch(PDO::FETCH_ASSOC);

    }

    // Update an existing cart
    public function updateTelegram(array $data, $id = 'telegram_id')
    {
        $query = "UPDATE {$this->table} SET ";

        foreach ($data as $key => $value) {
            $query .= "$key = :$key, ";
        }

        $query = rtrim($query, ', ');
        $query .= " WHERE telegram_id = :id";

        try {
            $stmt = $this->db->prepare($query);

            foreach ($data as $key => $value) {
                $stmt->bindValue(":$key", $value);
            }
            $stmt->bindValue(":id", $id);

            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            var_dump($e->getMessage());
            return false;
        }
    }


    // Additional methods can be added here as needed
}

