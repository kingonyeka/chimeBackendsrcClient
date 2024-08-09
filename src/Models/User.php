<?php

namespace App\Models;

use App\Database\Database;
use PDOException;
use PDO;
use Monolog\Logger;

class User extends Database
{
    protected $table = 'users';
    // protected $fillable = ['email', 'password'];
    protected $fillable = [
        'email',
        'password',
        'first_name',
        'last_name',
        'middle_name',
        'address',
        'user_id',
        'status',
        'courses_purchased',
        'robots_purchased',
        'joined_telegram',
        'last_logged_in',
        'verified_email',
        'date_last_modified'
    ];
    
    protected $hidden = ['password'];
    protected $db;

    public function __construct(Database $db)
    {
        parent::__construct(); // Initialize the database connection
        $this->db = $db;
    }

    // Fetch all users
    public function getUsersCount()
    {
        $stmt = $this->db->query("SELECT COUNT(*) FROM users");
        return $stmt->fetchColumn();
    }

    public function getUsers($limit = 10, $offset = 0, $sortBy = 'first_name', $orderDirection = 'asc', $search = null)
    {
        // Define valid and forbidden fields
        $forbiddenFields = ['password', 'last_login'];
        $validColumns = array_diff(['first_name', 'last_name', 'email', 'user_id', 'middle_name', 'address', 'courses_purchased', 'robots_purchased', 'joined_telegram', 'last_logged_in', 'verified_email', 'status' ], $forbiddenFields);

        // Map column index to column name
        $columnIndexToName = [
            0 => 'sn',
            1 => 'surname',
            2 => 'username',
            3 => 'role'
            // Add more mappings as needed for additional columns
        ];

        // Ensure sortBy is a valid column
        if (!in_array($sortBy, $validColumns)) {
            $sortBy = 'username';
        }

        // Start building the query
        $sql = "SELECT " . implode(", ", $validColumns) . " FROM {$this->table}";

        // Apply search condition if provided
        if ($search !== null) {
            $searchConditions = [];
            foreach ($validColumns as $column) {
                $searchConditions[] = "$column LIKE :search";
            }
            $sql .= " WHERE " . implode(" OR ", $searchConditions);
        }

        // Apply sorting, limit, and offset
        $sql .= " ORDER BY $sortBy $orderDirection";
        $sql .= " LIMIT :limit OFFSET :offset";

        // Prepare and execute the query
        $stmt = $this->db->prepare($sql);

        // Bind parameters
        if ($search !== null) {
            $stmt->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);

        $stmt->execute();

        // Fetch and return results
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Create or update user based on data array
    public function createUser(array $data)
    {
        $columns = implode(', ', array_keys($data));
        $values = ':' . implode(', :', array_keys($data));

        $query = "INSERT INTO users ($columns) VALUES ($values)
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

    // Update user based on email and data array
    public function updateUser(array $data, $user_id) {
        $query = "UPDATE users SET ";

        foreach ($data as $key => $value) {
            $query .= "$key = :$key, ";
        }

        $query = rtrim($query, ', ');
        $query .= " WHERE user_id = :user_id";

        try {
            $stmt = $this->db->prepare($query);

            foreach ($data as $key => $value) {
                $stmt->bindValue(":$key", $value);
            }
            $stmt->bindValue(":user_id", $user_id);

            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            var_dump($e->getMessage());
            return false;
        }
    }

    // Update user based on email and data array
    public function updateUserUsingEmail(array $data, $email) {
        $query = "UPDATE users SET ";

        foreach ($data as $key => $value) {
            $query .= "$key = :$key, ";
        }

        $query = rtrim($query, ', ');
        $query .= " WHERE email = :email";

        try {
            $stmt = $this->db->prepare($query);

            foreach ($data as $key => $value) {
                $stmt->bindValue(":$key", $value);
            }
            $stmt->bindValue(":email", $email);

            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            var_dump($e->getMessage());
            return false;
        }
    }

    // Delete a user by ID
    public function deleteUser($id)
    {
        $stmt = $this->db->prepare("DELETE FROM users WHERE user_id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->rowCount() > 0;
    }

    // Fetch user information by email or ID
    public function fetchUserInfo($emailOrId, $strict = false)
    {
        $query = "SELECT * FROM users WHERE email = :email OR user_id = :id";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':email', $emailOrId);
            $stmt->bindValue(':id', $emailOrId);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                return false;
            }

            if (! $strict) 
                {

                    unset($user['id']);
                    unset($user['password']);
                    unset($user['created_at']);
                    unset($user['date_last_modified']);

                }

            return $user;

        } catch (PDOException $e) {
            return false;
        }
    }

    public function fetchUserPasswod($emailOrId)
    {
        $query = "SELECT password FROM users WHERE email = :email OR user_id = :id";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':email', $emailOrId);
            $stmt->bindValue(':id', $emailOrId);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            return $user ? $user : false;
        } catch (PDOException $e) {
            return false;
        }
    }

    // Additional methods can be added here as needed
}
