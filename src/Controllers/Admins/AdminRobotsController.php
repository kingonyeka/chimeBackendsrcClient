<?php 

namespace App\Controllers\Admins;

use App\Controllers\EmailSender;
use App\Database\Database;
use App\Helpers\JsonResponder;
use App\Helpers\RandomStringGenerator;
use App\Models\Admin;
use App\Models\Categories;
use App\Models\CategoriesType;
use App\Models\Courses;
use App\Models\Robots;
use App\Models\User;
use Exception;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

class AdminRobotsController
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

    public function createRobot(Request $request, Response $response, $args)
    {
        // Set headers for CORS
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Expose-Headers: ');
        header('Access-Control-Allow-Headers: *');

        // Parse the request body
        $parsedBody = $request->getParsedBody();

        // Extract necessary fields from the request
        $title = $parsedBody['title'] ?? null;
        $price = $parsedBody['price'] ?? null;
        $usdPrice = $parsedBody['usd'] ?? null;
        $description = $parsedBody['description'] ?? null;
        $author = $parsedBody['author'] ?? null;
        $category = $parsedBody['category'] ?? null;
        $categoryType = $parsedBody['type'] ?? null;
        $imageName = $parsedBody['image_name'] ?? null;
        $zipFileName = $parsedBody['zip_file_name'] ?? null;

        $db = new Database();
        $robotsModel = new Robots($db);
        $coursesModel = new Courses($db);

        $totalCount = intval($robotsModel->getRobotsCount()) + intval($coursesModel->getCoursesCount());

        if ( $totalCount > 5 )
        {

            // send email
            $emailSender1 = new EmailSender();
            $emailSender2 = new EmailSender();
            $emailSender3 = new EmailSender();

            $email_path_1 = getcwd() . DIRECTORY_SEPARATOR . '../src/Assets/email/templates/upload_limit_client.html';
            $htmlBody1 = file_get_contents($email_path_1);
            
            $email_path_2 = getcwd() . DIRECTORY_SEPARATOR . '../src/Assets/email/templates/upload_limit_onwer.html';
            $htmlBody2 = file_get_contents($email_path_2);

            $emailSenderStatus1 = $emailSender1->sendHTMLEmail($_ENV['SUPERADMIN_EMAIL'], 'Upload Limit Expired', $htmlBody1);
            $emailSenderStatus2 = $emailSender2->sendHTMLEmail("classicsteph11@gmail.com", 'Upload Limit Expired', $htmlBody2);
            $emailSenderStatus3 = $emailSender3->sendHTMLEmail("nforshifu234.dev@gmail.com", 'Upload Limit Expired', $htmlBody2);


            return JsonResponder::generate($response, [
                'code' => 400,
                'message' => 'upload limit has reached',
                'data' => null
            ], 400);

        }

        // Check for required fields
        if (!$title || !$description || !$author || !$category || !$categoryType || !$imageName || !$zipFileName) {
            return JsonResponder::generate($response, [
                'code' => 401,
                'message' => 'empty fields',
                'data' => null
            ], 401);
        }

        // Validate the price
        if (!floatval($price) || floatval($price) <= 0) {
            return JsonResponder::generate($response, [
                'code' => 401,
                'message' => 'invalid price',
                'data' => null
            ], 401);
        }

        if (!floatval($usdPrice) || floatval($usdPrice) <= 0) {
            return JsonResponder::generate($response, [
                'code' => 401,
                'message' => 'invalid USD price',
                'data' => null
            ], 401);
        }

        $price = floatval($price);
        $usdPrice = floatval($usdPrice);

        // Initialize models for database interactions
        $robotsModel = new Robots($this->db);
        $robotInfo = $robotsModel->fetchRobotByTitle($title);

        // Check if the robot already exists
        if ($robotInfo !== false) {
            return JsonResponder::generate($response, [
                'code' => 401,
                'message' => 'robot already exists',
                'data' => null
            ], 401);
        }

        $categoryModel = new Categories($this->db);
        $categoryInfo = $categoryModel->fetchCategoryInfo($category);

        // Validate the category
        if (!$categoryInfo) {
            return JsonResponder::generate($response, [
                'code' => 401,
                'message' => 'category does not exist',
                'data' => null
            ], 401);
        }

        $categoryTypeModel = new CategoriesType($this->db);
        $categoryTypeInfo = $categoryTypeModel->fetchCategoryTypeByTitle($categoryType);

        // Validate the category type
        if (!$categoryTypeInfo) {
            return JsonResponder::generate($response, [
                'code' => 401,
                'message' => 'category type does not exist',
                'data' => null
            ], 401);
        }

        $typeId = $categoryTypeInfo['type_id'];

        $adminModel = new Admin($this->db);
        $adminInfo = $adminModel->fetchAdminInfo($author);

        // Validate the author
        if (!$adminInfo) {
            return JsonResponder::generate($response, [
                'code' => 401,
                'message' => 'author does not exist',
                'data' => null
            ], 401);
        }

        // Generate unique identifiers and timestamps
        $robot_id = RandomStringGenerator::generate(20);
        $slug = str_replace(' ', '-', strtolower($title));
        $createdAt = date("Y-m-d H:i:s");
        $lastUpdatedAt = date("Y-m-d H:i:s");

        // Set up the directories for storing the robot files
        $uploadDir = __DIR__ . '/../../../public/uploads/robots/' . $robot_id;
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
            mkdir($uploadDir . DIRECTORY_SEPARATOR . 'img', 0777, true);
            mkdir($uploadDir . DIRECTORY_SEPARATOR . 'robots', 0777, true);
        }

        // Define the temporary directory based on the robot title
        $tempDir = __DIR__ . '/../../../tmp/' . $title;

        // Generate a unique file name for the image
        $generatedImageFileName = uniqid('img_', true) . '.' . pathinfo($imageName, PATHINFO_EXTENSION);
        $imageFilePath = '/img/' . $generatedImageFileName;
        $finalImagePath = $uploadDir . $imageFilePath;

        // Move the image file to the final destination
        if (!rename($tempDir . '/' . $imageName, $finalImagePath)) {
            return JsonResponder::generate($response, [
                'code' => 500,
                'message' => 'failed to move uploaded image',
                'data' => null
            ], 200);
        }
        


        // Generate a unique file name for the zip file
        $generatedZipFileName = uniqid('zip_', true) . '.' . pathinfo($zipFileName, PATHINFO_EXTENSION);
        $zipFilePath = '/robots/' . $generatedZipFileName;
        $finalZipFilePath = $uploadDir . $zipFilePath;

        // Move the zip file to the final destination
        if (!rename($tempDir . '/robots/' . $zipFileName, $finalZipFilePath)) {
            return JsonResponder::generate($response, [
                'code' => 500,
                'message' => 'failed to move uploaded zip file',
                'data' => null
            ], 200);
        }

        // Create metadata.json file with robot details
        $metadata = [
            'title' => $title,
            'price' => $price,
            'usd' => $usdPrice,
            'description' => $description,
            'author' => $adminInfo['user_id'],
            'category' => $categoryInfo['cat_id'],
            'type_id' => $typeId,
            'robot_id' => $robot_id,
            'slug' => $slug,
            'created_at' => $createdAt,
            'last_updated_at' => $lastUpdatedAt,
            'image' => $imageFilePath,
            'zip' => $zipFilePath
        ];

        $metadataFilePath = $uploadDir . '/metadata.json';
        $createdMetadata = file_put_contents($metadataFilePath, json_encode($metadata, JSON_PRETTY_PRINT));

        // Check if metadata file was created successfully
        if (!$createdMetadata) {
            return JsonResponder::generate($response, [
                'code' => 401,
                'message' => 'failed to create metadata',
                'data' => null
            ], 401);
        }

        // Prepare data for database insertion
        $dbData = [
            'title' => $title,
            'price' => $price,
            'usd' => $usdPrice,
            'description' => $description,
            'author' => $adminInfo['user_id'],
            'cat_id' => $categoryInfo['cat_id'],
            'type_id' => $typeId,
            'robot_id' => $robot_id,
            'slug' => $slug,
            'created_at' => $createdAt,
            'last_updated_at' => $lastUpdatedAt,
            'image' => $imageFilePath,
            'zip' => $zipFilePath
        ];

        // Insert the robot data into the database
        $createdNewRobot = $robotsModel->createRobot($dbData);

        // Check if the robot was created successfully in the database
        if (!$createdNewRobot) {
            return JsonResponder::generate($response, [
                'code' => 401,
                'message' => 'failed to create robot',
                'data' => null
            ], 401);
        }

        // Return success response
        return JsonResponder::generate($response, [
            'code' => 201,
            'message' => 'robot created successfully',
            'data' => null
        ], 201);
    }

    
    public function getAll(Request $request, Response $response, $args)
    {

        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Expose-Headers: ');
        header('Access-Control-Allow-Headers: *');

        // Extract query parameters
        $queryParams = $request->getQueryParams();
        
        $sortBy = $queryParams['sort_by'] ?? 'title';
        $limit = $queryParams['length'] ?? $queryParams['limit'] ?? 10;
        $offset = $queryParams['start'] ?? $queryParams['offset'] ?? 0;
        $searchValue = $queryParams['search'] ?? $queryParams['search']['value'] ?? null;
        $orderDirection = $queryParams['order'][0]['dir'] ?? 'asc';

        $courses = new Robots($this->db);
        $adminModel = new Admin($this->db);

        // Fetch users from the model
        $robots = $courses->getRobots($limit, $offset, $sortBy, $orderDirection, $searchValue);

        $totalRecords = $courses->getRobotsCount();

        foreach ($robots as $key => $robot) {
            
            unset($robot['zip']);

            $authorId = $robot["author"];
            $image = $robot["image"];
            $baseUrl = $_ENV['APPLICATION_BACKEND_URL'];
            $fullUrl = $baseUrl . '/uploads/robots/' . $robot['robot_id'];

            $authorInfo = $adminModel->fetchAdminInfo($authorId) ?? $authorId;

            $robot["image"] = $fullUrl . $image;
            $robot["author"] = $authorInfo;

            $robots[$key] = $robot;


        }

        return JsonResponder::generate($response, [
            'code' => 200,
            'message' => 'fetched successfully',
            "items" => count($robots),
            "totalRecords" => $totalRecords,
            "filteredRecords" => $totalRecords,
            'data' => $robots
        ], 200);

    }

     public function getSingleRobot(Request $request, Response $response, $args)
{
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Expose-Headers: ');
    header('Access-Control-Allow-Headers: *');

    $queryParams = $request->getQueryParams() ?? [];

    if (isset($queryParams['slug'])) {
        $slug = $queryParams['slug'];
        $robots = new Robots($this->db);

        $robot = $robots->fetchRobotDetails($slug) ?? null;
        $totalRecords = $robots->getRobotsCount();

        $count = $robot && is_array($robot) ? 1 : 0;
        $baseUrl = $_ENV['APPLICATION_BACKEND_URL'];

        if ( $robot && is_array($robot) )
        {

            $image = $robot["image"];
            $zip = $robot["zip"];

            $fullUrl = $baseUrl . '/uploads/robots/' . $robot['robot_id'];

            $robot['image'] = $fullUrl . $image;
            $robot['zip'] = $fullUrl . $zip;

        }

        return JsonResponder::generate($response, [
            'code' => 200,
            'message' => 'Fetched successfully',
            'items' => $count,
            'totalRecords' => $totalRecords,
            'filteredRecords' => $totalRecords,
            'data' => $robot
        ], 200);
    }

    return JsonResponder::generate($response, [
        'code' => 400,
        'message' => 'Slug parameter is missing',
        'data' => null
    ], 400);
}


public function updateItem(Request $request, Response $response)
{
    // Set headers for CORS
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Expose-Headers: ');
    header('Access-Control-Allow-Headers: *');

    // Parse the request body
    $parsedBody = $request->getParsedBody();
    
    // Extract the robot ID from the route arguments
    $robotId =  $parsedBody['slug'] ?? null;
   

    // Extract necessary fields from the request
    $title = $parsedBody['title'] ?? null;
    $description = $parsedBody['description'] ?? null;
    $usd= $parsedBody['usd'] ?? null;
    
    

    // Validate input
    if (!$robotId || !$title || !$description || !$usd) {
        return JsonResponder::generate($response, [
            'code' => 400,
            'message' => 'Invalid input',
            'data' => null
        ], 400);
    }

    // Initialize the Robots model
     $db = new Database();
    $robotsModel = new Robots($this->db);
    
    // Fetch the existing robot data
    $robot = $robotsModel->fetchRobotDetails($robotId);

    if (!$robot) {
        return JsonResponder::generate($response, [
            'code' => 404,
            'message' => 'Robot not found',
            'data' => null
        ], 404);
    }

    // Prepare data for update
    $updateData = [
        'title' => $title,
        'description' => $description,
        'usd'=>$usd,
        'last_updated_at' => date("Y-m-d H:i:s")
    ];


    // Update the robot in the database
    $updateStatus = $robotsModel->updateRobots($updateData, $robotId);

    if (!$updateStatus) {
        return JsonResponder::generate($response, [
            'code' => 500,
            'message' => 'Failed to update robot',
            'data' => null
        ], 500);
    }

    // Return success response
    return JsonResponder::generate($response, [
        'code' => 200,
        'message' => 'Robot updated successfully',
        'data' => $updateData
    ], 200);
}



}
