<?php

namespace App\Models;

use App\Database\Database;
use PDO;
use PDOException;

/**
 * Class Payments
 *
 * This class handles CRUD operations for payments.
 *
 * @package App\Models
 * @author NFORSHIFU234
 * @developer Shifu-Nfor Nyuiring-yoh Rhagninyui
 */
class Payments extends Database
{
    protected $table = 'payments';
    protected $fillable = [
        'payment_id',
        'amount',
        'currency',
        'paid_at',
        'payment_provider',
        'payment_channel',
        'authorization_code',
        'card_type',
        'bank',
        'card_last4',
        'created_at',
        'user_id',
        'admin_id' // Added admin_id to the fillable fields if it exists in your database schema
    ];
    
    protected $db;

    /**
     * Payments constructor.
     *
     * @param Database $db
     */
    public function __construct(Database $db)
    {
        parent::__construct(); // Initialize the database connection
        $this->db = $db;
    }

    /**
     * Get the total count of payments.
     *
     * @return int
     */
    public function getPaymentsCount()
    {
        $stmt = $this->db->query("SELECT COUNT(*) FROM {$this->table}");
        return $stmt->fetchColumn();
    }

    /**
     * Get the total amount of all payments.
     *
     * @return float|false
     */
    public function getTotalAmount()
    {
        $query = "SELECT SUM(amount) AS total_amount FROM {$this->table}";

        try {
            $stmt = $this->db->query($query);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total_amount'];
        } catch (PDOException $e) {
            var_dump($e->getMessage());
            return false;
        }
    }

public function getTotalAmountNGN()
{
    return $this->getTotalAmountByCurrency('NGN');
}

public function getTotalAmountNotNGN()
{
    return $this->getTotalAmountByCurrency('NOT NGN');
}

private function getTotalAmountByCurrency($currencyCondition)
{
    $query = "SELECT SUM(amount) AS total_amount FROM {$this->table} WHERE ";

    // Apply currency condition
    if ($currencyCondition == 'NOT NGN') {
        $query .= "currency != :currency";
    } else {
        $query .= "currency = :currency";
    }

    try {
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':currency', $currencyCondition == 'NOT NGN' ? 'NGN' : $currencyCondition, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total_amount'];
    } catch (PDOException $e) {
        var_dump($e->getMessage());
        return false;
    }
}

    /**
     * Get a list of payments with optional sorting, pagination, and search.
     *
     * @param int $limit
     * @param int $offset
     * @param string $sortBy
     * @param string $orderDirection
     * @param string|null $search
     * @return array|false
     */
    public function getPayments($limit = 10, $offset = 0, $sortBy = 'created_at', $orderDirection = 'desc', $search = null)
    {
        $validColumns = [
            'payment_id',
            'amount',
            'currency',
            'paid_at',
            'payment_provider',
            'payment_channel',
            'authorization_code',
            'card_type',
            'bank',
            'card_last4',
            'created_at',
            'user_id',
            'admin_id'
        ];

        // Ensure sortBy is a valid column
        if (!in_array($sortBy, $validColumns)) {
            $sortBy = 'created_at';
        }

        // Start building the query
        $sql = "SELECT " . implode(", ", $validColumns) . " FROM {$this->table}";

        // Apply search condition if provided
        if ($search !== null) {
            $searchConditions = [];
            foreach ($validColumns as $column) {
                $searchConditions = "$column LIKE :search";
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

    /**
     * Get the sum of all amounts for a given user ID.
     *
     * @param string $userId
     * @return float|false
     */
    public function getTotalAmountByUserId($userId)
    {
        $query = "SELECT SUM(amount) as total_amount FROM {$this->table} WHERE user_id = :user_id";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':user_id', $userId);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result && isset($result['total_amount'])) {
                return (float)$result['total_amount'];
            }

            return false;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Get payment information by payment_id, user_id, or admin_id.
     *
     * @param string $id
     * @return array|false
     */
    public function getPaymentInfo($id)
    {
        $query = "SELECT * FROM {$this->table} WHERE payment_id = :id OR user_id = :id OR admin_id = :id";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':id', $id);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result) {
                return $result;
            }

            return false;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Update a record in the payments table.
     *
     * This function accepts an associative array of column-value pairs and updates the corresponding record
     * in the database. It binds the values to the prepared statement to prevent SQL injection.
     *
     * @param string $paymentId The ID of the payment record to update.
     * @param array $data Associative array of column-value pairs to update.
     * @return bool True on success, false on failure.
     */
    public function updatePayment( array $data, $paymentId)
    {
        // Start building the SQL query
        $query = "UPDATE {$this->table} SET ";

        // Append column placeholders for binding
        foreach ($data as $column => $value) {
            $query .= "$column = :$column, ";
        }

        // Remove the trailing comma and space
        $query = rtrim($query, ', ');

        // Append the WHERE clause to specify the record to update
        $query .= " WHERE payment_id = :payment_id";

        try {
            // Prepare the SQL statement
            $stmt = $this->db->prepare($query);

            // Bind the column values
            foreach ($data as $column => $value) {
                $stmt->bindValue(":$column", $value);
            }

            // Bind the payment ID
            $stmt->bindValue(":payment_id", $paymentId);

            // Execute the query
            $stmt->execute();

            // Return true if the update was successful
            return true;
        } catch (PDOException $e) {
            // Return false if there was an error
            return false;
        }
    }

    /**
     * Update all occurrences of the specified user ID.
     *
     * This function sets the user_id column to NULL and the admin_id column to the specified user ID
     * for all records where the user_id matches the given user ID.
     *
     * @param string $userId The user ID to update.
     * @return bool True on success, false on failure.
     */
    public function transferUserToAdmin($userId)
    {
        // Start building the SQL query
        $query = "UPDATE {$this->table} SET user_id = NULL, admin_id = :admin_id WHERE user_id = :user_id";

        try {
            // Prepare the SQL statement
            $stmt = $this->db->prepare($query);

            // Bind the user ID for both columns
            $stmt->bindValue(':admin_id', $userId);
            $stmt->bindValue(':user_id', $userId);

            // Execute the query
            $stmt->execute();

            // Return true if the update was successful
            return true;
        } catch (PDOException $e) {
            // Return false if there was an error
            return false;
        }
    }


    // Additional methods can be added here as needed
    public function getPaymentsWithCurrencyNGN($limit = 10, $offset = 0, $sortBy = 'created_at', $orderDirection = 'desc', $search = null)
{
    return $this->getPaymentsByCurrency('NGN', $limit, $offset, $sortBy, $orderDirection, $search);
}

public function getPaymentsWithCurrencyNotNGN($limit = 10, $offset = 0, $sortBy = 'created_at', $orderDirection = 'desc', $search = null)
{
    return $this->getPaymentsByCurrency('NOT NGN', $limit, $offset, $sortBy, $orderDirection, $search);
}

    private function getPaymentsByCurrency($currencyCondition, $limit = 10, $offset = 0, $sortBy = 'created_at', $orderDirection = 'desc', $search = null)
{
    $validColumns = [
        'payment_id',
        'amount',
        'currency',
        'paid_at',
        'payment_provider',
        'payment_channel',
        'authorization_code',
        'card_type',
        'bank',
        'card_last4',
        'created_at',
        'user_id',
        'admin_id'
    ];

    // Ensure sortBy is a valid column
    if (!in_array($sortBy, $validColumns)) {
        $sortBy = 'created_at';
    }

    // Start building the query
    $sql = "SELECT " . implode(", ", $validColumns) . " FROM {$this->table} WHERE ";

    // Apply currency condition
    if ($currencyCondition == 'NOT NGN') {
        $sql .= "currency != :currency";
    } else {
        $sql .= "currency = :currency";
    }

    // Apply search condition if provided
    if ($search !== null) {
        $searchConditions = [];
        foreach ($validColumns as $column) {
            $searchConditions[] = "$column LIKE :search";
        }
        $sql .= " AND (" . implode(" OR ", $searchConditions) . ")";
    }

    // Apply sorting, limit, and offset
    $sql .= " ORDER BY $sortBy $orderDirection";
    $sql .= " LIMIT :limit OFFSET :offset";

    // Prepare and execute the query
    $stmt = $this->db->prepare($sql);

    // Bind parameters
    $stmt->bindValue(':currency', $currencyCondition == 'NOT NGN' ? 'NGN' : $currencyCondition, PDO::PARAM_STR);
    if ($search !== null) {
        $stmt->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
    }
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);

    $stmt->execute();

    // Fetch and return results
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

}
