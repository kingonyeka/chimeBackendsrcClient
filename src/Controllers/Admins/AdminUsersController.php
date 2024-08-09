<?php 

namespace App\Controllers\Admins;

use App\Controllers\EmailSender;
use App\Database\Database;
use App\Helpers\JsonResponder;
use App\Middleware\JwtMiddleware;
use App\Models\Admin;
use App\Models\AdminSession;
use App\Models\Payment;
use App\Models\Payments;
use App\Models\User;
use App\Models\UserCourse;
use App\Models\UserPayment;
use App\Models\UserSession;
use DateTime;
use Illuminate\Support\Facades\Date;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

class AdminUsersController
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
     * Handle admin register.
     *
     * @param Request $request The HTTP request object.
     * @param Response $response The HTTP response object.
     * @param array $args Route arguments.
     * @return Response HTTP response with JSON data.
    */
    public function register(Request $request, Response $response, $args) {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Expose-Headers: ');
        header('Access-Control-Allow-Headers: *');
        // Verify the bearer token
        $checkBearerToken = AdminJwtVerifier::verify($request, $response);

        if ($checkBearerToken instanceof Response) {
            return $checkBearerToken;
        } elseif ($checkBearerToken === false) {
            return JsonResponder::generate($response, [
                "code" => 401,
                "message" => "Unauthorized",
                "data" => null,
            ], 401);
        }

        $adminInfo = $checkBearerToken;

        if (!$adminInfo) {
            return false;
        }


        // get details from the frontend (email) only
        $parsedBody = $request->getParsedBody();

        // Check if email is present in the parsed body
        $email = $parsedBody['email'] ?? null;
        
        if (!$email) {
            $data = ['code' => 401, 'message' => 'email is required', 'data' => null];
            return JsonResponder::generate($response, $data, 401);
        }

        // check if user exist in the admin table 
        $adminModel = new Admin($this->db);

        $loggedInAdminInfo = $adminModel->fetchAdminInfo($adminInfo['user_id'], true);


        $admin = $adminModel->fetchAdminInfo($email, true);
       
        if ($admin) {
            $data = ['code' => 401, 'message' => 'user exists', 'data' => null];
            return JsonResponder::generate($response, $data, 401);
        }

        // check if user exist in the users table 
        $userModel = new User($this->db);

        // Fetch the user's current information from the database
        $user = $userModel->fetchUserInfo($email, true);
        
        // return error if not found
        if (!$user) {
            $data = ['code' => 401, 'message' => 'user not found', 'data' => null];
            return JsonResponder::generate($response, $data, 401);
        }

        // get the user details and then make a copy to admin details
        $userFirstName = $user['first_name'];
        $userLastName = $user['last_name'];
        $userMiddleName = $user['middle_name'];
        $userAddress = $user['address'];
        $userEmail = $user['email'];
        $userPassword = $user['password'];
        $user_id = $user['user_id'];
        $date_created = $user['created_at'];

        $newAdmin = [
            'email' => $userEmail,
            'password' => $userPassword,
            'first_name' => $userFirstName,
            'last_name' => $userLastName,
            'middle_name' => $userMiddleName,
            'address' => $userAddress,
            'status' => 'active',
            'user_id' => $user_id,
            'created_by' => $loggedInAdminInfo['user_id'],
            'date_last_modified' => date('Y-m-d H:i:s'),
            'verified_email' => true,
        ];



        // add admin details to the db
        $addAdminToDB = $adminModel->createAdmin($newAdmin);

        // check status of all emails to be sent and if admin has been created in the db
        if ( ! $addAdminToDB )
        {

            // return Error message
            return JsonResponder::generate($response, [
                'code' => 401,
                "message" => "account creation failed",
                "data" => null
            ], 401);

        }

        $userSessionsModel = new UserSession($this->db);

        $sessionInfo = $userSessionsModel->getSessionByUserId($user_id);

        if ( $sessionInfo !== false )
        {

            if ( ! $userSessionsModel->deleteSession($sessionInfo['access_token']) )
            {

                // return Error message
                return JsonResponder::generate($response, [
                    'code' => 401,
                    "message" => "account creation failed 2",
                    "data" => null
                ], 401);


            }

        }

        $paymentsModel = new Payments($this->db);

        $getPayments = $paymentsModel->getPaymentInfo($user_id);

        if ( $getPayments !== false )
        {
            
            if ( ! $paymentsModel->transferUserToAdmin($user_id) )
            {
                return JsonResponder::generate($response, [
                    'code' => 401,
                    "message" => "account creation failed 3",
                    "data" => null  
                ], 401);
            }

        }

        // $userCoursesModel = new UserCourse($this->db);


        // $user = $userModel->fetchUserInfo($email, true);


        // if ( $user !== false )
        // {

        //     if ( ! $userModel->deleteUser($user_id) )
        //     {
    
        //         // return Error message
        //         return JsonResponder::generate($response, [
        //             'code' => 401,
        //             "message" => "account creation failed 3",
        //             "data" => null
        //         ], 401);
    
        //     }

        // }

        $newAdminMessagePath = getcwd() . DIRECTORY_SEPARATOR . '../src/Assets/email/templates/email_new_admin_message.html';
        $newAdminMessagePathHtmlBody = file_get_contents($newAdminMessagePath);

        $newAdminMessagePathHtmlBody = str_replace('{{first_name}}', $userFirstName, $newAdminMessagePathHtmlBody);

        $adminMessagePath = getcwd() . DIRECTORY_SEPARATOR . '../src/Assets/email/templates/email_new_admin_admin_message.html';
        $adminMessagePathHtmlBody = file_get_contents($adminMessagePath);


        $userFullName = "{$userFirstName} {$userMiddleName} {$userLastName}";
        $yearJoined = new DateTime($date_created);
        $yearJoined = $yearJoined->format('Y');
        
        $currentYear = (new DateTime())->format('Y');

        $howLongInYears = $currentYear - $yearJoined;

        if ( $howLongInYears < 1 )
        {
            $howLongInYears = "{$yearJoined} (less than a year)";
        }
        else
        {
            $howLongInYears = "{$yearJoined} year(s) ago";
        }

        $adminMessagePathHtmlBody = str_replace('{{new_admin_name}}', $userFullName, $adminMessagePathHtmlBody);
        $adminMessagePathHtmlBody = str_replace('{{new_admin_email}}', $userEmail, $adminMessagePathHtmlBody);
        $adminMessagePathHtmlBody = str_replace('{{year_joined}}', $howLongInYears, $adminMessagePathHtmlBody);

        $nameParts = array_filter([$loggedInAdminInfo['first_name'], $loggedInAdminInfo['middle_name'], $loggedInAdminInfo['last_name']]);
        $creatorName = implode(' ', $nameParts);

        $adminMessagePathHtmlBody = str_replace('{{creator_name}}', $creatorName , $adminMessagePathHtmlBody);
        $adminMessagePathHtmlBody = str_replace('{{creator_email}}', $loggedInAdminInfo['email'], $adminMessagePathHtmlBody);


        // send email to the new admin
        $emailSender = new EmailSender();
        $emailSender2 = new EmailSender();

        // send email to the admin that is creating the user and a copy to the root admin
        $emailSenderStatus = $emailSender->sendHTMLEmail( $userEmail, 'Welcome to the Chime Forex Trading Team!', $newAdminMessagePathHtmlBody );
        $emailSenderStatus2 = $emailSender2->sendHTMLEmail( $_ENV['SUPERADMIN_EMAIL'], 'Security Notification: New Admin Account Created', $adminMessagePathHtmlBody );

        if ( $emailSenderStatus !== true && $emailSenderStatus2 !== true )
        {

            $fallbackUser = [
                'email' => $userEmail,
                'password' => $userPassword,
                'first_name' => $userFirstName,
                'last_name' => $userLastName,
                'middle_name' => $userMiddleName,
                'address' => $userAddress,
                'status' => 'active',
                'user_id' => $user_id,
                'created_at' => $date_created,
                'date_last_modified' => date('Y-m-d H:i:s'),
                'verified_email' => $user['verified_email'],
                'status' => $user['status'],
            ];

            $adminModel->deleteAdmin($user_id);
            $userModel->createUser($fallbackUser);

            

            $data = ['code' => 500, 'message' => 'an error occured please contact the admin.', 'data' => null];
            return JsonResponder::generate($response, $data, 500);
        }

        // return Success message
        return JsonResponder::generate($response, [
            "code" => 201,
            "message" => "successfully created",
            "data" => null
        ], 201);




    }

    public function ban(Request $request, Response $response, $args) {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Expose-Headers: ');
        header('Access-Control-Allow-Headers: *');
        // Verify the bearer token
        $checkBearerToken = AdminJwtVerifier::verify($request, $response);

        if ($checkBearerToken instanceof Response) {
            return $checkBearerToken;
        } elseif ($checkBearerToken === false) {
            return JsonResponder::generate($response, [
                "code" => 401,
                "message" => "unauthorized",
                "data" => null,
            ], 401);
        }

        $adminInfo = $checkBearerToken;

        if (!$adminInfo) {
            return false;
        }


        // get details from the frontend (email) only
        $parsedBody = $request->getParsedBody();

        // Check if email is present in the parsed body
        $email = $parsedBody['email'] ?? null;
        
        if (!$email) {
            $data = ['code' => 401, 'message' => 'email is required', 'data' => null];
            return JsonResponder::generate($response, $data, 401);
        }

        // check if user exist in the admin table 
        $adminModel = new Admin($this->db);

        $loggedInAdminInfo = $adminModel->fetchAdminInfo($adminInfo['user_id'], true);

        // check if user exist in the users table 
        $userModel = new User($this->db);

        // Fetch the user's current information from the database
        $user = $userModel->fetchUserInfo($email, true);
        
        // return error if not found
        if (!$user) {
            $data = ['code' => 401, 'message' => 'user not found', 'data' => null];
            return JsonResponder::generate($response, $data, 401);
        }

        $updateUserstatus = $userModel->updateUser( [
            'status' => 'banned',
            'date_last_modified' => date('Y-m-d H:i:s')
        ] , $user['user_id'] );

        if ( ! $updateUserstatus )
        {

            // return Error message
            return JsonResponder::generate($response, [
                'code' => 401,
                "message" => "failed to ban user",
                "data" => null
            ], 401);

        }

        // return Error message
        return JsonResponder::generate($response, [
            'code' => 200,
            "message" => "banned successfully",
            "data" => null
        ], 200);

    }

    public function updateUser(Request $request, Response $response, $args) {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Expose-Headers: ');
        header('Access-Control-Allow-Headers: *');
        // Verify the bearer token
        $checkBearerToken = AdminJwtVerifier::verify($request, $response);

        if ($checkBearerToken instanceof Response) {
            return $checkBearerToken;
        } elseif ($checkBearerToken === false) {
            return JsonResponder::generate($response, [
                "code" => 401,
                "message" => "unauthorized",
                "data" => null,
            ], 401);
        }

        $adminInfo = $checkBearerToken;

        if (!$adminInfo) {
            return false;
        }


        // get details from the frontend (email) only
        $parsedBody = $request->getParsedBody();

        // Check if email is present in the parsed body
        $email = $parsedBody['email'] ?? null;
        
        if (!$email) {
            $data = ['code' => 401, 'message' => 'email is required', 'data' => null];
            return JsonResponder::generate($response, $data, 401);
        }

        // check if user exist in the admin table 
        $adminModel = new Admin($this->db);

        $loggedInAdminInfo = $adminModel->fetchAdminInfo($adminInfo['user_id'], true);

        // check if user exist in the users table 
        $userModel = new User($this->db);

        // Fetch the user's current information from the database
        $user = $userModel->fetchUserInfo($email, true);

        // return error if not found
        if (!$user) {
            $data = ['code' => 401, 'message' => 'user not found', 'data' => null];
            return JsonResponder::generate($response, $data, 401);
        }

        // Prepare update data by comparing provided values with current user details
        $updateData = [];

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
        }

        // Check if password needs to be updated
        $newPassword = $parsedBody['new_password'] ?? null;

        if ($newPassword) {

            $updateData['password'] = password_hash($newPassword, PASSWORD_BCRYPT);

        }

        // If no fields to update, return success message
        if (empty($updateData)) {
            $data = ['code' => 200, 'message' => 'no changes to update', 'data' => null];
            return JsonResponder::generate($response, $data, 200);
        }

        $updateData['date_last_modified'] = date("Y-m-d H:i:s");

        // Update user details in the database
        $updateUserInDB = $userModel->updateUser($updateData, $user['user_id']);

        if (!$updateUserInDB) {
            $data = ['code' => 401, 'message' => 'failed to update user', 'data' => null];
            return JsonResponder::generate($response, $data, 401);
        }

        // return Success message
        return JsonResponder::generate($response, [
            "code" => 201,
            "message" => "user updated successfully",
            "data" => null
        ], 201);

    }

    public function updateAdmin(Request $request, Response $response, $args) {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Expose-Headers: ');
        header('Access-Control-Allow-Headers: *');
        // Verify the bearer token
        $checkBearerToken = AdminJwtVerifier::verify($request, $response);

        if ($checkBearerToken instanceof Response) {
            return $checkBearerToken;
        } elseif ($checkBearerToken === false) {
            return JsonResponder::generate($response, [
                "code" => 401,
                "message" => "unauthorized",
                "data" => null,
            ], 401);
        }

        $adminInfo = $checkBearerToken;

        if (!$adminInfo) {
            return false;
        }


        // get details from the frontend (email) only
        $parsedBody = $request->getParsedBody();

        // Check if email is present in the parsed body
        $email = $parsedBody['email'] ?? null;
        
        if (!$email) {
            $data = ['code' => 401, 'message' => 'email is required', 'data' => null];
            return JsonResponder::generate($response, $data, 401);
        }

        // check if user exist in the admin table 
        $adminModel = new Admin($this->db);

        $loggedInAdminInfo = $adminModel->fetchAdminInfo($adminInfo['user_id'], true);

        // check if user exist in the users table 
        $userModel = new Admin($this->db);

        // Fetch the user's current information from the database
        $user = $userModel->fetchAdminInfo($email, true);

        // return error if not found
        if (!$user) {
            $data = ['code' => 401, 'message' => 'user not found', 'data' => null];
            return JsonResponder::generate($response, $data, 401);
        }

        // Prepare update data by comparing provided values with current user details
        $updateData = [];

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
        }

        // Check if password needs to be updated
        $newPassword = $parsedBody['new_password'] ?? null;

        if ($newPassword) {

            $updateData['password'] = password_hash($newPassword, PASSWORD_BCRYPT);

        }

        // If no fields to update, return success message
        if (empty($updateData)) {
            $data = ['code' => 200, 'message' => 'no changes to update', 'data' => null];
            return JsonResponder::generate($response, $data, 200);
        }

        $updateData['date_last_modified'] = date("Y-m-d H:i:s");

        // Update user details in the database
        $updateUserInDB = $userModel->updateAdmin($updateData, $user['user_id']);

        if (!$updateUserInDB) {
            $data = ['code' => 401, 'message' => 'failed to update user', 'data' => null];
            return JsonResponder::generate($response, $data, 401);
        }

        // return Success message
        return JsonResponder::generate($response, [
            "code" => 201,
            "message" => "user updated successfully",
            "data" => null
        ], 201);

    }
    
    public function getAllUsers(Request $request, Response $response, $args)
    {
         header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Expose-Headers: ');
        header('Access-Control-Allow-Headers: *');
        // Extract query parameters
        $queryParams = $request->getQueryParams();
        
        $sortBy = $queryParams['sort_by'] ?? 'first_name';
        $limit = $queryParams['length'] ?? $queryParams['limit'] ?? 10;
        $offset = $queryParams['start'] ?? $queryParams['offset'] ?? 0;
        $searchValue = $queryParams['search'] ?? null;
        $orderDirection = $queryParams['order'][0]['dir'] ?? 'asc';

        $userModel = new User($this->db);

        // Fetch users from the model
        $users = $userModel->getUsers($limit, $offset, $sortBy, $orderDirection, $searchValue);

        foreach ($users as $key => $user) {
            
            $userId = $user['user_id'];

            $userPaymentModel = new UserPayment($this->db);

            $hasUserPaid = $userPaymentModel->fetchUserPaymentInfo($userId) === false ? false : true;
            $userCourses = null;

            if ( $hasUserPaid !== false )
            {

                $userCoursesModel = new UserCourse($this->db);
                $userCourses = $userCoursesModel->getUserCourses($userId);

                if ( isset($userCourses[0]['courses']) )
                {

                    $userCourses = json_decode($userCourses[0]['courses'], true);

                }
                else
                {
                    $userCourses = null;
                }

            }

            $paymentModel = new Payments($this->db);
            $totalAmount = $paymentModel->getTotalAmountByUserId($userId);

            $user['hasUserPaid'] = $hasUserPaid;
            $user['courses'] = $userCourses;
            $user['total_amount'] = $totalAmount;

            $users[$key] = $user;

        }

        $totalRecords = $userModel->getUsersCount();

        return JsonResponder::generate($response, [
            'code' => 200,
            'message' => 'fetched successfully',
            "items" => count($users),
            "totalRecords" => $totalRecords,
            "filteredRecords" => $totalRecords,
            'data' => $users
        ], 200);

    }
    
    public function getAllAdmins(Request $request, Response $response, $args)
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Expose-Headers: ');
        header('Access-Control-Allow-Headers: *');
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
        
        // Check if the decoded refresh token has an error other than 'token has expired'
        if (is_array($decodedRefreshToken) && isset($decodedRefreshToken['error']) && $decodedRefreshToken['error'] !== 'token has expired') {
            return JsonResponder::generate($response, [
                'code' => 401,
                'message' => $decodedRefreshToken['error'],
                'data' => null
            ], 401);
        }


        // Get the user ID from the decoded refresh token
        $adminId = $decodedRefreshToken->data->userId ?? $decodedRefreshToken->userId ?? null;

        // Validate the refresh token with the database
        $adminSessionsModel = new AdminSession($this->db);
        $storedToken = $adminSessionsModel->getAccessToken($adminId, $refreshToken);
        
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
        $roleName = $admin['role_name'];

        // Extract query parameters
        $queryParams = $request->getQueryParams();
        
        $sortBy = $queryParams['sort_by'] ?? 'first_name';
        $limit = $queryParams['length'] ?? $queryParams['limit'] ?? 10;
        $offset = $queryParams['start'] ?? $queryParams['offset'] ?? 0;
        $searchValue = $queryParams['search'] ?? $queryParams['search']['value'] ?? null;
        $orderDirection = $queryParams['order'][0]['dir'] ?? 'asc';

        $userModel = new Admin($this->db);

        // Fetch users from the model
        $users = $userModel->getAdmin($limit, $offset, $sortBy, $orderDirection, $searchValue);

        $totalRecords = $userModel->getAdminsCount();

        if ( $roleName === 'super-admin' )
        {

            return JsonResponder::generate($response, [
                'code' => 200,
                'message' => 'fetched successfully',
                "items" => count($users),
                "totalRecords" => $totalRecords,
                "filteredRecords" => $totalRecords,
                'data' => $users
            ], 200);

        } 

        // Filter out super-admins
        $filteredUsers = array_filter($users, function($user) {
            return $user['role_name'] !== 'super-admin';
        });

        $totalRecords = $userModel->getAdminsCount();
        $filteredRecordsCount = count($filteredUsers);

        return JsonResponder::generate($response, [
            'code' => 200,
            'message' => 'fetched successfully',
            "items" => $filteredRecordsCount,
            "totalRecords" => $totalRecords,
            "filteredRecords" => $filteredRecordsCount,
            'data' => array_values($filteredUsers) // reindex array
        ], 200);


    }


}
