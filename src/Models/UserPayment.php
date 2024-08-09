<?php

namespace App\Models;

use App\Database\Database;
use PDO;
use PDOException;

/**
 * Class UserPayment
 *
 * This class handles CRUD operations for user payments.
 *
 * @package App\Models
 * @author NFORSHIFU234
 * @developer Shifu-Nfor Nyuiring-yoh Rhagninyui
 */
class UserPayment
{
    protected $db;
    protected $table = 'user_payment';
    protected $fillable = [
        'user_id',
        'payment_id',
        'type'
    ];

    /**
     * UserPayment constructor.
     *
     * @param Database $db
     */
    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /**
     * Create a new user payment record.
     *
     * @param string $userId
     * @param string $paymentId
     * @param string $type
     * @return bool
     */
    public function createUserPayment($userId, $paymentId, $type)
    {
        $query = "INSERT INTO {$this->table} (user_id, payment_id, type) VALUES (:user_id, :payment_id, :type)";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':user_id', $userId);
            $stmt->bindValue(':payment_id', $paymentId);
            $stmt->bindValue(':type', $type);
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Fetch user payment information by user ID or payment ID.
     *
     * @param string $userIdOrPaymentId
     * @return array|false
     */
    public function fetchUserPaymentInfo($userIdOrPaymentId) {
        
        $query = "SELECT * FROM {$this->table} WHERE user_id = :user_id OR payment_id = :payment_id";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':user_id', $userIdOrPaymentId);
            $stmt->bindValue(':payment_id', $userIdOrPaymentId);
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
     * Delete a user payment record by user ID and payment ID.
     *
     * @param string $userId
     * @param string $paymentId
     * @return bool
     */
    public function deleteUserPayment($userId, $paymentId)
    {
        $query = "DELETE FROM {$this->table} WHERE user_id = :user_id AND payment_id = :payment_id";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':user_id', $userId);
            $stmt->bindValue(':payment_id', $paymentId);
            $stmt->execute();
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            return false;
        }
    }

    // Additional methods can be added here as needed
}
