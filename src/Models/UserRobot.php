<?php

namespace App\Models;

use App\Database\Database;
use PDOException;
use PDO;

class UserRobot 
{
    protected $table = 'user_robots';
    protected $fillable = ['user_id', 'robots'];
    protected $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    // Create or update user-course association based on data array
    public function createUserRobot(array $data)
    {
        $columns = implode(', ', array_keys($data));
        $values = ':' . implode(', :', array_keys($data));

        $query = "INSERT INTO {$this->table} ($columns) VALUES ($values)
                  ON DUPLICATE KEY UPDATE ";

        foreach ($data as $key => $value) {
            $query .= "$key = :$key, ";
        }

        $query = rtrim($query, ', ');

        try {
            $stmt = $this->db->prepare($query);

            foreach ($data as $key => $value) {
                $stmt->bindValue(":$key", $value);
            }

            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    // Fetch all courses for a user
    public function getUserRobots($user_id)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE user_id = :user_id");
        $stmt->execute(['user_id' => $user_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
        // return $stmt->get
    }

    // Delete a user-course association by ID
    public function deleteUserRobot($id)
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->rowCount() > 0;
    }

    // Update user courses based on user ID and new courses data
    public function updateUserRobots($user_id, $robots)
    {
        $query = "UPDATE {$this->table} SET robots = :robots WHERE user_id = :user_id";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(":user_id", $user_id);
            $stmt->bindValue(":robots", $robots);

            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    // Additional methods can be added here as needed
}

