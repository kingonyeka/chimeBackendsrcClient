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
use Slim\Psr7\UploadedFile;

class AdminCoursesController
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

    public function createCourse(Request $request, Response $response, $args)
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Expose-Headers: ');
        header('Access-Control-Allow-Headers: *');

        $parsedBody = $request->getParsedBody();

        $title = $parsedBody['title'] ?? null;
        $price = $parsedBody['price'] ?? null;
        $usdPrice = $parsedBody['usd'] ?? null;
        $description = $parsedBody['description'] ?? null;
        $author = $parsedBody['author'] ?? null;
        $category = $parsedBody['category'] ?? null;
        $categoryType = $parsedBody['type'] ?? null;
        $imageName = $parsedBody['image_name'] ?? null;

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

        if (!$title || !$description || !$author || !$category || !$categoryType || !$imageName) {
            return JsonResponder::generate($response, [
                'code' => 401,
                'message' => 'empty fields',
                'data' => null
            ], 401);
        }

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

        $coursesModel = new Courses($this->db);
        $courseInfo = $coursesModel->fetchCourseByTitle($title);

        if ($courseInfo !== false) {
            return JsonResponder::generate($response, [
                'code' => 401,
                'message' => 'course already exists',
                'data' => null
            ], 401);
        }

        $categoryModel = new Categories($this->db);
        $categoryInfo = $categoryModel->fetchCategoryInfo($category);

        if (!$categoryInfo) {
            return JsonResponder::generate($response, [
                'code' => 401,
                'message' => 'category does not exist',
                'data' => null
            ], 401);
        }

        $categoryTypeModel = new CategoriesType($this->db);
        $categoryTypeInfo = $categoryTypeModel->fetchCategoryTypeByTitle($categoryType);

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

        if (!$adminInfo) {
            return JsonResponder::generate($response, [
                'code' => 401,
                'message' => 'author does not exist',
                'data' => null
            ], 401);
        }

        $course_id = RandomStringGenerator::generate(20);
        $slug = str_replace(' ', '-', strtolower($title));
        $createdAt = date("Y-m-d H:i:s");
        $lastUpdatedAt = date("Y-m-d H:i:s");

        $uploadDir = __DIR__ . '/../../../public/uploads/courses/' . $course_id;
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
            mkdir($uploadDir . DIRECTORY_SEPARATOR . 'img', 0777, true);
            mkdir($uploadDir . DIRECTORY_SEPARATOR . 'course', 0777, true);
            mkdir($uploadDir . DIRECTORY_SEPARATOR . 'quiz', 0777, true);
            mkdir($uploadDir . DIRECTORY_SEPARATOR . 'live_session', 0777, true);
        }

        // Move the uploaded image file
        $tempDir = __DIR__ . '/../../../tmp/' . $title;
        $generatedImageFileName = uniqid('img_', true) . '.' . pathinfo($imageName, PATHINFO_EXTENSION);
        $imageFilePath = '/img/' . $generatedImageFileName;
        $finalImagePath = $uploadDir . '/img/' . $generatedImageFileName;

        if (!rename($tempDir . '/' . $imageName, $finalImagePath)) {
            return JsonResponder::generate($response, [
                'code' => 500,
                'message' => 'failed to move uploaded image',
                'data' => null
            ], 200);
        }

        // Helper function to sort and rename files
        $sortAndRenameFiles = function($fileNames, $prefix) {
            natsort($fileNames);
            $sortedFileNames = [];
            $index = 1;
            foreach ($fileNames as $fileName) {
                $newFileName = sprintf('%s_%02d.%s', $prefix, $index, pathinfo($fileName, PATHINFO_EXTENSION));
                $sortedFileNames[] = ['original' => $fileName, 'new' => $newFileName];
                $index++;
            }
            return $sortedFileNames;
        };

        // Sort and rename course videos
        $sortedCourseFiles = $sortAndRenameFiles($parsedBody['course_video_names'], 'chapter');
        $sortedQuizFiles = $sortAndRenameFiles($parsedBody['quiz_video_names'], 'quiz_chapter');
        $sortedLiveSessionFiles = $sortAndRenameFiles($parsedBody['live_session_video_names'], 'live_session');

        $moveFiles = function($sortedFiles, $subDir) use ($tempDir, $uploadDir) {
            $finalFileNames = [];
            foreach ($sortedFiles as $file) {
                // $finalFilePath = $uploadDir . '/vid/' . $file['new'];
                $finalFilePath = $uploadDir . '/' . $subDir . '/' . $file['new'];
                if (!rename($tempDir . '/' . $subDir . '/' . $file['original'], $finalFilePath)) {
                    throw new Exception('failed to move uploaded file: ' . $file['original']);
                }
                $finalFileNames[] = $subDir . '/' . $file['new'];
            }
            return $finalFileNames;
        };

        try {
            $courseVideoNames = $moveFiles($sortedCourseFiles, 'course');
            $quizVideoNames = $moveFiles($sortedQuizFiles, 'quiz');
            $liveSessionVideoNames = $moveFiles($sortedLiveSessionFiles, 'live_session');
        } catch (Exception $e) {
            return JsonResponder::generate($response, [
                'code' => 500,
                'message' => $e->getMessage(),
                'data' => null
            ], 200);
        }

        // Create metadata.json file
        $metadata = [
            'title' => $title,
            'price' => $price,
            'usdPrice' => $usdPrice,
            'description' => $description,
            'author' => $adminInfo['user_id'],
            'category' => $categoryInfo['cat_id'],
            'type_id' => $typeId,
            'course_id' => $course_id,
            'slug' => $slug,
            'created_at' => $createdAt,
            'last_updated_at' => $lastUpdatedAt,
            'image' => $imageFilePath,
            'course_videos' => $courseVideoNames,
            'quiz_videos' => $quizVideoNames,
            'live_session_videos' => $liveSessionVideoNames
        ];

        $metadataFilePath = $uploadDir . '/metadata.json';

        $createdMetadata = file_put_contents($metadataFilePath, json_encode($metadata, JSON_PRETTY_PRINT));

        if (!$createdMetadata) {
            return JsonResponder::generate($response, [
                'code' => 401,
                'message' => 'failed to create metadata',
                'data' => null
            ], 401);
        }

        $dbData = [
            'title' => $title,
            'price' => $price,
            'usd' => $usdPrice,
            'description' => $description,
            'author' => $adminInfo['user_id'],
            'cat_id' => $categoryInfo['cat_id'],
            'type_id' => $typeId,
            'course_id' => $course_id,
            'slug' => $slug,
            'created_at' => $createdAt,
            'last_updated_at' => $lastUpdatedAt,
            'image' => $imageFilePath,
            'course_videos' => json_encode($courseVideoNames),
            'quiz_videos' => json_encode($quizVideoNames),
            'live_session_videos' => json_encode($liveSessionVideoNames)
        ];

        $createdNewCourse = $coursesModel->createCourse($dbData);

        if (!$createdNewCourse) {
            return JsonResponder::generate($response, [
                'code' => 401,
                'message' => 'failed to create course',
                'data' => null
            ], 401);
        }

        return JsonResponder::generate($response, [
            'code' => 201,
            'message' => 'course created successfully',
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

        $coursesModel = new Courses($this->db);

        // Fetch users from the model
        $courses = $coursesModel->getCourses($limit, $offset, $sortBy, $orderDirection, $searchValue);

        $adminModel = new Admin($this->db);

        foreach ($courses as $key => $course) {
            
            $authorId = $course["author"];
            $image = $course["image"];
            $baseUrl = $_ENV['APPLICATION_BACKEND_URL'];
            $fullUrl = $baseUrl . '/uploads/courses/' . $course['course_id'];

            $authorInfo = $adminModel->fetchAdminInfo($authorId) ?? $authorId;

            $course["image"] = $fullUrl . $image;
            $course["author"] = $authorInfo;

            $courses[$key] = $course;


        }

        $totalRecords = $coursesModel->getCoursesCount();

        return JsonResponder::generate($response, [
            'code' => 200,
            'message' => 'fetched successfully',
            "items" => count($courses),
            "totalRecords" => $totalRecords,
            "filteredRecords" => $totalRecords,
            'data' => $courses
        ], 200);

    }
    
   public function getSingleCourse(Request $request, Response $response, $args)
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Expose-Headers: ');
        header('Access-Control-Allow-Headers: *');

        $queryParams = $request->getQueryParams();
        
        if (isset($queryParams['slug'])) {
            $slug = $queryParams['slug'];
        
            $coursesModel = new Courses($this->db);

        
            $course = $coursesModel->getSingleCourse($slug);
  
            
            $adminModel = new Admin($this->db);

                
            $authorId = $course["author"];
            $image = $course["image"];
            $baseUrl = $_ENV['APPLICATION_BACKEND_URL'];
            $fullUrl = $baseUrl . '/uploads/courses/' . $course['course_id'];

            $authorInfo = $adminModel->fetchAdminInfo($authorId) ?? $authorId;


            $course["image"] = $fullUrl . $image;
            $course["author"] = $authorInfo;

            $coursesVideos = json_decode($course['course_videos'], true);
            $quizesVideos = json_decode($course['quiz_videos'], true);
            $liveSessionVideos = json_decode($course['live_session_videos'], true);

            foreach ($coursesVideos as $key => $video) {
                
                $baseUrl = $_ENV['APPLICATION_BACKEND_URL'];

                $fullUrl = $baseUrl . '/uploads/courses/' . $course['course_id'] . '/';

                $video = $fullUrl . $video;

                $coursesVideos[$key] = $video;

            }

            foreach ($quizesVideos as $key => $video) {
                
                $baseUrl = $_ENV['APPLICATION_BACKEND_URL'];

                $fullUrl = $baseUrl . '/uploads/courses/' . $course['course_id'] . '/';

                $video = $fullUrl . $video;

                $quizesVideos[$key] = $video;

            }

            foreach ($liveSessionVideos as $key => $video) {
                
                $baseUrl = $_ENV['APPLICATION_BACKEND_URL'];

                $fullUrl = $baseUrl . '/uploads/courses/' . $course['course_id'] . '/';

                $video = $fullUrl . $video;

                $liveSessionVideos[$key] = $video;

            }

            $course['course_videos'] = $coursesVideos;
            $course['quiz_videos'] = $quizesVideos;
            $course['live_session_videos'] = $liveSessionVideos;



            $totalRecords = $coursesModel->getCoursesCount();
            
            return JsonResponder::generate($response, [
                'code' => 200,
                'message' => 'fetched successfully',
                "items" => 1,
                "totalRecords" => $totalRecords,
                "filteredRecords" => $totalRecords,
                'data' => $course
            ], 200);
        } else {
            return JsonResponder::generate($response, [
                'code' => 400,
                'message' => 'slug query param missing',
            ], 400);
        }
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
    $Id =  $parsedBody['slug'] ?? null;
   

    // Extract necessary fields from the request
    $title = $parsedBody['title'] ?? null;
    $description = $parsedBody['description'] ?? null;
       $usd = $parsedBody['usd'] ?? null;
    
    

    // Validate input
    if (!$Id || !$title || !$description || !$description) {
        return JsonResponder::generate($response, [
            'code' => 400,
            'message' => 'Invalid input',
            'data' => null
        ], 400);
    }

 
    $coursesModel = new Courses($this->db);
  

    // Prepare data for update
    $updateData = [
        'title' => $title,
        'description' => $description,
        'usd' => $usd,
        'last_updated_at' => date("Y-m-d H:i:s")
    ];


    // Update the robot in the database
    $updateStatus = $coursesModel->updateCourses($updateData, $Id);

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
