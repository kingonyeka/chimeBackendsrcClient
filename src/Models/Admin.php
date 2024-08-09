<?php

namespace App\Models;

use App\Database\Database;
use PDOException;
use PDO;
use Monolog\Logger;

class Admin extends Database
{
    protected $table = 'admins';
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

    public function getAdminsCount()
    {
        $stmt = $this->db->query("SELECT COUNT(*) FROM {$this->table}");
        return $stmt->fetchColumn();
    }

    public function getAdmin($limit = 10, $offset = 0, $sortBy = 'title', $orderDirection = 'asc', $search = null)
    {
        // Define valid and forbidden fields
        $forbiddenFields = ['password',];
        $validColumns = array_diff([
            'email',
            'first_name',
            'last_name',
            'middle_name',
            'address',
            'user_id',
            'status',
            'joined_telegram',
            'last_logged_in',
            'verified_email',
            'date_last_modified',
            'role_name'
        ]);

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
            $sortBy = 'title';
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
    public function createAdmin(array $data)
    {
        $columns = implode(', ', array_keys($data));
        $values = ':' . implode(', :', array_keys($data));
    
        $query = "INSERT INTO {$this->table} ($columns) VALUES ($values)";
    
        try {
            $stmt = $this->db->prepare($query);
    
            foreach ($data as $key => $value) {
                $stmt->bindValue(":$key", $value);
            }
    
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            var_dump($e->getMessage());
            return false;
        }
    }
    

    // Update user based on email and data array
    public function updateAdmin(array $data, $user_id) {
        $query = "UPDATE {$this->table} SET ";

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
    public function updateAdminUsingEmail(array $data, $email) {
        $query = "UPDATE {$this->table} SET ";

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
    public function deleteAdmin($id)
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE user_id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->rowCount() > 0;
    }

    // Fetch user information by email or ID
    public function fetchAdminInfo($emailOrId, $strict = false)
    {
        $query = "SELECT * FROM {$this->table} WHERE email = :email OR user_id = :id";

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

    public function fetchSuperAdminInfo($strict = false)
    {
        $query = "SELECT * FROM {$this->table} WHERE role_name = 'super-admin' ";

        try {
            $stmt = $this->db->prepare($query);
            // $stmt->bindValue(':email', $emailOrId);
            // $stmt->bindValue(':id', $emailOrId);
            $stmt->execute();
            $superAdmin = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$superAdmin) {
                return false;
            }

            if (! $strict) 
                {

                    unset($superAdmin['id']);
                    unset($superAdmin['password']);
                    unset($superAdmin['created_at']);
                    unset($superAdmin['date_last_modified']);

                }

            return $superAdmin;

        } catch (PDOException $e) {
            return false;
        }
    }

    public function fetchUserPasswod($emailOrId)
    {
        $query = "SELECT password FROM {$this->table} WHERE email = :email OR id = :id";

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
