<?php

namespace App\Controllers\Admins;

use App\Controllers\EmailSender;
use App\Database\Database;
use App\Helpers\JsonResponder;
use App\Middleware\JwtMiddleware;
use App\Models\Admin;
use App\Models\AdminSession;
use App\Models\User;
use App\Models\UserSession;
use DateTime;
use Illuminate\Support\Facades\Date;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

class AdminAuthController
{

    private $jwtSecret;
    private $db;

    /**
     * AuthController constructor.
     * 
     * @param string $jwtSecret The JWT secret key
     */
    public function __construct($jwtSecret)
    {
        $this->jwtSecret = $_ENV['JWT_SECRET'];
        $this->db = new Database();
    }

    /**
     * Handle admin login.
     *
     * @param Request $request The HTTP request object.
     * @param Response $response The HTTP response object.
     * @param array $args Route arguments.
     * @return Response HTTP response with JSON data.
     */
    public function login(Request $request, Response $response, $args)
    {

        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Expose-Headers: ');
        header('Access-Control-Allow-Headers: *');

        // Create a Database instance
        $database = $this->db;

        // Get the raw JSON data from the request body
        $jsonBody = file_get_contents('php://input');

        // Decode the JSON data into an associative array
        $parsedBody = json_decode($jsonBody, true);

        // Check if email and password are present in the parsed body
        $email = $parsedBody['email'] ?? null;
        $password = $parsedBody['password'] ?? null;

        // If email or password is missing, return an error response
        if (!$email || !$password) {
            $data = ['code' => 401, 'message' => 'Email and password are required', 'data' => null];
            return JsonResponder::generate($response, $data, 401);
        }

        // Fetch admin information based on the provided email
        $adminModel = new Admin($database);
        $admin = $adminModel->fetchAdminInfo($email, true);

        // If admin not found or password is incorrect, return an error response
        if (!$admin || !password_verify($password, $admin['password'])) {
            $data = ['code' => 401, 'message' => 'invalid email or password', 'data' => null];
            return JsonResponder::generate($response, $data, 401);
        }

        // Initialize the JWT middleware with the secret key
        $jwtMiddleware = new JwtMiddleware($this->jwtSecret);

        // Generate JWT
        $jwtPayload = [
            'userId' => $admin['user_id'],
            'email' => $admin['email']
        ];
        $jwt = $jwtMiddleware->generateToken($jwtPayload, 15780096);

        // Generate refresh token
        $refreshToken = $jwtMiddleware->generateRefreshToken($admin['user_id']); // Generate a random refresh token

        // Store the refresh token securely in the database
        $adminSessionModel = new AdminSession($database);

        // Calculate expiration time based on environment variable
        $expirySeconds = intval($_ENV['REFRESH_TOKEN_EXPIRY']);
        $expiryTime = date('Y-m-d H:i:s', time() + ($expirySeconds * $expirySeconds));

        // Prepare session data for the database
        $sessionDbData = [
            'user_id' => $admin['user_id'],
            'access_token' => $jwt,
            'refresh_token' => $refreshToken,
            'expires_at' => $expiryTime, // Set expiration time based on calculated value
            'last_refreshed_at' => date('Y-m-d H:i:s'), // Initial refresh time
            'updated_at' => date('Y-m-d H:i:s'), // Initial refresh time
        ];

        // Check if a session already exists for the user
        if ($adminSessionModel->getSessionByUserId($admin['user_id']) !== false) {
            // Update existing session
            $storedSession = $adminSessionModel->updateSession($admin['user_id'], $sessionDbData);
        } else {
            // Create a new session
            $sessionDbData['created_at'] = date('Y-m-d H:i:s');
            $storedSession = $adminSessionModel->createSession($sessionDbData);
        }

        // Update the admin's last logged-in time
        $userLoggedInUpdate = $adminModel->updateAdmin([
            'last_logged_in' => date('Y-m-d H:i:s')
        ], $admin['user_id']);

        // Prepare the response data
        $data = [
            'code' => 200,
            'message' => 'login successful',
            'data' => [
                'jwt' => $jwt,
                'refreshToken' => $refreshToken, // Include refresh token if needed
                'role' => $admin['role_name']
            ]
        ];

        // Check if both session storage and login time update were successful
        if ($storedSession === true && $userLoggedInUpdate === true) {
            return JsonResponder::generate($response, $data);
        }

        // Return an error response if something went wrong
        return JsonResponder::generate($response, [
            'code' => 500,
            'message' => 'an error occurred',
            'data' => null
        ], 500);
    }


    /**
     * Refreshes the JWT and refresh token for the user.
     * 
     * @param Request $request  The HTTP request object.
     * @param Response $response The HTTP response object.
     * @return Response The HTTP response object with the result of the operation.
     */
    public function refresh(Request $request, Response $response)
    {

        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Expose-Headers: ');
        header('Access-Control-Allow-Headers: *');

        // Decode the JSON data into an associative array
        $body = $request->getParsedBody();

        // Extract the old token from the request body
        $oldToken = $body['token'] ?? null;

        // Check if the old token is not provided
        if (!$oldToken) {
            return JsonResponder::generate($response, [
                'code' => 401,
                'message' => 'token is required',
                'data' => null
            ], 400);
        }

        // Get the Authorization header from the request
        $authHeader = $request->getHeaderLine('Authorization');

        // Check if the Authorization header is missing or not in the correct format
        if (!$authHeader || !preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return false;
        }

        // Extract the JWT token from the Authorization header
        $refreshToken = $matches[1];

        // Initialize the JWT middleware with the secret key
        $jwtMiddleware = new JwtMiddleware($this->jwtSecret);
        $decodedRefreshToken = $jwtMiddleware->decodeToken($refreshToken);

        // Check if the refresh token is invalid or expired
        if (is_array($decodedRefreshToken) && isset($decodedRefreshToken['error'])) {
            return JsonResponder::generate($response, [
                'code' => 401,
                'message' => 'access token is invalid or expired token',
                'data' => null
            ], 401);
        }

        // Decode the old token
        $decodeOldToken = $jwtMiddleware->decodeToken($oldToken);

        // Check if the old token is not expired
        if (!is_array($decodeOldToken)) {
            return JsonResponder::generate($response, [
                'code' => 401,
                'message' => 'token has not expired',
                'data' => null
            ], 401);
        }

        // Check if the decoded refresh token has an error other than 'token has expired'
        if (is_array($decodedRefreshToken) && isset($decodedRefreshToken['error']) && $decodedRefreshToken['error'] !== 'token has expired') {
            return JsonResponder::generate($response, [
                'code' => 401,
                'message' => $decodedRefreshToken['error'],
                'data' => null
            ], 401);
        }

        // Get the user ID from the decoded refresh token
        $adminId = $decodedRefreshToken->userId ?? null;

        // Validate the refresh token with the database
        $adminSessionsModel = new AdminSession($this->db);
        $storedToken = $adminSessionsModel->getToken($adminId, $oldToken, $refreshToken);

        // Check if the stored token is not found
        if (!$storedToken) {
            return JsonResponder::generate($response, [
                'code' => 401,
                'message' => 'invalid token',
                'data' => null
            ], 401);
        }

        // Fetch admin information
        $adminModel = new Admin($this->db);
        $admin = $adminModel->fetchAdminInfo($adminId, true);

        // Check if the admin information is not found
        if (!$admin) {
            return JsonResponder::generate($response, [
                'code' => 401,
                'message' => 'user not found',
                'data' => null
            ], 401);
        }

        $adminId = $admin['user_id'];
        $jwtPayload = ['userId' => $adminId, 'email' => $admin['email']];

        // Generate new JWT and refresh token
        $jwt = $jwtMiddleware->generateToken($jwtPayload);
        $newRefreshToken = $jwtMiddleware->generateRefreshToken($adminId);

        // Calculate expiration time based on environment variable
        $expirySeconds = intval($_ENV['REFRESH_TOKEN_EXPIRY']);
        $expiryTime = date('Y-m-d H:i:s', time() + ($expirySeconds * $expirySeconds));

        // Update the session in the database
        $adminSessionsModel->updateSession($adminId, [
            'access_token' => $jwt,
            'refresh_token' => $newRefreshToken,
            'expires_at' => $expiryTime, // Set expiration time based on calculated value
            'last_refreshed_at' => date('Y-m-d H:i:s'), // Initial refresh time
        ]);

        // Return the response with the new tokens
        return JsonResponder::generate($response, [
            'code' => 201,
            'message' => 'token refreshed successfully',
            'data' => [
                'jwt' => $jwt,
                'refreshToken' => $newRefreshToken
            ]
        ], 201);
    }




}
