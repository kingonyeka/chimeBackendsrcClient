<?php

namespace App\Controllers\Admins;

use App\Database\Database;
use App\Helpers\JsonResponder;
use App\Middleware\JwtMiddleware;
use App\Models\Admin;
use App\Models\AdminSession;
use Exception;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

class AdminJwtVerifier {

    private static $jwtSecret;
    private static $db;

    /**
     * AuthController constructor.
     * 
     * @param string $jwtSecret The JWT secret key
     */
    private static function init()
    {
        // var_dump($_ENV['JWT_SECRET']);
        self::$jwtSecret = $_ENV['JWT_SECRET'];
        self::$db = new Database();
    }


    public static function verify(Request $request, Response $response): bool | array | Response {
        self::init();

        // Get the raw JSON data from the request body
        $jsonBody = file_get_contents('php://input');

        // Decode the JSON data into an associative array
        $body = json_decode($jsonBody, true);

        // Get the Authorization header from the request
        $authHeader = $request->getHeaderLine('Authorization');

        // Check if the Authorization header is missing or not in the correct format
        if (!$authHeader || !preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return false;
        }

        // Extract the JWT token from the Authorization header
        $refreshToken = $matches[1];

        try {
            $jwtModel = new JwtMiddleware(self::$jwtSecret);

            // Decode the JWT token using the JWT library
            $decoded = $jwtModel->decodeToken($refreshToken);

            // Check if the decoded token contains an error property
            if (is_array($decoded) && isset($decoded['error']) && $decoded['error'] !== null) {
                // Check if the error is due to token expiration
                if ($decoded['error'] === 'token has expired') {
                    throw new ExpiredException();
                }
                

                // For other errors, return a general invalid token response
                return JsonResponder::generate($response, [
                    'code' => 401,
                    'message' => 'Invalid token',
                    'data' => null
                ], 401);
            }

            // Check if the decoded token contains an error
            if (isset($decoded->error)) {
                return false;
            }


            // Extract user ID from the decoded token
            $userId = $decoded->data->userId ?? $decoded->userId ?? null;

            if (!$userId) {
                return false;
            }

            $adminSessionsModel = new AdminSession(self::$db);
            $fetchUserSessionInfo = $adminSessionsModel->getAccessToken($userId, $refreshToken);


            if (!$fetchUserSessionInfo) {
                return false;
            }

            // Fetch user from the database
            $userModel = new Admin(self::$db);
            $user = $userModel->fetchAdminInfo($userId);

            if (!$user) {
                return false;
            }

            return $user;

        } catch (ExpiredException $e) {
            return JsonResponder::generate($response, [
                'code' => 401,
                'message' => 'token has expired',
                'data' => null
            ], 401);
        } catch (SignatureInvalidException $e) {
            return JsonResponder::generate($response, [
                'code' => 401,
                'message' => 'invalid token signature',
                'data' => null
            ], 401);
        } catch (Exception $e) {
            // Handle other exceptions
            return false;
        }
    }


}