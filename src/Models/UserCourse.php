<?php

namespace App\Models;

use App\Database\Database;
use PDOException;
use PDO;

class UserCourse 
{
    protected $table = 'user_courses';
    protected $fillable = ['user_id', 'course_id', 'has_paid'];
    protected $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    // Create or update user-course association based on data array
    public function createUserCourse(array $data)
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
    public function getUserCourses($user_id)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE user_id = :user_id");
        $stmt->execute(['user_id' => $user_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Delete a user-course association by ID
    public function deleteUserCourse($id)
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->rowCount() > 0;
    }

    // Update user courses based on user ID and new courses data
    public function updateUserCourses($user_id, $courses)
    {
        $query = "UPDATE {$this->table} SET courses = :courses WHERE user_id = :user_id";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(":user_id", $user_id);
            $stmt->bindValue(":courses", $courses);

            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    // Additional methods can be added here as needed
}

