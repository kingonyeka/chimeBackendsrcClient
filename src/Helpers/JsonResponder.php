<?php

namespace App\Helpers;

use Slim\Psr7\Response;

class JsonResponder {
    
    static function generate(Response $response, $data, $statusCode = 200): Response {
        // return urlencode($value);
        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json')->withStatus($statusCode);
    }

}
