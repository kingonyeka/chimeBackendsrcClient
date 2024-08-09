<?php

namespace App\Controllers;

use App\Controllers\EmailSender as ControllersEmailSender;
use App\Database\Database;
use App\Helpers\JsonResponder;
use App\Helpers\RandomStringGenerator as HelpersRandomStringGenerator;
use App\Helpers\URLEncode;
use App\Middleware\JwtMiddleware;
use App\Models\Courses;
use App\Models\Robots;
use App\Models\User;
use App\Models\UserCourse;
use App\Models\UserRobot;
use App\Models\UserSession;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Firebase\JWT\JWT;
use Firebase\JWT\SignatureInvalidException;
use RandomStringGenerator;

class AuthController
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
        $this->jwtSecret = $jwtSecret;
        $this->db = new Database();
    }

    /**
     * Send a JSON response with status code.
     *
     * @param Response $response The HTTP response
     * @param mixed $data The data to be sent in the response
     * @param int $statusCode The HTTP status code (default 200)
     * @return Response The HTTP response
     */
    private function jsonResponse(Response $response, $data, $statusCode = 200)
    {
        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json')->withStatus($statusCode);
    }

    /**
     * Verify JWT token with database.
     * 
     * @param Request $request The HTTP request
     * @param Response $response The HTTP response
     * @return bool True if the token is valid and associated user exists, false otherwise
     */
    private function verifyTokenWithDB(Request $request, Response $response): bool|Response
    {
        // Get the Authorization header from the request
        $authHeader = $request->getHeaderLine('Authorization');

        // Check if the Authorization header is missing or not in the correct format
        if (!$authHeader || !preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return false;
        }

        // Extract the JWT token from the Authorization header
        $token = $matches[1];

        try {
            $jwtModel = new JwtMiddleware($this->jwtSecret);

            // Decode the JWT token using the JWT library
            $decoded = $jwtModel->decodeToken($token);

            // Check if the decoded token contains an error
            if (isset($decoded->error)) {
                return false;
            }

            if (is_array($decoded) && isset($decoded['error'])) {
                return JsonResponder::generate($response, [
                    'code' => 401,
                    'message' => $decoded['error'],
                    'data' => null
                ], 401);
            }


            // Extract user ID from the decoded token
            $userId = $decoded->data->userId ?? null;


            if (!$userId) {
                return false;
            }

            // Fetch user from the database
            $userModel = new User($this->db);
            $user = $userModel->fetchUserInfo($userId);

            if (!$user) {
                return false;
            }

            // If everything is fine, return true
            return true;

        } catch (SignatureInvalidException $e) {
            // Handle invalid signature
            return false;
        } catch (\Exception $e) {
            // Handle other exceptions
            return false;
        }
    }



    /**
     * Decode and verify JWT token.
     * 
     * @param Request $request The HTTP request
     * @param Response $response The HTTP response
     * @param mixed $args Additional route arguments
     * @return Response The HTTP response
     */
    public function decodeToken(Request $request, Response $response, $args): Response
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Expose-Headers: ');
        header('Access-Control-Allow-Headers: *');

        // Get the raw JSON data from the request body
        $jsonBody = file_get_contents('php://input');
        // Decode the JSON data into an associative array
        $parsedBody = json_decode($jsonBody, true);

        // Extract token from the parsed body
        $token = $parsedBody['token'] ?? null;

        // Check if token is missing
        if (!$token) {
            $data = ['code' => 400, 'message' => 'Token is required', 'data' => null];
            return $this->jsonResponse($response, $data, 400);
        }

        // Get JWT secret key from environment variables
        $key = $_ENV['JWT_SECRET'];

        // Initialize JWT middleware with the secret key
        $jwtMiddleware = new JwtMiddleware($key);

        // Decode the JWT token
        $decoded = $jwtMiddleware->decodeToken($token);

        // Check if $decoded is an array (indicating an error)
        if (is_array($decoded) && isset($decoded['error'])) {
            $data = ['code' => 401, 'message' => $decoded['error'], 'data' => null];
            return $this->jsonResponse($response, $data, 401);
        }

        // Convert $decoded to an array if it's an object
        $decoded = is_object($decoded) ? json_decode(json_encode($decoded), true) : $decoded;

        // Extract user ID from the decoded token
        $userId = $decoded['data']['email'] ?? null;

        // Initialize database connection and user model
        $db = new Database();
        $userModel = new User($db);

        // Check if the user exists in the database
        $doesUserExist = $userModel->fetchUserInfo($userId);

        if (!$doesUserExist) {
            $data = ['code' => 401, 'message' => 'user does not exist', 'data' => null];
            return $this->jsonResponse($response, $data, 401);
        }

        // Return success message if token is valid
        $data = ['code' => 200, 'message' => 'token is valid', 'data' => null];
        return $this->jsonResponse($response, $data, 200);
    }


    /**
     * Handle user login.
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

        if (!$email || !$password) {
            $data = ['code' => 401, 'message' => 'Email and password are required', 'data' => null];
            return $this->jsonResponse($response, $data, 401);
        }

        $userModel = new User($database);

        $user = $userModel->fetchUserInfo($email, true);

        if (!$user || !password_verify($password, $user['password'])) {
            $data = ['code' => 401, 'message' => 'Invalid email or password', 'data' => null];
            return $this->jsonResponse($response, $data, 401);
        }

        $user = $userModel->fetchUserInfo($email);

        $jwtMiddleware = new JwtMiddleware($this->jwtSecret);

        // Generate JWT
        $jwtPayload = ['userId' => $user['user_id']];
        $jwt = $jwtMiddleware->generateToken($jwtPayload, 15780096);

        // Generate refresh token (optional, depending on your authentication flow)
        $refreshToken = $jwtMiddleware->generateRefreshToken($user['user_id']); // Generate a random refresh token

        // Store the refresh token securely (e.g., in the database)
        $userSessionModel = new UserSession($database);
        $storedSession = $userSessionModel->createSession([
            'user_id' => $user['user_id'],
            'access_token' => $jwt,
            'refresh_token' => $refreshToken,
            'expires_at' => date('Y-m-d H:i:s', strtotime('+1 hour')), // Example expiration time
            'last_refreshed_at' => date('Y-m-d H:i:s'), // Initial refresh time
        ]);

        $userLoggedInUpdate = $userModel->updateUser([
            'last_logged_in' => date('Y-m-d H:i:s')
        ], $user['user_id']);

        $data = [
            'code' => 200,
            'message' => 'login successful',
            'data' => [
                'jwt' => $jwt,
                'refreshToken' => $refreshToken // Include refresh token if needed
            ]
        ];

        if ($storedSession === true && $userLoggedInUpdate === true) {
            return $this->jsonResponse($response, $data);
        }

        return $this->jsonResponse($response, [
            'code' => 500,
            'message' => 'An Error occurred',
            'data' => null
        ], 500);
    }




    /**
     * Handle user account signup.
     *
     * @param Request $request The HTTP request object.
     * @param Response $response The HTTP response object.
     * @param array $args Route arguments.
     * @return Response HTTP response with JSON data.
     */
    public function signup(Request $request, Response $response, $args)
    {

        // Create a Database instance
        $database = new Database();

        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Expose-Headers: ');
        header('Access-Control-Allow-Headers: *');

        // Get the raw JSON data from the request body
        $jsonBody = file_get_contents('php://input');

        // Decode the JSON data into an associative array
        $parsedBody = json_decode($jsonBody, true);

        // Check if email and password are present in the parsed body
        $email = $parsedBody['email'] ?? null;
        $password = $parsedBody['password'] ?? null;

        // Validate email and password (you can add more validation as needed)
        if (!$email || !$password) {

            $data = ['code' => 401, 'message' => 'email and password are required', 'data' => null];
            return $this->jsonResponse($response, $data, 401);

        }

        $userModel = new User($database);

        $doesUserExist = $userModel->fetchUserInfo($email);

        if ($doesUserExist !== false) {
            $data = ['code' => 401, 'message' => 'user already exist', 'data' => null];
            return $this->jsonResponse($response, $data, 401);
        }


        // Create user account (you'll need to implement this logic)
        $addUserToDB = $userModel->createUser([
            'user_id' => HelpersRandomStringGenerator::generate(20),
            'email' => $email,
            'password' => password_hash($password, PASSWORD_BCRYPT)
        ]);

        if (!$addUserToDB) {
            $data = ['code' => 401, 'message' => 'failed to create user. ERR[DB_001]', 'data' => null];
            return $this->jsonResponse($response, $data, 401);
        }

        $emailSender = new ControllersEmailSender();

        // Send verification email (you'll need to implement this logic)

        $email_path = getcwd() . DIRECTORY_SEPARATOR . '../src/Assets/email/templates/email_confirmation.html';
        $htmlBody = file_get_contents($email_path);

        $key = $_ENV['JWT_SECRET'];
        $jwtMiddleware = new JwtMiddleware($key);
        $token = $jwtMiddleware->generateToken(['email' => $email]);

        if (!$token) {
            throw new Exception("Failed to generate JWT token");
        }

        $confirmUrl = $_ENV['APPLICATION_FRONTEND_URL'] . "/dashboard/profile-information?token=" . urlencode($token);

        // Replace placeholder in HTML with the actual URL
        $htmlBody = str_replace('{{url}}', $confirmUrl, $htmlBody);

        $emailSenderStatus = $emailSender->sendHTMLEmail($email, 'Confirm Registration', $htmlBody);

        if ($emailSenderStatus !== true) {

            $data = ['code' => 500, 'message' => 'an error occured please contact the admin.', 'data' => null];
            return $this->jsonResponse($response, $data, 500);
        }

        // Return response based on success or failure
        return $this->jsonResponse($response, [
            'code' => 200,
            'message' => 'signup successful',
            'data' => null
        ]);
    }

    /**
     * Handle user details form submission.
     *
     * @param Request $request The HTTP request object.
     * @param Response $response The HTTP response object.
     * @param array $args Route arguments.
     * @return Response HTTP response with JSON data.
     */
    public function details(Request $request, Response $response, $args)
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Expose-Headers: ');
        header('Access-Control-Allow-Headers: *');


        // Get the raw JSON data from the request body
        $jsonBody = file_get_contents('php://input');

        // Decode the JSON data into an associative array
        $parsedBody = json_decode($jsonBody, true);

        // Check if required fields are present in the parsed body
        $firstName = $parsedBody['first_name'] ?? null;
        $lastName = $parsedBody['last_name'] ?? null;
        $middleName = $parsedBody['middle_name'] ?? '';
        $address = $parsedBody['address'] ?? null;
        $email = $parsedBody['email'] ?? null;

        if (!$email) {
            $data = ['code' => 401, 'message' => 'email is required', 'data' => null];
            return $this->jsonResponse($response, $data, 401);
        }

        // Validate form data (you can add more validation as needed)
        if (!$firstName || !$lastName || !$address) {

            $data = ['code' => 401, 'message' => 'first name, last name and address are required', 'data' => null];
            return $this->jsonResponse($response, $data, 401);

        }


        $userModel = new User($this->db);

        $user = $userModel->fetchUserInfo($email, true);

        if (!$user) {
            $data = ['code' => 401, 'message' => 'user does not exist', 'data' => null];
            return $this->jsonResponse($response, $data, 401);
        }

        // Save user details 
        $addUserToDB = $userModel->updateUser([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'middle_name' => $middleName,
            'address' => $address,
            'status' => 'active',
            'verified_email' => true,
        ], $user['user_id']);

        if (!$addUserToDB) {
            $data = ['code' => 401, 'message' => 'failed to update user. ERR[DB_002]', 'data' => null];
            return $this->jsonResponse($response, $data, 401);
        }



        $emailSender = new ControllersEmailSender();

        $email_path = getcwd() . DIRECTORY_SEPARATOR . '../src/Assets/email/templates/email_confirmed.html';
        $htmlBody = file_get_contents($email_path);

        $key = $_ENV['JWT_SECRET'];
        $jwtMiddleware = new JwtMiddleware($key);
        $token = $jwtMiddleware->generateToken(['email' => $email]);
        $refreshToken = $jwtMiddleware->generateRefreshToken([
            'email' => $email,
            'userId' => $user['user_id']
        ]);

        if (!$token) {
            $data = ['code' => 500, 'message' => 'an error occured please contact the admin.', 'data' => null];
            return $this->jsonResponse($response, $data, 500);
        }

        if (!$refreshToken) {
            $data = ['code' => 500, 'message' => 'an error occured please contact the admin.', 'data' => null];
            return $this->jsonResponse($response, $data, 500);
        }


        $confirmUrl = $_ENV['APPLICATION_FRONTEND_URL'] . "/login";

        // Replace placeholder in HTML with the actual URL
        $htmlBody = str_replace('{{url}}', $confirmUrl, $htmlBody);
        $htmlBody = str_replace('{{first_name}}', $firstName, $htmlBody);

        // Send verification email to tell them email confimed 
        $emailSenderStatus = $emailSender->sendHTMLEmail($email, 'Account Verified', $htmlBody);

        if (!$emailSenderStatus) {
            $data = ['code' => 500, 'message' => 'an error occured please contact the admin.', 'data' => null];
            return $this->jsonResponse($response, $data, 500);
        }



        // Return response based on success or failure
        return $this->jsonResponse($response, [
            'code' => 200,
            'message' => 'details saved successfully',
            'data' => [
                'jwt' => $token,
                'refreshToken' => $refreshToken // Include refresh token if needed
            ]
        ]);
    }


    /**
     * Update user information endpoint.
     * This endpoint allows users to update their profile information such as name, address, email, and password.
     * 
     * @param Request $request The HTTP request
     * @param Response $response The HTTP response
     * @param mixed $args Additional route arguments
     * @return Response The HTTP response containing the result of the update operation
     */
    public function updateUser(Request $request, Response $response, $args)
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Expose-Headers: ');
        header('Access-Control-Allow-Headers: *');

        // Verify the JWT token from the request
        if (!$this->verifyTokenWithDB($request, $response)) {
            return $this->jsonResponse($response, [
                'code' => 401,
                'message' => 'Unauthorized'
            ], 401);
        }

        $decoded = $request->getAttribute('decoded');

        // Check if $decoded contains an error message
        if (isset($decoded->error)) {
            return $this->jsonResponse($response, [
                'code' => 401,
                'message' => $decoded->error,
            ], 401);
        }

        $id = $decoded->data->userId;

        $userModel = new User($this->db);

        // Fetch the user's current information from the database
        $user = $userModel->fetchUserInfo($id, true);

        if (!$user) {
            $data = ['code' => 401, 'message' => 'User not found', 'data' => null];
            return $this->jsonResponse($response, $data, 401);
        }

        // Get the raw JSON data from the request body
        $jsonBody = file_get_contents('php://input');

        // Decode the JSON data into an associative array
        $parsedBody = json_decode($jsonBody, true);

        // Prepare update data by comparing provided values with current user details
        $updateData = [];
        $isSensitiveDataUpdated = false;

        if (isset($parsedBody['first_name']) && $parsedBody['first_name'] !== $user['first_name']) {
            $updateData['first_name'] = $parsedBody['first_name'];
        }

        if (isset($parsedBody['last_name']) && $parsedBody['last_name'] !== $user['last_name']) {
            $updateData['last_name'] = $parsedBody['last_name'];
        }

        if (isset($parsedBody['middle_name']) && $parsedBody['middle_name'] !== $user['middle_name']) {
            $updateData['middle_name'] = $parsedBody['middle_name'];
        }

        if (isset($parsedBody['address']) && $parsedBody['address'] !== $user['address']) {
            $updateData['address'] = $parsedBody['address'];
        }

        if (isset($parsedBody['email']) && $parsedBody['email'] !== $user['email']) {
            $updateData['email'] = $parsedBody['email'];
            $isSensitiveDataUpdated = true; // Mark sensitive data as updated
        }

        // Check if password needs to be updated
        $oldPassword = $parsedBody['old_password'] ?? null;
        $newPassword = $parsedBody['new_password'] ?? null;
        $confirmNewPassword = $parsedBody['confirm_new_password'] ?? null;

        if ($newPassword && $confirmNewPassword) {
            if ($newPassword === $confirmNewPassword) {
                if (password_verify($oldPassword, $user['password'])) {
                    $updateData['password'] = password_hash($newPassword, PASSWORD_BCRYPT);
                    $isSensitiveDataUpdated = true; // Mark sensitive data as updated
                } else {
                    $data = ['code' => 401, 'message' => 'Old password is incorrect', 'data' => null];
                    return $this->jsonResponse($response, $data, 401);
                }
            } else {
                $data = ['code' => 401, 'message' => 'New passwords do not match', 'data' => null];
                return $this->jsonResponse($response, $data, 401);
            }
        }

        // If no fields to update, return success message
        if (empty($updateData)) {
            $data = ['code' => 200, 'message' => 'No changes to update', 'data' => null];
            return $this->jsonResponse($response, $data, 200);
        }

        // Update user details in the database
        $updateUserInDB = $userModel->updateUser($updateData, $user['user_id']);

        if (!$updateUserInDB) {
            $data = ['code' => 401, 'message' => 'Failed to update user', 'data' => null];
            return $this->jsonResponse($response, $data, 401);
        }

        $emailSender = new EmailSender();

        $email_path = getcwd() . DIRECTORY_SEPARATOR . '../src/Assets/email/templates/email_profile_updated_success.html';
        $htmlBody = file_get_contents($email_path);


        $emailSenderStatus = $emailSender->sendHTMLEmail($user['email'], 'Your Account Information Has Been Successfully Updated', $htmlBody);

        if ($emailSenderStatus !== true) {

            $data = ['code' => 500, 'message' => 'an error occured please contact the admin.', 'data' => null];
            return $this->jsonResponse($response, $data, 500);
        }

        // Determine the HTTP response code based on sensitive data update
        $statusCode = $isSensitiveDataUpdated ? 210 : 200;
        $message = $isSensitiveDataUpdated ? 'user updated successfully' : 'user updated successfully';
        $data = ['code' => $statusCode, 'message' => $message, 'data' => null];
        return $this->jsonResponse($response, $data, $statusCode);
    }




    /**
     * Refresh JWT token endpoint.
     * This endpoint generates a new JWT token and refresh token based on a provided refresh token.
     * 
     * @param Request $request The HTTP request
     * @param Response $response The HTTP response
     * @return Response The HTTP response containing the new JWT token and refresh token
     */
    public function refresh(Request $request, Response $response)
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Expose-Headers: ');
        header('Access-Control-Allow-Headers: *');

        // Get the raw JSON data from the request body
        $jsonBody = file_get_contents('php://input');

        // Decode the JSON data into an associative array
        $body = json_decode($jsonBody, true);

        $oldJwt = $body['token'] ?? null;

        if (!$oldJwt) {
            return $this->jsonResponse($response, [
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

        $jwtMiddleware = new JwtMiddleware($this->jwtSecret);
        $decodedRefreshToken = $jwtMiddleware->decodeToken($refreshToken);

        if (is_array($decodedRefreshToken) && isset($decodedRefreshToken['error'])) {
            return JsonResponder::generate($response, [
                'code' => 401,
                'message' => 'access token is invalid or expired token',
                'data' => null
            ], 401);
        }

        $decodedOldJWT = $jwtMiddleware->decodeToken($oldJwt);

        if (!is_array($decodedOldJWT)) {
            return JsonResponder::generate($response, [
                'code' => 401,
                'message' => 'token has not expired',
                'data' => null
            ], 401);
        }

        if (is_array($decodedRefreshToken) && isset($decodedRefreshToken['error']) && $decodedRefreshToken['error'] !== 'token has expired') {
            return JsonResponder::generate($response, [
                'code' => 401,
                'message' => $decodedRefreshToken['error'],
                'data' => null
            ], 401);
        }


        $userId = $decodedRefreshToken->userId ?? null;

        // Validate the refresh token with the database
        $userSessionsModel = new UserSession($this->db);
        $storedToken = $userSessionsModel->getToken($userId, $refreshToken, $oldJwt);

        if (!$storedToken) {
            return $this->jsonResponse($response, [
                'code' => 401,
                'message' => 'Invalid token',
                'data' => null
            ], 401);
        }

        $userModel = new User($this->db);

        $user = $userModel->fetchUserInfo($userId);

        if (!$user) {
            return JsonResponder::generate($response, [
                'code' => 401,
                'message' => "user not found",
                "data" => null
            ], 401);
        }

        $jwtMiddleware = new JwtMiddleware($this->jwtSecret);
        // Generate new JWT and refresh token
        $jwtPayload = ['userId' => $userId, 'email' => $user['email']];

        $jwt = $jwtMiddleware->generateToken($jwtPayload);

        $newRefreshToken = $jwtMiddleware->generateRefreshToken($userId);

        $userSessionsModel->updateSession($userId, [
            'access_token' => $jwt,
            'refresh_token' => $newRefreshToken,
            'expires_at' => date('Y-m-d H:i:s', strtotime('+1 hour')), // Example expiration time
            'last_refreshed_at' => date('Y-m-d H:i:s'), // Initial refresh time
        ]);

        return $this->jsonResponse($response, [
            'code' => 201,
            'message' => 'Token refreshed successfully',
            'data' => [
                'jwt' => $jwt,
                'refreshToken' => $newRefreshToken
            ]
        ], 201);
    }


    /**
     * Get user endpoint.
     * This endpoint retrieves user information based on the provided email.
     * 
     * @param Request $request The HTTP request
     * @param Response $response The HTTP response
     * @param mixed $args Additional route arguments
     * @return Response The HTTP response containing user information
     */
    public function getUser(Request $request, Response $response, array $args): Response
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Expose-Headers: ');
        header('Access-Control-Allow-Headers: *');

        $checkToken = $this->verifyTokenWithDB($request, $response);

        // Verify the JWT token in the request
        if (!$checkToken) {


            return $this->jsonResponse($response, [
                'code' => 401,
                'message' => 'Unauthorized'
            ], 401);

        }

        if ($checkToken instanceof Response) {
            return $checkToken;
        }


        // Get the decoded token and query parameters from the request
        $decoded = $request->getAttribute('decoded');
        $queryParams = $request->getQueryParams();

        // Check if there's an error in the decoded token
        if (is_array($decoded) && isset($decoded['error'])) {
            $message = $decoded['error'] ?? 'An error occurred';

            return $this->jsonResponse($response, [
                'message' => $message,
                'code' => 401,
                'data' => null
            ], 401);
        }

        // Extract the email parameter from query parameters
        $email = $queryParams['email'] ?? null;

        // Ensure the email parameter is provided
        if (!$email) {
            return $this->jsonResponse($response, [
                'code' => 401,
                'message' => 'Email parameter is required',
                'data' => null
            ], 401);
        }

        // Get the user ID from the decoded token
        $userId = $decoded->data->userId;

        // Initialize the User model for database interaction
        $userModel = new User($this->db);

        // Fetch user information from the database based on user ID
        $user = $userModel->fetchUserInfo($userId);

        // Check if user data is retrieved successfully
        if (!$user) {
            $data = ['code' => 401, 'message' => 'User not found', 'data' => null];
            return $this->jsonResponse($response, $data, 401);
        }

        // Check if the provided email matches the user's email
        if ($email !== $user['email']) {
            $data = ['code' => 401, 'message' => 'User not found', 'data' => null];
            return $this->jsonResponse($response, $data, 401);
        }

        $userCoursesModel = new UserCourse($this->db);
        $courses = $userCoursesModel->getUserCourses($userId) ?? [];

        // $courses = [];
        // $robots = [];

        $userRobotModel = new UserRobot($this->db);
        $robots = $userRobotModel->getUserRobots($userId) ?? [];

        $finalCourses = [];
        $finalRobots = [];

        $coursess = isset($courses['courses']) ? json_decode($courses['courses'], true) : [];

        foreach ($coursess as $key => $course) {
            
            // var_dump($course);
            $slug = $course['slug'];

            $coursesModel = new Courses($this->db);

            $courseInfo = $coursesModel->fetchCourseDetails($slug);

            if ( is_array($courseInfo) )
            {
                // var_dump($courseInfo);

                $coursesVideos = json_decode($courseInfo['course_videos'], true);
                $quizesVideos = json_decode($courseInfo['quiz_videos'], true);
                $liveSessionVideos = json_decode($courseInfo['live_session_videos'], true);
                   

                foreach ($coursesVideos as $key => $video) {
                    
                    $baseUrl = $_ENV['APPLICATION_BACKEND_URL'];

                    $fullUrl = $baseUrl . '/uploads/courses/' . $courseInfo['course_id'] . '/';

                    $video = $fullUrl . $video;

                    $coursesVideos[$key] = $video;

                }

                foreach ($quizesVideos as $key => $video) {
                    
                    $baseUrl = $_ENV['APPLICATION_BACKEND_URL'];

                    $fullUrl = $baseUrl . '/uploads/courses/' . $courseInfo['course_id'] . '/';

                    $video = $fullUrl . $video;

                    $quizesVideos[$key] = $video;

                }

                foreach ($liveSessionVideos as $key => $video) {
                    
                    $baseUrl = $_ENV['APPLICATION_BACKEND_URL'];

                    $fullUrl = $baseUrl . '/uploads/courses/' . $courseInfo['course_id'] . '/';

                    $video = $fullUrl . $video;

                    $liveSessionVideos[$key] = $video;

                }

                 
                  $courseImageUrl = $baseUrl . '/uploads/courses/' . $courseInfo['course_id'] . '/' . $courseInfo['image'];
                  $courseInfo['image'] = $courseImageUrl;

                $courseInfo['course_videos'] = $coursesVideos;
                $courseInfo['quiz_videos'] = $quizesVideos;
                $courseInfo['live_session_videos'] = $liveSessionVideos;

                array_push($finalCourses, $courseInfo);


            }

        }

        $robots = isset($robots['robots']) ? json_decode($robots['robots'], true) : [];
        foreach ($robots as $key => $robot) {

            $slug = $robot['slug'] ?? '';

            $robotsModel = new Robots($this->db);

            $robotInfo = $robotsModel->fetchRobotDetails($slug);


            if ( is_array($robotInfo) )
            {

                // change the url for the image too

                $baseUrl = $_ENV['APPLICATION_BACKEND_URL'];
                $fullUrl = $baseUrl . '/uploads/robots/' . $robotInfo['robot_id'];

                $zipName = $fullUrl . $robotInfo['zip'];
                $imageName = $fullUrl . $robotInfo['image'];

                $robotInfo['image'] = $imageName;
                $robotInfo['zip'] = $zipName;

            }

            array_push($finalRobots, $robotInfo);



        }



        // $user['courses'] = isset($courses['courses']) ? json_decode($courses['courses'], true) : [];

        $user['courses'] = $finalCourses;

        // $user['robots'] = isset($robots['robots']) ? json_decode($robots['robots'], true) : [];

        $user['robots'] = $finalRobots;

        // Write the user information to the response body
        $response->getBody()->write(json_encode($user));

        // Set the response headers and status code
        return $response->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    }



}
