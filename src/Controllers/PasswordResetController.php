<?php

// PasswordResetController.php
namespace App\Controllers;

use App\Controllers\EmailSender;
use App\Database\Database;
use App\Helpers\JsonResponder;
use App\Middleware\JwtMiddleware;
use App\Models\PasswordReset;
use App\Models\User;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class PasswordResetController
{

    private $jwtSecret;
    private $db;

    public function __construct($jwtSecret)
    {
        $this->jwtSecret = $jwtSecret;
        $this->db = new Database();
    }

    public function requestReset(Request $request, Response $response, $args)
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Expose-Headers: ');
        header('Access-Control-Allow-Headers: *');
        $jsonBody = file_get_contents('php://input');
        $parsedBody = json_decode($jsonBody, true);
        $email = $parsedBody['email'] ?? null;
    
        if (!$email) {
            return JsonResponder::generate($response, ['code' => 401, 'message' => 'email is required'], 401);
        }
    
        $userModel = new User($this->db);
        $user = $userModel->fetchUserInfo($email);
    
        if (!$user) {
            return JsonResponder::generate($response, ['code' => 401, 'message' => 'email not found'], 401);
        }
    
        $jwtModel = new JwtMiddleware($this->jwtSecret);
    
        $expiresAt = date('Y-m-d H:i:s', strtotime('+15 minutes')); // Changed to 15 minutes
    
        $token = $jwtModel->generateToken([
            'expiresAt' => $expiresAt
        ], 900); // 15 minutes in seconds
    
        $passwordResetModel = new PasswordReset($this->db);
        $passwordResetModel->createResetToken($email, $token, $expiresAt);
    
        $resetLink = $_ENV['APPLICATION_FRONTEND_URL'] . "/reset-password?token=" . urlencode($token);

        $email_path = getcwd() . DIRECTORY_SEPARATOR . '../src/Assets/email/templates/email_reset_password.html';
        $htmlBody = file_get_contents($email_path);

        // Replace placeholder in HTML with the actual URL
        $htmlBody = str_replace('{{reset_url}}', $resetLink, $htmlBody);

        $emailSender = new EmailSender();
        $emailSender->sendHTMLEmail($email, 'Password Reset', $htmlBody);
    
        return JsonResponder::generate($response, ['code' => 200, 'message' => 'password reset email sent']);
    }


    // PasswordResetController.php

    public function verifyToken(Request $request, Response $response, $args)
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Expose-Headers: ');
        header('Access-Control-Allow-Headers: *');
        $token = $request->getQueryParams()['token'] ?? null;

        if (!$token) {
            return JsonResponder::generate($response, ['code' => 401, 'message' => 'token is required'], 401);
        }

        $database = new Database();
        $passwordResetModel = new PasswordReset($database);
        $resetToken = $passwordResetModel->fetchResetToken($token);

        if (!$resetToken || strtotime($resetToken['expires_at']) < time()) {
            return JsonResponder::generate($response, ['code' => 401, 'message' => 'Invalid or expired token'], 401);
        }

        return JsonResponder::generate($response, ['code' => 200, 'message' => 'token is valid']);
    }

    public function resetPassword(Request $request, Response $response, $args)
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Expose-Headers: ');
        header('Access-Control-Allow-Headers: *');
        $jsonBody = file_get_contents('php://input');
        $parsedBody = json_decode($jsonBody, true);
        $token = $parsedBody['token'] ?? null;
        $newPassword = $parsedBody['new_password'] ?? null;
        $confirmPassword = $parsedBody['confirm_password'] ?? null;
    
        if (!$token || !$newPassword || !$confirmPassword) {
            return JsonResponder::generate($response, ['code' => 401, 'message' => 'all fields are required'], 401);
        }
    
        if ($newPassword !== $confirmPassword) {
            return JsonResponder::generate($response, ['code' => 401, 'message' => 'passwords do not match'], 401);
        }
    
        $database = new Database();
        $passwordResetModel = new PasswordReset($database);
        $resetToken = $passwordResetModel->fetchResetToken($token);
    
        if (!$resetToken || strtotime($resetToken['expires_at']) < time()) {
            return JsonResponder::generate($response, ['code' => 401, 'message' => 'invalid or expired token'], 401);
        }
    
        $email = $resetToken['email'];
        $userModel = new User($database);
        $userModel->updateUserUsingEmail([
            'password' => password_hash($newPassword, PASSWORD_BCRYPT)
        ], $email);
        $passwordResetModel->invalidateResetToken($token);
    
        // Send password reset success email
        $email_path = getcwd() . DIRECTORY_SEPARATOR . '../src/Assets/email/templates/email_reset_password_success.html';
        $htmlBody = file_get_contents($email_path);
    
        $emailSender = new EmailSender();
        $emailSender->sendHTMLEmail($email, 'Password Reset Successful', $htmlBody);
    
        return JsonResponder::generate($response, ['code' => 200, 'message' => 'password has been reset']);
    }
    


}
