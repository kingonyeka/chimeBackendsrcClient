<?php 

namespace App\Controllers\Admins;

use App\Database\Database;
use App\Helpers\JsonResponder;
use App\Models\Admin;
use App\Models\Payment;
use App\Models\Payments;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

class SuperAdminController
{

    private $jwtSecret;
    private $db;

    /**
     * AuthController constructor.
     * 
     * @param string $jwtSecret The JWT secret key
     */
    public function __construct()
    {
        $this->jwtSecret = $_ENV['JWT_SECRET'];
        $this->db = new Database();
    }

    public function getSuperAdminInfo(Request $request, Response $response, $args) {
        
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Expose-Headers: ');
        header('Access-Control-Allow-Headers: *');

        $adminModel = new Admin($this->db);

        // Fetch the user's current information from the database
        $superAdminInfo = $adminModel->fetchSuperAdminInfo(false);

        

        // return Success message
        return JsonResponder::generate($response, [
            "code" => 200,
            "message" => "super-admin fetched successfully",
            "data" => $superAdminInfo
        ], 201);

    }

    public function updateSuperAdmin(Request $request, Response $response, $args) {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Expose-Headers: ');
        header('Access-Control-Allow-Headers: *');

        // // Verify the bearer token
        // $checkBearerToken = AdminJwtVerifier::verify($request, $response);

        // if ($checkBearerToken instanceof Response) {
        //     return $checkBearerToken;
        // } elseif ($checkBearerToken === false) {
        //     return JsonResponder::generate($response, [
        //         "code" => 401,
        //         "message" => "unauthorized",
        //         "data" => null,
        //     ], 401);
        // }

        // $adminInfo = $checkBearerToken;

        // if (!$adminInfo) {
        //     return false;
        // }


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

        // check if user exist in the users table 
        $userModel = new Admin($this->db);

        // Fetch the user's current information from the database
        $user = $userModel->fetchAdminInfo($email);

        // return error if not found
        if (!$user) {
            $data = ['code' => 401, 'message' => 'super admin not found', 'data' => null];
            return JsonResponder::generate($response, $data, 401);
        }

        if ( $user['role_name'] !== 'super-admin' )
        {
            return JsonResponder::generate($response, [
                'code' => 401,
                'message' => "unkown super admin",
                'data' => null
            ], 401);
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

        // if (isset($parsedBody['email']) && $parsedBody['email'] !== $user['email']) {
        //     $updateData['email'] = $parsedBody['email'];
        // }

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
            $data = ['code' => 401, 'message' => 'failed to update admin', 'data' => null];
            return JsonResponder::generate($response, $data, 401);
        }

        // return Success message
        return JsonResponder::generate($response, [
            "code" => 200,
            "message" => "admin updated successfully",
            "data" => null
        ], 201);

    }

}