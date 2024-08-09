<?php

namespace App\Controllers;

use App\Helpers\JsonResponder;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Controllers\PaymentHandlers\PaystackPaymentHandler;
use App\Controllers\PaymentHandlers\FlutterwavePaymentHandler;
use App\Controllers\PaymentHandlers\StripePaymentHandler;
use App\Database\Database;
use App\Models\Telegram;
use App\Models\User;

class TelegramController
{

    public function index(Request $request, Response $response, $args)
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Expose-Headers: ');
        header('Access-Control-Allow-Headers: *');

        $db = new Database();
        $telegramModel = new Telegram($db);

        $telegramInfo = $telegramModel->getTelegramInfo();

        return JsonResponder::generate($response, [
            'code' => 200,
            'message' => 'fetched successfully',
            'data' => $telegramInfo
        ], 200);


    }
    
    public function update(Request $request, Response $response, $args) {
        
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Expose-Headers: ');
        header('Access-Control-Allow-Headers: *');

        $parsedBody = $request->getParsedBody();
        
        $price = $parsedBody['price'] ?? null;
        $usd = $parsedBody['usd'] ?? null;

        $price = doubleval($price) ?? null;
        $usd = doubleval($usd) ?? null;

        if ( ! $price || ! $usd )
        {

            return JsonResponder::generate($response, ['code' => 401, 'message' => 'required feilds are empty'], 401);

        }

        $dbData = [
            'price' => $price,
            'usd' => $usd
        ];

        $db = new Database();
        $telegramModel = new Telegram($db);

        $updateStatus = $telegramModel->updateTelegram($dbData);

        if ( ! $updateStatus )
        {
            return JsonResponder::generate($response, ['code' => 401, 'message' => 'failed to update'], 401);

        }

        return JsonResponder::generate($response, ['code' => 200, 'message' => 'updated successfully'], 200);


    }


}
