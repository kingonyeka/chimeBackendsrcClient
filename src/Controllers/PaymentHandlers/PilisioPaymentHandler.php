<?php


namespace App\Controllers\PaymentHandlers;

use App\Controllers\EmailSender;
use App\Database\Database;
use App\Helpers\JsonResponder;
use App\Models\Cart;
use App\Models\Payment;
use App\Models\User;
use App\Models\UserCourse;
use App\Models\UserPayment;
use App\Models\UserRobot;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use GuzzleHttp\Client;

class PilisioPaymentHandler
{
    private $client;
    private $controller;
    private $logger;
    private $secretKey; // Add your secret key here

    public function __construct()
    {

        // Set your Plisio secret key
        // $this->secretKey = $_ENV['SECRET_KEY']; // Replace with your actual secret key
        $this->secretKey = "xT5jhJ_gDFPawotdNbXnQHP2wbHzVtsfiDKzDBJhKolhoFXVrmF-koAdA4_Y8FzB"; // Replace with your actual secret key
    }

    public function initialize(Request $request, Response $response, $args): Response
    {
        $frontendBaseURL = $_ENV['APPLICATION_FRONTEND_URL'];
        $backendBaseURL = $_ENV['APPLICATION_BACKEND_URL'];

        $params = (array)$request->getParsedBody();
        $amount = $params['amount'] ?? 0;
        $currency = $params['currency'] ?? 'USD';
        $orderNumber = uniqid();
        $email = $params['email'] ?? '';
        $orderName = $params['description'] ?? 'Order-' . $orderNumber;
        $callbackUrl = $backendBaseURL . "/payments/pilisio/callback?email=".$email;

        $metaData = $params['metadata'] ?? [];
        $metaData = json_decode($metaData, true) ?? [];

        $telegram = $metaData['telegram'] ?? null;

        if ( $telegram && is_array($telegram) )
       {
            $callbackUrl .= '&type=telegram';
       }


        $invoiceData = $this->initializePayment($amount, $currency, $orderNumber, $email, $orderName, $callbackUrl, $this->secretKey);

        if (isset($invoiceData['error'])) {
            $response->getBody()->write(json_encode([
                'error' => $invoiceData['error'],
                'message' => $invoiceData['error'],
                'code' => 400,
                'data' => null
            ]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        $requestData = [
            'source_currency' => $currency,
            'source_amount' => $amount,
            'order_number' => $orderNumber,
            'currency' => 'USDT',
            'email' => $email,
            'order_name' => $orderName,
            'callback_url' => $callbackUrl,
            'api_key' => $this->secretKey,
            'expire_min' => 15
        ];

        $response->getBody()->write(json_encode(
            [
                'invoice_url' => $invoiceData['invoice_url'],
            ]
        ));
        return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
    }

    private function initializePayment($amount, $currency, $orderNumber, $email, $orderName, $callbackUrl, $apiKey)
    {
        $url = 'https://api.plisio.net/api/v1/invoices/new';

        $queryParams = http_build_query([
            'source_currency' => $currency,
            'source_amount' => $amount,
            'order_number' => $orderNumber,
            'currency' => 'USDT',
            'email' => $email,
            'order_name' => $orderName,
            'callback_url' => $callbackUrl,
            'api_key' => $apiKey,
            'expire_min' => 15
        ]);

        $finalUrl = $url . '?' . $queryParams;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $finalUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
            curl_close($ch);
            return ['error' => $error_msg];
        }
        curl_close($ch);

        $response_data = json_decode($response, true);

        if ($response_data['status'] !== 'success') {
            return ['error' => $response_data['data']['message']];
        }

        return $response_data['data'];
    }

    // public function callback(Request $request, Response $response, $args): Response
    // {
    //     $postData = (array)$request->getParsedBody();

    //     if ($this->verifyCallbackData($postData, $this->secretKey)) {
    //         // Callback data is valid
    //         // Perform database changes or other operations here
    //         if ($postData['status'] == 'completed') {
    //             // Payment completed
    //             // Update your database to mark the payment as completed
    //         } elseif ($postData['status'] == 'pending') {
    //             // Payment pending
    //             // Update your database to mark the payment as pending
    //         }
    //         // Add other status checks as needed

    //         $response->getBody()->write(json_encode(['message' => 'Success']));
    //         return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
    //     }

    //     // Callback data is invalid
    //     $response->getBody()->write(json_encode(['message' => 'Invalid callback data']));
    //     return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
    // }

    // private function verifyCallbackData($postData, $secretKey)
    // {
    //     if (!isset($postData['verify_hash'])) {
    //         return false;
    //     }

    //     $verifyHash = $postData['verify_hash'];
    //     unset($postData['verify_hash']);
    //     ksort($postData);

    //     if (isset($postData['expire_utc'])) {
    //         $postData['expire_utc'] = (string)$postData['expire_utc'];
    //     }
    //     if (isset($postData['tx_urls'])) {
    //         $postData['tx_urls'] = html_entity_decode($postData['tx_urls']);
    //     }

    //     $postString = serialize($postData);
    //     $checkKey = hash_hmac('sha1', $postString, $secretKey);

    //     return $checkKey === $verifyHash;
    // }

    public function callback(Request $request, Response $response, $args): Response
    {
        $postData = (array)$request->getParsedBody();

       

        // Callback data is valid
        // Perform database changes or other operations here
        if ( ! $this->processPaymentStatus($postData) )
        {
            return $this->createJsonResponse($response, ['message' => 'failed to process payment'], 500);
        }

        return $this->createJsonResponse($response, ['message' => 'Success'], 200);
    }

   

    private function processPaymentStatus($postData)
    {
        switch ($postData['status']) {
            case 'completed':
                // Payment completed
                // Update your database to mark the payment as completed
                $this->handleDBProcess($postData);
                break;
            case 'mismatch':
                // Payment completed
                // Update your database to mark the payment as completed
                $this->handleDBProcess($postData);
                break;
            case 'pending':
                // Payment pending
                // Update your database to mark the payment as pending
                break;
            // Add other status checks as needed
        }
    }

    private function createJsonResponse(Response $response, array $data, int $status): Response
    {
        $response->getBody()->write(json_encode($data));
        return $response->withStatus($status)->withHeader('Content-Type', 'application/json');
    }

    private function handleDBProcess($requestData)  {
        // var_dump($requestData); exit;
        $BaseURL = $_ENV['APPLICATION_FRONTEND_URL'];
      
        $email = $requestData['order_name'] ?? null;
        $type = $requestData['type'] ?? null;
  
        $txn_id = $requestData['txn_id'] ?? null;

        if ( $type === "telegram" )
        {

            // Initialize the database and models
            $database = new Database();
            $userModel = new User($database);

            $userInfo = $userModel->fetchUserInfo($email);
            
            

            $BaseURL = $_ENV['APPLICATION_FRONTEND_URL'];

            if ( ! $userInfo )
            {

                header("Location: ${BaseURL}/checkout?status=failed&message=payment%20was%20successful%20but%20the%20user%20does%20not%20exist1");
                exit;

            }

            $hasUserBeenUpdated = $userModel->updateUser([
                'joined_telegram' => 1
            ], $userInfo['user_id']);

            if ( ! $hasUserBeenUpdated )
            {

                header("Location: ${BaseURL}/checkout?status=failed&message=payment%20was%20successful%20but%20the%20user%20info%20was%20not%20updated");
                exit;

            }

            // send email
            $emailSender = new EmailSender();
            $email_path = getcwd() . DIRECTORY_SEPARATOR . '../src/Assets/email/templates/email_telegram_payment_successful.html';
            $htmlBody = file_get_contents($email_path);

            $htmlBody = str_replace('{{first_name}}', $userInfo['first_name'], $htmlBody);

                
            $emailSenderStatus = $emailSender->sendHTMLEmail($email, 'Payment Successful', $htmlBody);

            if (isset($emailSenderStatus) && isset($emailSenderStatus['status']) && $emailSenderStatus['status'] !== 200) {
                header("Location: ${BaseURL}/checkout?status=failed&message=successful%20transaction%20but%20failed%20to%20send%20confirmation%20email");
                exit;
            }

            // Redirect on success
            header("Location: ${BaseURL}/checkout?status=success&message=payment%20verified%20and%20processed%20successfully");
            exit;


        }

        if ( $txn_id )
        {

            // Initialize the database and models
            $database = new Database();
            $userModel = new User($database);
            $cartModel = new Cart($database);

            $userInfo = $userModel->fetchUserInfo($email);

            // conver ['data']['tx_id'] to date since it is in "expire_utc": 1597175132
            $expire_utc = $requestData['expire_utc'] ?? null;
            $expire_utc = date("Y-m-d H:i:s", $expire_utc);

            $amount = $requestData['source_amount'] ?? null;
            $currency = $requestData['source_currency'] ?? null;
            $paymentChannel =  'plisio' ?? null;
            $paymentProvider = 'plisio';
            $authorizationCode = $requestData['txn_id'] ?? null;
            $cardType = 'crypto';
            $cardLast4 = 'plisio';
            $bank = 'plisio';
            $createdAt = $expire_utc;

            if ( ! $userInfo )
            {

                header("Location: ${BaseURL}/checkout?status=failed&message=payment%20was%20successful%20but%20the%20user%20does%20not%20exist2");
                exit;

            }

            $paymentModel = new Payment($database);

            // Create the payment entry with the correct user_id
            $hasPaymentBeenAdded = $paymentModel->createPayment([
                'payment_id' => $txn_id,
                'paid_at' => $expire_utc,
                'amount' => $amount,
                'currency' => $currency,
                'payment_provider' => $paymentProvider,
                'payment_channel' => $paymentChannel,
                'authorization_code' => $authorizationCode,
                'card_type' => $cardType,
                'card_last4' => $cardLast4,
                'bank' => $bank,
                'created_at' => $createdAt,
                'user_id'  => $userInfo['user_id']  
            ]);


            if (!$hasPaymentBeenAdded) {
                header("Location: ${BaseURL}/checkout?status=failed&message=payment%20successful%20but%20failed%20to%20record%20payment");
                exit;
            }

            $cartModel = new Cart($database);

            $cartItems = $cartModel->fetchCartByUserId($userInfo['user_id']);

            $cartProducts = $cartItems['products'] ? json_decode($cartItems['products'], true) : [];

            $courses = [];
            $robots = [];


            foreach ($cartProducts as $key => $cartItem) {
                
                $type = $cartItem['type'];
                
                if ( $type === 'course' )
                {
                    array_push($courses, $cartItem);
                }
                else if ( $type === 'robot' )
                {
                    array_push($robots, $cartItem);
                }

            }



            // Handle user payments and courses
            $userPaymentsModel = new UserPayment($database);

            // If there are courses, handle them
            if (!empty($courses)) {
                

                $this->handleCourses($userInfo['user_id'], $courses, $userPaymentsModel);
                // Create the initial user payment entry
                $userPaymentsModel->createUserPayment($userInfo['user_id'], $txn_id, 'course');

            }

            // If there are robots, handle them
            if (!empty($robots)) {
                $this->handleRobots($userInfo['user_id'], $robots, $userPaymentsModel);
                // Create the initial user payment entry
                $userPaymentsModel->createUserPayment($userInfo['user_id'], $txn_id, 'robot');
            }

            // Send confirmation email
            $emailSender = new EmailSender();
            $email_path = getcwd() . DIRECTORY_SEPARATOR . '../src/Assets/email/templates/email_payment_successful.html';
            $htmlBody = file_get_contents($email_path);

            $confirmUrl = $_ENV['APPLICATION_FRONTEND_URL'] . "/dashboard/";
            $htmlBody = str_replace('{{url}}', $confirmUrl, $htmlBody);
            $htmlBody = str_replace('{{first_name}}', $userInfo['first_name'], $htmlBody);
            $htmlBody = str_replace('{{vendor}}', $paymentProvider, $htmlBody);
            $htmlBody = str_replace('{{card_last4}}', $cardLast4, $htmlBody);
            $htmlBody = str_replace('{{payment_id}}', $txn_id, $htmlBody);
            $htmlBody = str_replace('{{amount}}', $amount, $htmlBody);
            $htmlBody = str_replace('{{currency}}', $currency, $htmlBody);

            // $coursesList = '';
            // foreach ($courses as $course) {
            //     $coursesList .= '<tr>';
            //     $coursesList .= '<td>' . htmlspecialchars($course['title']) . '</td>';
            //     $coursesList .= '<td>' . htmlspecialchars($course['price']) . '</td>';
            //     $coursesList .= '</tr>';
            // }
            // $htmlBody = str_replace('<!-- Loop through the courses and list them here -->', $coursesList, $htmlBody);

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
                header("Location: ${BaseURL}/checkout?status=failed&message=successful%20transaction%20but%20failed%20to%20send%20confirmation%20email");
                exit;
            }

            // $cartModel->deleteCart($user['user_id']);

            $cartModel->updateCart([
                'products' => json_encode([])
            ], $userInfo['user_id']);

            // Redirect on success
            header("Location: ${BaseURL}/checkout?status=success&message=payment%20verified%20and%20processed%20successfully");
            exit;


        }

    }


    private function handleCourses($userId, $courses, $userPaymentsModel)
    {
        $database = new Database();
        $userCoursesModel = new UserCourse($database);

        $userCourses = $userCoursesModel->getUserCourses($userId);

        if ($userCourses) {
            $existingCourses = json_decode($userCourses['courses'], true);
            $newCourses = [];

            // Loop through the new courses and check for duplicates
            foreach ($courses as $course) {
                $isDuplicate = false;
                foreach ($existingCourses as $existingCourse) {
                    if ($existingCourse['slug'] === $course['slug']) {
                        $isDuplicate = true;
                        break;
                    }
                }

                // Add to newCourses array if not a duplicate
                if (!$isDuplicate) {
                    $newCourses[] = $course;
                }
            }

            // Merge the existing courses with the new, non-duplicate courses
            $mergedCourses = array_merge($existingCourses, $newCourses);

            $totalCourses = count($mergedCourses);

            $userCoursesModel->updateUserCourses($userId, json_encode($mergedCourses));
        } else {
            $totalCourses = count($courses);
            $userCoursesModel->createUserCourse([
                'user_id' => $userId,
                'courses' => json_encode($courses)
            ]);
        }

        $userModel = new User($database);
        $userModel->updateUser([
            'courses_purchased' => $totalCourses,
            'date_last_modified' => date('Y-m-d H:i:s')
        ], $userId);
    }

    private function handleRobots($userId, $robots, $userPaymentsModel)
    {
        $database = new Database();
        $userRobotsModel = new UserRobot($database);

        $userRobots = $userRobotsModel->getUserRobots($userId);



        if ($userRobots) {
            $existingRobots = json_decode($userRobots['robots'], true);
            $newRobots = [];

            // Loop through the new robots and check for duplicates
            foreach ($robots as $robot) {
                $isDuplicate = false;
                foreach ($existingRobots as $existingRobot) {
                    if ($existingRobot['slug'] === $robot['slug']) {
                        $isDuplicate = true;
                        break;
                    }
                }

                // Add to newRobots array if not a duplicate
                if (!$isDuplicate) {
                    $newRobots[] = $robot;
                }
            }

            // Merge the existing robots with the new, non-duplicate robots
            $mergedRobots = array_merge($existingRobots, $newRobots);

            $totalRobots = count($mergedRobots);


            $userRobotsModel->updateUserRobots($userId, json_encode($mergedRobots));
        } else {
            $totalRobots = count($robots);
            $userRobotsModel->createUserRobot([
                'user_id' => $userId,
                'robots' => json_encode($robots)
            ]);
        }

        $userModel = new User($database);
        $userModel->updateUser([
            'robots_purchased' => $totalRobots,
            'date_last_modified' => date('Y-m-d H:i:s')
        ], $userId);
    }

}
