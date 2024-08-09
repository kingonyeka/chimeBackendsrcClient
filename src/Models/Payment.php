<?php

namespace App\Models;

use App\Database\Database;
use PDOException;
use PDO;

class Payment 
{
    protected $db;
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
        'country_code',
        'card_last4',
        'created_at'
    ];

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function createPayment(array $data)
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
            return 'PDOException: ' . $e->getMessage();
            return false;
        }
    }

    public function updatePayment(array $data, $paymentId)
    {
        $query = "UPDATE {$this->table} SET ";

        foreach ($data as $key => $value) {
            $query .= "$key = :$key, ";
        }

        $query = rtrim($query, ', ');
        $query .= " WHERE payment_id = :payment_id";

        try {
            $stmt = $this->db->prepare($query);

            foreach ($data as $key => $value) {
                $stmt->bindValue(":$key", $value);
            }
            $stmt->bindValue(":payment_id", $paymentId);

            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function deletePayment($paymentId)
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE payment_id = :payment_id");
        $stmt->execute(['payment_id' => $paymentId]);
        return $stmt->rowCount() > 0;
    }

    // Additional methods can be added here as needed
}
