<?php

namespace App\Models;

use App\Database\Database;
use PDOException;
use PDO;
use Monolog\Logger;

class Categories extends Database
{
    protected $table = 'categories';
    protected $fillable = [

        'title',
        'description',
        'cat_id',
        'created_at',
        'last_updated_at',
    ];
    
    protected $db;

    public function __construct(Database $db)
    {
        parent::__construct(); // Initialize the database connection
        $this->db = $db;
    }

    public function getCategoriesCount()
    {
        $stmt = $this->db->query("SELECT COUNT(*) FROM {$this->table}");
        return $stmt->fetchColumn();
    }

    public function getCategories($limit = 10, $offset = 0, $sortBy = 'title', $orderDirection = 'asc', $search = null)
    {
        // Define valid and forbidden fields
        $forbiddenFields = ['password', 'last_updated_at'];
        $validColumns = array_diff([
            'title',
            'cat_id',
            'description',
            'created_at',
        ], $forbiddenFields);

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
    public function createCategory(array $data)
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
            return false;
        }

    }

    // Update user based on email and data array
    public function updateCategory(array $data, $cat_id) {
        $query = "UPDATE {$this->table} SET ";

        foreach ($data as $key => $value) {
            $query .= "$key = :$key, ";
        }

        $query = rtrim($query, ', ');
        $query .= " WHERE cat_id = :cat_id";

        try {
            $stmt = $this->db->prepare($query);

            foreach ($data as $key => $value) {
                $stmt->bindValue(":$key", $value);
            }
            $stmt->bindValue(":cat_id", $cat_id);

            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            var_dump($e->getMessage());
            return false;
        }
    }

    // Delete a user by ID
    public function deleteCategory($cat_id)
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE cat_id = :cat_id");
        $stmt->execute(['cat_id' => $cat_id]);
        return $stmt->rowCount() > 0;
    }

    public function fetchCategoryInfo($catIdOrName) {
        
        $query = "SELECT * FROM {$this->table} WHERE cat_id = :cat_id OR title = :title";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':cat_id', $catIdOrName);
            $stmt->bindValue(':title', $catIdOrName);
            $stmt->execute();
            $category = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$category) {
                return false;
            }

            return $category;

        } catch (PDOException $e) {
            return false;
        }

    }

    // Additional methods can be added here as needed
}
