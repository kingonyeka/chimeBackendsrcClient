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

class TelegramPaystackPaymentHandler
{
    private $paystackPaymentUrl;
    private $paystackSecretKey;

    public function __construct()
    {
        // Fetch the predefined Paystack payment URL from environment variables
        $this->paystackPaymentUrl = $_ENV['PAYSTACK_TELEGRAM_PAYMENT_URL'];

        // Fetch the Paystack secret key from environment variables
        $this->paystackSecretKey = $_ENV['PAYSTACK_SECRET_KEY'];

    }

    public function startPayment(Request $request, Response $response, $args)
    {
        // Return the predefined Paystack payment URL directly
        return JsonResponder::generate($response, [
            'code' => 200,
            'message' => 'Payment URL for Paystack',
            'data' => [
                'payment_url' => $this->paystackPaymentUrl
            ]
        ]);
    }


   

    public function processCallback(Request $request, Response $response, $args)
    {

        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Expose-Headers: ');
        header('Access-Control-Allow-Headers: *');

        // Get the transaction reference from the callback URL parameters
        $trxRef = $request->getQueryParams()['reference'] ?? null;

        $BaseURL = $_ENV['APPLICATION_FRONTEND_URL'];

        if (!$trxRef) {
            header("Location: ${BaseURL}/checkout?status=failed&message=payment%20failed%20or%20was%20not%20completed");
            exit;
        }

        // Verify the transaction with Paystack API
        $verificationResult = $this->verifyPaystackTransaction($trxRef);

        // echo "<pre>";
        // var_dump($verificationResult);
        // echo "</pre>";

        // exit;

        if ($verificationResult && isset($verificationResult['status']) && $verificationResult['status']) {
            // Paystack API responded, check the transaction status
            $transactionStatus = $verificationResult['data']['status'];
            if ($transactionStatus === 'success') {
                // Transaction was successful
                $paymentId = $verificationResult['data']['reference'];
                $paidAt = $verificationResult['data']['paid_at'];
                $amount = $verificationResult['data']['amount'] / 100;
                $currency = $verificationResult['data']['currency'];
                $paymentProvider = 'paystack';
                $paymentChannel = $verificationResult['data']['channel'];
                $authorizationCode = $verificationResult['data']['authorization']['authorization_code'];
                $cardType = $verificationResult['data']['authorization']['card_type'];
                $cardLast4 = $verificationResult['data']['authorization']['last4'];
                $bank = $verificationResult['data']['authorization']['bank'];
                $createdAt = $verificationResult['data']['created_at'];
                $ipAddress = $verificationResult['data']['ip_address'];
                $courses = $verificationResult['data']['metadata']['courses'] ?? [];
                $robots = $verificationResult['data']['metadata']['robots'] ?? [];
                $transactionType = $verificationResult['data']['metadata']['type'] ?? 'course';
                $email = $verificationResult['data']['customer']['email'];

                // Initialize the database and models
                $database = new Database();
                $userModel = new User($database);
                $paymentModel = new Payment($database);

                // Fetch user information
                $user = $userModel->fetchUserInfo($email);

                if (!$user) {
                    header("Location: ${BaseURL}/checkout?status=failed&message=payment%20was%20successful%20but%20the%20user%20does%20not%20exist");
                    exit;
                }

                // Create the payment entry with the correct user_id
                $hasPaymentBeenAdded = $paymentModel->createPayment([
                    'payment_id' => $paymentId,
                    'paid_at' => $paidAt,
                    'amount' => $amount,
                    'currency' => $currency,
                    'payment_provider' => $paymentProvider,
                    'payment_channel' => $paymentChannel,
                    'authorization_code' => $authorizationCode,
                    'card_type' => $cardType,
                    'card_last4' => $cardLast4,
                    'bank' => $bank,
                    'created_at' => $createdAt,
                    'user_id'  => $user['user_id']  // Use the email as user_id if that is how your DB schema is set
                ]);

                if (!$hasPaymentBeenAdded) {
                    header("Location: ${BaseURL}/checkout?status=failed&message=payment%20successful%20but%20failed%20to%20record%20payment");
                    exit;
                }

                // update user details for telegram

                $hasUserBeenUpdated = $userModel->updateUser([
                    'joined_telegram' => true
                ], $user['user_id']);

                if (!$hasUserBeenUpdated) {
                    header("Location: ${BaseURL}/checkout?status=failed&message=payment%20successful%20but%20failed%20to%20record%20payment");
                    exit;
                }
                
                // Send confirmation email
                $emailSender = new EmailSender();
                $email_path = getcwd() . DIRECTORY_SEPARATOR . '../src/Assets/email/templates/email_telegram_payment_successful.html';
                $htmlBody = file_get_contents($email_path);

                $htmlBody = str_replace('{{url}}', $this->paystackPaymentUrl, $htmlBody);
                $htmlBody = str_replace('{{first_name}}', $user['first_name'], $htmlBody);
                

                $emailSenderStatus = $emailSender->sendHTMLEmail($email, 'Payment Successful', $htmlBody);

                if (isset($emailSenderStatus) && isset($emailSenderStatus['status']) && $emailSenderStatus['status'] !== 200) {
                    header("Location: ${BaseURL}/checkout?status=failed&message=successful%20transaction%20but%20failed%20to%20send%20confirmation%20email");
                    exit;
                }

              
                // Redirect on success
                header("Location: ${BaseURL}/checkout?status=success&message=payment%20verified%20and%20processed%20successfully");
                exit;

            } else {
                // Transaction was not successful (failed, abandoned, etc.)
                return JsonResponder::generate($response, [
                    'code' => 400,
                    'message' => 'transaction failed or was not completed',
                    'data' => $verificationResult
                ], 400);
            }
        } else {
            // Transaction verification failed, possibly a network issue or invalid response
            return JsonResponder::generate($response, [
                'code' => 500,
                'message' => 'Failed to verify payment transaction',
                'data' => $verificationResult
            ], 500);
        }
    }



    

    private function verifyPaystackTransaction($trxRef)
    {
        // Paystack API endpoint for transaction verification
        $url = "https://api.paystack.co/transaction/verify/$trxRef";

        // Initialize cURL session
        $ch = curl_init();

        // Set cURL options
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer " . $this->paystackSecretKey,
            "Cache-Control: no-cache"
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Execute the cURL request
        $result = curl_exec($ch);

        // Close cURL session
        curl_close($ch);

        // Decode the JSON response
        $decodedResult = json_decode($result, true);

        // Return the decoded response
        return $decodedResult;
    }

}
