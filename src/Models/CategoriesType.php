<?php

namespace App\Models;

use App\Database\Database;
use PDOException;
use PDO;

/**
 * CategoriesType Model
 * 
 * This class handles operations related to the `categories_type` table.
 */
class CategoriesType extends Database
{
    protected $table = 'categories_type';
    protected $fillable = [
        'title',
        'description',
        'type_id',
        'cat_id',
        'created_at',
        'updated_at',
    ];
    
    protected $db;

    /**
     * Constructor to initialize database connection
     * 
     * @param Database $db Database connection instance
     */
    public function __construct(Database $db)
    {
        parent::__construct();
        $this->db = $db;
    }

    /**
     * Get total count of categories types
     * 
     * @return int Count of categories types
     */
    public function getCategoriesTypeCount()
    {
        $stmt = $this->db->query("SELECT COUNT(*) FROM {$this->table}");
        return $stmt->fetchColumn();
    }

    /**
     * Get categories types with pagination, sorting, and optional search
     * 
     * @param int $limit Number of records to retrieve
     * @param int $offset Offset for pagination
     * @param string $sortBy Column to sort by
     * @param string $orderDirection Sort direction (asc/desc)
     * @param string|null $search Optional search term
     * @return array Fetched categories types
     */
    public function getCategoriesTypes($limit = 10, $offset = 0, $sortBy = 'title', $orderDirection = 'asc', $search = null)
    {
        $forbiddenFields = ['password', 'cat_id', 'last_updated_at'];
        $validColumns = array_diff([
            'title',
            'description',
            'created_at',
            'type_id',
        ], $forbiddenFields);

        if (!in_array($sortBy, $validColumns)) {
            $sortBy = 'title';
        }

        $sql = "SELECT " . implode(", ", $validColumns) . " FROM {$this->table}";

        if ($search !== null) {
            $searchConditions = [];
            foreach ($validColumns as $column) {
                $searchConditions[] = "$column LIKE :search";
            }
            $sql .= " WHERE " . implode(" OR ", $searchConditions);
        }

        $sql .= " ORDER BY $sortBy $orderDirection";
        $sql .= " LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);

        if ($search !== null) {
            $stmt->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Create a new category type
     * 
     * @param array $data Data to insert
     * @return bool Operation status
     */
    public function createCategoryType(array $data)
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

    /**
     * Update a category type by cat_id
     * 
     * @param array $data Data to update
     * @param int $cat_id Category ID
     * @return bool Operation status
     */
    public function updateCategoryType(array $data, $cat_id) {
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

    /**
     * Delete a category type by cat_id
     * 
     * @param int $cat_id Category ID
     * @return bool Operation status
     */
    public function deleteCategoryType($cat_id)
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE cat_id = :cat_id");
        $stmt->execute(['cat_id' => $cat_id]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Fetch category type info by cat_id or title
     * 
     * @param mixed $catIdOrName Category ID or title
     * @return array|false Fetched category type info or false on failure
     */
    public function fetchCategoryTypeInfo($catIdOrName) {
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

    /**
     * Fetch all categories types
     * 
     * @return array Fetched categories types
     */
    public function fetchAllCategoriesTypes() {
        $query = "SELECT * FROM {$this->table}";

        try {
            $stmt = $this->db->query($query);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Fetch categories types by cat_id
     * 
     * @param int $cat_id Category ID
     * @return array Fetched categories types
     */
    public function fetchCategoriesTypesByCatId($cat_id) {
        $query = "SELECT * FROM {$this->table} WHERE cat_id = :cat_id";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':cat_id', $cat_id);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Fetch categories types by title
     * 
     * @param int $title Category ID
     * @return array Fetched categories types
     */
    public function fetchCategoriesTypesByTitle($title) {
        $query = "SELECT * FROM {$this->table} WHERE title = :title";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':title', $title);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Fetch category types by title
     * 
     * @param int $title Category ID
     * @return array Fetched categories types
     */
    public function fetchCategoryTypeByTitle($title) {
        $query = "SELECT * FROM {$this->table} WHERE title = :title";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':title', $title);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Fetch categories types by type_id
     * 
     * @param int $title Category ID
     * @return array Fetched categories types
     */
    public function fetchCategoriesTypesByTypeId($title) {
        $query = "SELECT * FROM {$this->table} WHERE type_id = :title";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':title', $title);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Search categories types by multiple fields
     * 
     * @param string $search Search term
     * @param array $fields Fields to search in
     * @return array Fetched categories types
     */
    public function searchCategoriesTypes($search, $fields = ['title', 'description', 'type_id']) {
        $forbiddenFields = ['password', 'last_updated_at'];
        $validFields = array_diff($fields, $forbiddenFields);
        $searchConditions = [];

        foreach ($validFields as $field) {
            $searchConditions[] = "$field LIKE :search";
        }

        $query = "SELECT * FROM {$this->table} WHERE " . implode(" OR ", $searchConditions);

        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }
}
