<?php

namespace App\Models;

use App\Database\Database;
use PDOException;
use PDO;

class Cart extends Database
{
    protected $table = 'cart';
    protected $fillable = [
        'user_id',
        'products'
    ];
    protected $hidden = [];
    protected $db;

    public function __construct(Database $db)
    {
        parent::__construct(); // Initialize the database connection
        $this->db = $db;
    }

    // Get the count of carts
    public function getCartsCount()
    {
        $stmt = $this->db->query("SELECT COUNT(*) FROM {$this->table}");
        return $stmt->fetchColumn();
    }

    // Get a list of carts with pagination and optional sorting and search
    public function getCarts($limit = 10, $offset = 0, $sortBy = 'id', $orderDirection = 'asc', $search = null)
    {
        $validColumns = ['id', 'user_id', 'products'];
        
        // Ensure sortBy is a valid column
        if (!in_array($sortBy, $validColumns)) {
            $sortBy = 'id';
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

    // Create a new cart
    public function createCart(array $data)
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

    // Update an existing cart
    public function updateCart(array $data, $id)
    {
        $query = "UPDATE {$this->table} SET ";

        foreach ($data as $key => $value) {
            $query .= "$key = :$key, ";
        }

        $query = rtrim($query, ', ');
        $query .= " WHERE user_id = :id";

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

    // Delete a cart by ID
    public function deleteCart($id)
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE user_id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->rowCount() > 0;
    }

    // Fetch cart information by ID
    public function fetchCartInfo($id)
    {
        $query = "SELECT * FROM {$this->table} WHERE id = :id";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':id', $id);
            $stmt->execute();
            $cart = $stmt->fetch(PDO::FETCH_ASSOC);

            return $cart ? $cart : false;
        } catch (PDOException $e) {
            return false;
        }
    }

    // Fetch cart information by user ID
    public function fetchCartByUserId($user_id)
    {
        $query = "SELECT * FROM {$this->table} WHERE user_id = :user_id";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':user_id', $user_id);
            $stmt->execute();
            $cart = $stmt->fetch(PDO::FETCH_ASSOC);

            return $cart ? $cart : false;
        } catch (PDOException $e) {
            return false;
        }
    }

    // Fetch cart information by user ID
    public function fetchCartByIdAndUserId($user_id)
    {
        $query = "SELECT * FROM {$this->table} WHERE user_id = :user_id AND ";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':user_id', $user_id);
            $stmt->execute();
            $cart = $stmt->fetch(PDO::FETCH_ASSOC);

            return $cart ? $cart : false;
        } catch (PDOException $e) {
            return false;
        }
    }

    // Additional methods can be added here as needed
}

