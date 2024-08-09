<?php

namespace App\Models;

use App\Database\Database;
use PDO;
use PDOException;
use DI\Container;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class UserSession extends Database
{
    protected $table = 'user_sessions';
    protected $fillable = ['user_id', 'access_token', 'refresh_token', 'expires_at', 'last_refreshed_at'];

    protected $db;

    protected $logger;

    public function __construct(Database $db)
    {
        parent::__construct(); // Initialize the database connection
        $this->db = $db;

        // Create Container using PHP-DI
        $container = new Container();
        // Logger setup
        $logger = new Logger('app_logger');
        $logger->pushHandler(new StreamHandler(__DIR__ . '/../../logs/app.log', Logger::DEBUG));
        $container->set(Logger::class, $logger);

        $this->logger = $logger;

    }

    public function createSession(array $data)
    {
        try {
            $columns = implode(', ', array_keys($data));
            $values = ':' . implode(', :', array_keys($data));

            $query = "INSERT INTO user_sessions ($columns) VALUES ($values)
                    ON DUPLICATE KEY UPDATE ";

            foreach ($data as $key => $value) {
                $query .= "$key = :$key, ";
            }

            $query = rtrim($query, ', ');

            $stmt = $this->db->prepare($query);

            foreach ($data as $key => $value) {
                $stmt->bindValue(":$key", $value);
            }

            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            // Log or throw the exception for better error handling
            error_log("Error creating/updating user session: " . $e->getMessage());
            $this->logger->error($e->getMessage());

            return false;
        }
    }


    public function updateSession($userId, array $data)
    {
        try {
            $query = "UPDATE user_sessions SET ";
            $updates = [];

            foreach ($data as $key => $value) {
                $updates[] = "$key = :$key";
            }

            $query .= implode(', ', $updates);
            $query .= " WHERE user_id = :id";

            $stmt = $this->db->prepare($query);

            foreach ($data as $key => $value) {
                $stmt->bindValue(":$key", $value);
            }

            $stmt->bindValue(':id', $userId);
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            error_log("Error updating user session: " . $e->getMessage());
            return false;
        }
    }

    public function deleteSession($sessionId)
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM user_sessions WHERE access_token = :id");
            $stmt->execute(['id' => $sessionId]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error deleting user session: " . $e->getMessage());
            return false;
        }
    }

    // public function getSessionByUserId($userId)
    // {
    //     try {
    //         $stmt = $this->db->prepare("SELECT * FROM user_sessions WHERE user_id = :user_id");
    //         $stmt->execute(['user_id' => $userId]);
    //         return $stmt->fetch(PDO::FETCH_ASSOC);
    //     } catch (PDOException $e) {
    //         error_log("Error fetching user session by user ID: " . $e->getMessage());
    //         return false;
    //     }
    // }

    public function getSessionByUserId($userId)
    {

        $query = "SELECT * FROM {$this->table} WHERE user_id = :id";

        // try {
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':id', $userId);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                return false;
            }

            return $user;

        // } catch (PDOException $e) {
        //     return false;
        // }
    }

    public function getSessionByAccessToken($accessToken)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM user_sessions WHERE access_token = :access_token");
            $stmt->execute(['access_token' => $accessToken]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching user session by access token: " . $e->getMessage());
            return false;
        }
    }


    public function getToken($userId, $refreshToken, $accessToken) {
        $stmt = $this->db->prepare("SELECT * FROM " . $this->table . " WHERE user_id = ? AND refresh_token = ? AND access_token = ?");
        $stmt->execute([$userId, $refreshToken, $accessToken]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }



    public function getExpiredTokens($currentTime)
    {
        // Assuming a table structure with columns: tokenId, userId, token, expiresAt
        $sql = "SELECT * FROM user_tokens WHERE expires_at <= :currentTime";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':currentTime', $currentTime, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deleteToken($tokenId)
    {
        // Assuming a table structure with columns: tokenId, userId, token, expiresAt
        $sql = "DELETE FROM user_tokens WHERE tokenId = :tokenId";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':tokenId', $tokenId, PDO::PARAM_INT);
        $stmt->execute();
    }

}
