<?php

namespace App\Controllers;

use App\Helpers\JsonResponder;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Controllers\PaymentHandlers\PaystackPaymentHandler;
use App\Controllers\PaymentHandlers\FlutterwavePaymentHandler;
use App\Controllers\PaymentHandlers\StripePaymentHandler;
use App\Database\Database;
use App\Models\User;

class PaymentController
{
    private $paymentVendors = [
        'paystack',
        'flutterwave',
    ];

    public function processPayment(Request $request, Response $response, $args)
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Expose-Headers: ');
        header('Access-Control-Allow-Headers: *');
        // Check if the request content type is JSON
        $contentType = $request->getHeaderLine('Content-Type');
        $isJsonRequest = strpos($contentType, 'application/json') !== false;
    
        if ($isJsonRequest) {
            // Parse JSON request body
            $jsonBody = $request->getBody()->getContents();
            $parsedBody = json_decode($jsonBody, true);
    
            // Validate JSON payload
            $requiredFields = ['amount', 'currency', 'email', 'metadata', 'vendor'];
            foreach ($requiredFields as $field) {
                if (!isset($parsedBody[$field])) {
                    return JsonResponder::generate($response, ['code' => 401, 'message' => "$field is required"], 401);
                }
            }
    
            $vendor = $parsedBody['vendor'];
        } else {
            // Parse form data request
            $parsedBody = $request->getParsedBody();

            // Validate form data payload
            $requiredFields = ['amount', 'currency', 'email', 'metadata', 'vendor'];
            foreach ($requiredFields as $field) {
                if (!isset($parsedBody[$field])) {
                    return JsonResponder::generate($response, ['code' => 401, 'message' => "$field is required"], 401);
                }
            }
    
            $vendor = $parsedBody['vendor'];
        }
    
        if (!in_array($vendor, $this->paymentVendors)) {
            return JsonResponder::generate($response, ['code' => 401, 'message' => 'Invalid payment vendor'], 401);
        }

        $metaData = $parsedBody['metadata'];

        $telegram = $metaData['telegram'] ?? null;

        if ( $telegram && is_array($telegram) )
        {

            $userEmail = $telegram['email'] ?? null;

            // Initialize the database and models
            $database = new Database();
            $userModel = new User($database);
    
            $userInfo = $userModel->fetchUserInfo($userEmail);
    
            if ( ! $userInfo )
            {
    
                return JsonResponder::generate($response, ['code' => 401, 'message' => 'User not found.'], 401);
    
            }
    
            $userTelegramStatus = $userInfo['joined_telegram'];
    
            if ( boolval($userTelegramStatus) != false )
            {
                return JsonResponder::generate($response, ['code' => 401, 'message' => 'You have already paid for telegram subscription.'], 401);
            }

        }


    
        // Delegate payment processing to the corresponding vendor class
        $vendorClass = '\\App\\Controllers\\PaymentHandlers\\' . ucfirst($vendor) . 'PaymentHandler';
        if (class_exists($vendorClass)) {
            $handler = new $vendorClass();
            return $handler->processPayment($response, $parsedBody);
        } else {
            return JsonResponder::generate($response, ['code' => 500, 'message' => 'Payment handler not found'], 500);
        }
    }
    


}
