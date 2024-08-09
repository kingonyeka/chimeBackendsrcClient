<?php

namespace App\Models;

use PDO;

class PasswordReset
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function createResetToken($email, $token, $expiresAt)
    {
        $sql = "INSERT INTO password_resets (email, token, expires_at) VALUES (:email, :token, :expires_at)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':token', $token);
        $stmt->bindParam(':expires_at', $expiresAt);
        return $stmt->execute();
    }

    public function getResetTokenByEmail($email)
    {
        $sql = "SELECT * FROM password_resets WHERE email = :email AND expires_at > NOW() ORDER BY id DESC LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function fetchResetToken($token)
    {
        $sql = "SELECT * FROM password_resets WHERE token = :token AND expires_at > NOW() ORDER BY id DESC LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':token', $token);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function invalidateResetToken($token)
    {
        $sql = "DELETE FROM password_resets WHERE token = :token";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':token', $token);
        return $stmt->execute();
    }

    public function deleteResetToken($id)
    {
        $sql = "DELETE FROM password_resets WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}
