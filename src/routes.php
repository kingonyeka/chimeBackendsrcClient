<?php

use App\Controllers\Admins\AdminAuthController;
use App\Controllers\Admins\AdminCategoryController;
use App\Controllers\Admins\AdminCoursesController;
use App\Controllers\Admins\AdminPaymentController;
use App\Controllers\Admins\AdminRobotsController;
use App\Controllers\Admins\AdminUsersController;
use App\Controllers\Admins\SuperAdminController;
use App\Controllers\AuthController;
use App\Controllers\CartController;
use App\Controllers\EmailSender;
use Slim\Routing\RouteCollectorProxy;
use App\Controllers\PasswordResetController;
use App\Controllers\PaymentController;
use App\Controllers\PaymentHandlers\PilisioPaymentHandler;
use App\Controllers\TelegramController;
use App\Database\Database;
use App\Helpers\JsonResponder;
use App\Middleware\JwtMiddleware;
use App\Models\Cart;
use App\Models\Courses;
use App\Models\Payment;
use App\Models\Robots;
use App\Models\User;
use App\Models\UserPayment;
use Slim\Exception\HttpNotFoundException;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Stripe\Stripe;

return function ($app) {



    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Expose-Headers: ');
    header('Access-Control-Allow-Headers: *');
    // Define the JWT middleware
    $jwtMiddleware = new JwtMiddleware($app->getContainer()->get('settings')['jwt']['secret']);

    $app->post('/upload', function (Request $request, Response $response, $args) {


        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Expose-Headers: ');
        header('Access-Control-Allow-Headers: *');

        // Define the base directory for uploads
        $baseUploadDir = __DIR__ . '/../tmp/';
        // Define the temporary directory for storing file chunks
        $tmpDir = __DIR__ . '/../tmp/blob/';

        // Create the temporary directory if it doesn't exist
        if (!file_exists($tmpDir)) {
            mkdir($tmpDir, 0777, true);
        }

        // Parse the request body for necessary data
        $parsedBody = $request->getParsedBody();
        $chunkNumber = isset($parsedBody['chunkNumber']) ? intval($parsedBody['chunkNumber']) : 0;
        $totalChunks = isset($parsedBody['totalChunks']) ? intval($parsedBody['totalChunks']) : 1;
        $fileName = isset($parsedBody['filename']) ? $parsedBody['filename'] : '';
        $courseTitle = isset($parsedBody['course_title']) && ! empty($parsedBody['course_title']) ? $parsedBody['course_title'] : null;
        $uploadType = isset($parsedBody['upload_type']) && ! empty($parsedBody['upload_type']) ? $parsedBody['upload_type'] : null;

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

        $allowedTypes = ['quiz', 'course', 'live_session', 'robots'];

        if ( $uploadType === 'course' || $uploadType === 'robots' )
        {

            if ( $uploadType && ! in_array($uploadType, $allowedTypes) )
            {
    
                return JsonResponder::generate($response, [
                    'code' => 400,
                    'message' => 'invalid type',
                    'data' => null
                ], 400);
                
            }

        }

        if ($courseTitle) {


            if ( $uploadType === 'course' )
            {

                $courseInfo = $coursesModel->fetchCourseByTitle($courseTitle);
            
                if ($courseInfo !== false) {
                    return JsonResponder::generate($response, [
                        'code' => 401,
                        'message' => 'course already exists',
                        'data' => null
                    ], 401);
                }
    

            }
            else if ( $uploadType === "robots" )
            {


                $robotsInfo = $robotsModel->fetchRobotByTitle($courseTitle);

                if ($robotsInfo !== false) {
                    return JsonResponder::generate($response, [
                        'code' => 401,
                        'message' => 'robot already exists',
                        'data' => null
                    ], 401);
                }

            }


        }



        // Determine the final upload directory
        $uploadDir = $baseUploadDir;
        if ($courseTitle) {
            // If course title is provided, use it as a subdirectory
            $uploadDir .= $courseTitle . DIRECTORY_SEPARATOR;
            // Create the course title directory if it doesn't exist
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }


            if ( $uploadType && in_array($uploadType, $allowedTypes) )
            {

                $uploadDir .= $uploadType . DIRECTORY_SEPARATOR;
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
            }

        }

        // Retrieve the uploaded file
        $uploadedFiles = $request->getUploadedFiles();
        $file = $uploadedFiles['file'];
        
       

        // Define the path for the current chunk
        $chunkFileName = $tmpDir . $fileName . '.part' . $chunkNumber;

        // Check if the file was uploaded without errors
        if ($file->getError() === UPLOAD_ERR_OK) {
            // Move the uploaded chunk to the temporary directory
            $file->moveTo($chunkFileName);

            // If all chunks are received, assemble the final file
            if ($chunkNumber === $totalChunks - 1) {
                $finalFileName = $uploadDir . $fileName;
                $finalFile = fopen($finalFileName, 'ab');

                // Concatenate all chunks into the final file
                for ($i = 0; $i < $totalChunks; $i++) {
                    $chunkFileName = $tmpDir . $fileName . '.part' . $i;
                    $chunk = fopen($chunkFileName, 'rb');
                    stream_copy_to_stream($chunk, $finalFile);
                    fclose($chunk);
                }

                fclose($finalFile);

                // Delete temporary chunk files
                for ($i = 0; $i < $totalChunks; $i++) {
                    $chunkFileName = $tmpDir . $fileName . '.part' . $i;
                    unlink($chunkFileName);
                }

                // Respond with success message
               
                $response->getBody()->write(json_encode(['fileName' => $fileName, 'message' => 'File assembled successfully.']));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            }

            // Respond with chunk upload success message
            $response->getBody()->write(json_encode(['message' => 'Chunk ' . ($chunkNumber + 1) . ' of ' . $totalChunks . ' uploaded successfully.']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        }

        // Respond with error message if upload failed
        $response->getBody()->write(json_encode(['message' => 'Failed to upload chunk ' . ($chunkNumber + 1) . ' of ' . $totalChunks . '.']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    })->add($jwtMiddleware);


    $app->group('/auth', function (RouteCollectorProxy $group) {
        $group->post('/login', AuthController::class . ':login');
        $group->post('/signup', AuthController::class . ':signup');
        $group->post('/details', AuthController::class . ':details')
            ->add(new JwtMiddleware($this->get('settings')['jwt']['secret']));
        $group->post('/confirm-token', AuthController::class . ':decodeToken');
        $group->post('/refresh', AuthController::class . ':refresh'); // New route for refresh token
            // ->add(new JwtMiddleware($this->get('settings')['jwt']['secret']));

    });

    $app->group('/user', function (RouteCollectorProxy $group) {
        // $group->get('', AuthController::class . ':getUser');
        $group->get('/details', AuthController::class . ':getUser');
        $group->post('/details/update', AuthController::class . ':updateUser');
    })->add(new JwtMiddleware($app->getContainer()->get('settings')['jwt']['secret']));

    $app->group('/password', function (RouteCollectorProxy $group) {
        $group->post('/request-reset', PasswordResetController::class . ':requestReset');
        $group->get('/verify-token', PasswordResetController::class . ':verifyToken');
        $group->post('/reset', PasswordResetController::class . ':resetPassword');
    });

 $app->group('/payments', function (RouteCollectorProxy $group) {
        $group->post('/process', PaymentController::class . ':processPayment');
        
        $pilisioPaymentHandler = new PilisioPaymentHandler();

        $group->group('/pilisio', function ($group) use ($pilisioPaymentHandler) {
            $group->post('/initialize', [$pilisioPaymentHandler, 'initialize']);
            $group->post('/callback', [$pilisioPaymentHandler, 'callback']);
        });
        


    });

    // Group routes related to cart actions
    $app->group('/cart', function (RouteCollectorProxy $group) {
        $group->post('/upsert', CartController::class . ':upsert'); // Unified create/update route
        $group->get('/fetch', CartController::class . ':fetchByEmail'); // Fetch by user email
        $group->post('/delete', CartController::class . ':delete'); // Delete by cart ID
        $group->post('/remove', CartController::class . ':removeItem'); // Delete by cart ID
    })->add(new JwtMiddleware($app->getContainer()->get('settings')['jwt']['secret']));

    // Group routes related to admin actions
    $app->group('/admin', function (RouteCollectorProxy $group){

        // Group routes related to admin authentication
        $group->group('/auth', function (RouteCollectorProxy $group){

            $group->post('/login', AdminAuthController::class . ':login');

            $group->post('/refresh', AdminAuthController::class . ':refresh'); // New route for refresh token
                // ->add(new JwtMiddleware($this->get('settings')['jwt']['secret']));

        });

        // Group routes related to admin managing users
        $group->group('/users', function (RouteCollectorProxy $group){

            // $group->post('/create', AdminUsersController::class . ':register');
            $group->get('/', AdminUsersController::class . ':getAllUsers');
            $group->post('/ban', AdminUsersController::class . ':ban');
            $group->post('/update', AdminUsersController::class . ':updateUser');

        });

        // Group routes related to admin managing other admins
        $group->group('/admin', function (RouteCollectorProxy $group){
            $group->get('/', AdminUsersController::class . ':getAllAdmins');
            $group->post('/create', AdminUsersController::class . ':register');
            $group->post('/update', AdminUsersController::class . ':updateAdmin');

        });

        $group->group('/courses', function (RouteCollectorProxy $group){

            $group->get('', AdminCoursesController::class . ':getAll')
            ->add(new JwtMiddleware($this->get('settings')['jwt']['secret']));

            $group->post('/create', AdminCoursesController::class . ':createCourse')
                ->add(new JwtMiddleware($this->get('settings')['jwt']['secret']));


        });

        $group->group('/robots', function (RouteCollectorProxy $group){

            $group->get('', AdminRobotsController::class . ':getAll')
                ->add(new JwtMiddleware($this->get('settings')['jwt']['secret']));

            $group->post('/create', AdminRobotsController::class . ':createRobot')
                ->add(new JwtMiddleware($this->get('settings')['jwt']['secret']));


        });

        $group->group('/revenues', function (RouteCollectorProxy $group){

            $group->get('', AdminPaymentController::class . ':getAll');
            $group->get('/usd', AdminPaymentController::class . ':getAllUSD');

        });

        $group->group('/categories', function (RouteCollectorProxy $group){

            $group->get('/', AdminCategoryController::class . ':getAll')
                ->add(new JwtMiddleware($this->get('settings')['jwt']['secret']));

            $group->post('/create', AdminCategoryController::class . ':create')
                ->add(new JwtMiddleware($this->get('settings')['jwt']['secret']));


        });

        
        
    });

    // Group routes related to super admin actions
    $app->group('/super-admin', function (RouteCollectorProxy $group){


        $group->get('/fetch', SuperAdminController::class . ':getSuperAdminInfo');

        $group->post('/update', SuperAdminController::class . ':updateSuperAdmin'); 

    });

    $app->get('/courses', AdminCoursesController::class . ':getAll');
    $app->get('/singleCourse', AdminCoursesController::class . ':getSingleCourse');
    $app->get('/robots', AdminRobotsController::class . ':getAll');
    $app->get('/singleRobot', AdminRobotsController::class . ':getSingleRobot');
     $app->post('/updateRobot', AdminRobotsController::class . ':updateItem');
      $app->post('/updateCourse', AdminCoursesController::class . ':updateItem');

    $app->get('/telegram', TelegramController::class . ':index');
    $app->post('/telegram', TelegramController::class . ':update');


    $app->post('/stripe/create_payment_intent', function (Request $request, Response $response, $args) {
          header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Expose-Headers: ');
    header('Access-Control-Allow-Headers: *');
        Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);
        // Stripe::setApiKey(getenv('STRIPE_SECRET_KEY'));

        // Set your secret key. Remember to switch to your live secret key in production!
        // \Stripe\Stripe::setApiKey(getenv('STRIPE_SECRET_KEY')); // Replace with your secret key

        $body = $request->getParsedBody();

        $amount = $body['amount'];
        $currency = $body['currency'];
        $metadata = $body['metadata'];

        try {

            if ( isset($metadata['courses']) && is_array($metadata['courses']) )
            {
                $metadata['courses'] = json_encode($metadata['courses']);
            }

            if ( isset($metadata['robots']) && is_array($metadata['robots']) )
            {
                $metadata['robots'] = json_encode($metadata['robots']);
            }

            $paymentIntent = \Stripe\PaymentIntent::create([
                'amount' => $amount,
                'currency' => $currency,
                'metadata' => $metadata,
            ]);

            $responseData = [
                'clientSecret' => $paymentIntent->client_secret,
            ];

            $response->getBody()->write(json_encode($responseData));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } catch (\Stripe\Exception\ApiErrorException $e) {
            $error = [
                'error' => $e->getMessage(),
            ];

            $response->getBody()->write(json_encode($error));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    });

$app->post('/stripe/callback', function (Request $request, Response $response, $args) {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Expose-Headers: ');
    header('Access-Control-Allow-Headers: *');
    $body = $request->getParsedBody();
    $paymentIntentId = $body['paymentIntentId'] ?? null;

    if (!$paymentIntentId) {
        $error = ['success' => false, 'message' => 'PaymentIntent ID is required'];
        $response->getBody()->write(json_encode($error));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    try {
        $stripe = new \Stripe\StripeClient($_ENV['STRIPE_SECRET_KEY']); // Replace with your actual secret key
        $paymentIntent = $stripe->paymentIntents->retrieve($paymentIntentId);
         
       

        // Call your function to process the payment intent
        $processStatus = processPaymentIntent($paymentIntent);

        if ($processStatus) {
            $error = ['success' => true, 'message' => 'Payment processed successfully'];
            $response->getBody()->write(json_encode($error));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } else {
            $error = ['success' => false, 'message' => 'Failed to process payment'];
            $response->getBody()->write(json_encode($error));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    } catch (\Exception $e) {
        $error = ['success' => false, 'message' => $e->getMessage()];
        $response->getBody()->write(json_encode($error));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
});

// Function to process the payment intent
function processPaymentIntent($paymentIntent) {
   
    // Extract necessary information
    $email = $paymentIntent->metadata->email;
    $courses = json_decode($paymentIntent->metadata->courses, true) ?? [];
    $robots = json_decode($paymentIntent->metadata->robots, true) ?? [];
    $telegram  = json_decode($paymentIntent->metadata->telegram) ?? [];
    

    if ( ! empty($telegram) )
    {
        $email = $telegram->email ?? '';

    }

    // Initialize the database and models
    $database = new Database();
    $userModel = new User($database);
    $paymentModel = new Payment($database);
    $userPaymentsModel = new UserPayment($database);

    // Fetch user information
    $user = $userModel->fetchUserInfo($email);

    if (!$user) {
        return false;
    }


    // Create the payment entry with the correct user_id
    $paymentData = [
        'payment_id' => $paymentIntent->id,
        'paid_at' => date('Y-m-d H:i:s', $paymentIntent->created),
        'amount' => $paymentIntent->amount / 100,
        'currency' => $paymentIntent->currency,
        'payment_provider' => 'stripe',
        'payment_channel' => 'card',
        'authorization_code' => "---",
        'card_type' => "---",
        'card_last4' => $paymentIntent->charges->data[0]->payment_method_details->card->last4 ?? 0000,
        'bank' => "null",
        'created_at' => date('Y-m-d H:i:s'),
        'user_id' => $user['user_id']
    ];

    $hasPaymentBeenAdded = $paymentModel->createPayment($paymentData);

    if (!$hasPaymentBeenAdded) {
        return false;
    }
    $BaseURL = $_ENV['APPLICATION_FRONTEND_URL'];


    if ( ! empty($telegram) )
    {

        $userInfo = $user;



        if ( ! $userInfo )
        {
            return false;

        }

        $hasUserBeenUpdated = $userModel->updateUser([
            'joined_telegram' => 1
        ], $userInfo['user_id']);

        if ( ! $hasUserBeenUpdated )
        {

            return false;

        }

        // send email
        $emailSender = new EmailSender();
        $email_path = getcwd() . DIRECTORY_SEPARATOR . '../src/Assets/email/templates/email_telegram_payment_successful.html';
        $htmlBody = file_get_contents($email_path);

        $htmlBody = str_replace('{{first_name}}', $userInfo['first_name'], $htmlBody);

            
        $emailSenderStatus = $emailSender->sendHTMLEmail($email, 'Payment Successful', $htmlBody);

        if (isset($emailSenderStatus) && isset($emailSenderStatus['status']) && $emailSenderStatus['status'] !== 200) {
           return false;
        }

        return true;


    }

 

    // If there are robots, handle them
    if (!empty($robots)) {
        $userPaymentsModel->createUserPayment($user['user_id'], $paymentIntent->id, 'robot');
    }

    // Send confirmation email
    $emailSender = new EmailSender();
    $email_path = getcwd() . DIRECTORY_SEPARATOR . '../src/Assets/email/templates/email_payment_successful.html';
    $htmlBody = file_get_contents($email_path);

    $confirmUrl = $_ENV['APPLICATION_FRONTEND_URL'] . "/dashboard/";
    $htmlBody = str_replace('{{url}}', $confirmUrl, $htmlBody);
    $htmlBody = str_replace('{{first_name}}', $user['first_name'], $htmlBody);
    $htmlBody = str_replace('{{vendor}}', 'Stripe', $htmlBody);
    $htmlBody = str_replace('{{card_last4}}', $paymentData['card_last4'], $htmlBody);
    $htmlBody = str_replace('{{payment_id}}', $paymentIntent->id, $htmlBody);
    $htmlBody = str_replace('{{amount}}', $paymentData['amount'], $htmlBody);
    $htmlBody = str_replace('{{currency}}', $paymentData['currency'], $htmlBody);

    // Generate courses section if any
    $coursesSection = '';
    if (!empty($courses)) {
        $coursesSection = '<h2>Courses Purchased</h2><table class="courses-table"><tr><th>Course ID</th><th>Course Name</th></tr>';
        foreach ($courses as $course) {
            $coursesSection .= '<tr><td>' . htmlspecialchars($course['slug']) . '</td><td>' . htmlspecialchars($course['title']) . '</td></tr>';
        }
        $coursesSection .= '</table>';
    }
    $htmlBody = str_replace('{{courses_section}}', $coursesSection, $htmlBody);

    // Generate robots section if any
    $robotsSection = '';
    if (!empty($robots)) {
        $robotsSection = '<h2>Robots Purchased</h2><table class="robots-table"><tr><th>Robot ID</th><th>Robot Name</th></tr>';
        foreach ($robots as $robot) {
            $robotsSection .= '<tr><td>' . htmlspecialchars($robot['slug']) . '</td><td>' . htmlspecialchars($robot['title']) . '</td></tr>';
        }
        $robotsSection .= '</table>';
    }
    $htmlBody = str_replace('{{robots_section}}', $robotsSection, $htmlBody);

    $emailSenderStatus = $emailSender->sendHTMLEmail($email, 'Payment Successful', $htmlBody);

    if (isset($emailSenderStatus) && isset($emailSenderStatus['status']) && $emailSenderStatus['status'] !== 200) {
        return false;
    }

    // Clear the user's cart
    $cartModel = new Cart($database);
    $cartModel->updateCart(['products' => json_encode([])], $user['user_id']);

    return true;
}



};
