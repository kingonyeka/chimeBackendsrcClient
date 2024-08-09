<?php

namespace App\Middleware;

use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use Slim\Psr7\Response as Psr7Response;
use Slim\Psr7\Factory\StreamFactory;

class JwtMiddleware implements MiddlewareInterface {
    private $jwtSecret;

    public function __construct($jwtSecret) {
        $this->jwtSecret = $jwtSecret;
    }

    public function generateToken($data, int $time = 3600) {
        $payload = [
            'data' => $data,
            'iat' => time(),
            'exp' => time() + $time // Token expires based on the provided time
        ];
    
        try {
            return JWT::encode($payload, $this->jwtSecret, 'HS256');
        } catch (Exception $e) {
            // Log the error or handle accordingly
            return null;
        }
    }
    

    public function generateRefreshToken($userId) {
        $refreshTokenPayload = [
            'userId' => $userId,
            'iat' => time(),
            'exp' => time() + intval($_ENV['REFRESH_TOKEN_EXPIRY']) * intval($_ENV['REFRESH_TOKEN_EXPIRY'])
        ];

        return JWT::encode($refreshTokenPayload, $this->jwtSecret, 'HS256');
    }

    public function decodeToken($token) {
        try {
            // Ensure the key is passed as a string
            if (!is_string($this->jwtSecret)) {
                throw new \Exception("Invalid key format");
            }

            // Decode the token using the JWT library
            return JWT::decode($token, new Key($this->jwtSecret, 'HS256'));

        } catch (SignatureInvalidException $e) {
            // Token signature is invalid
            return ['error' => 'invalid token signature'];

        } catch (ExpiredException $e) {
            // Token has expired
            return ['error' => 'token has expired'];

        } catch (Exception $e) {
            // var_dump($e->getMessage());
            // Other errors (e.g., token format issues)
            return ['error' => 'invalid token'];
        }
    }

    public function process(Request $request, RequestHandlerInterface $handler): Response {
        // Get the Authorization header from the request
        $authHeader = $request->getHeaderLine('Authorization');
    
        // Check if the Authorization header is missing or not in the correct format
        if (!$authHeader || !preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            // Create a new response with JSON body and status code 401
            return $this->createJsonResponse(401, 'Authorization header required');
        }
    
        // Extract the JWT token from the Authorization header
        $token = $matches[1];
    
        try {
            // Decode the JWT token using the JWT library
            $decoded = $this->decodeToken($token);
    
            // Check if the decoded token contains an error
            if (isset($decoded->error)) {
                throw new \Exception($decoded->error);
            }
    
            // Add the decoded token to the request attributes
            $request = $request->withAttribute('decoded', $decoded);
        } catch (SignatureInvalidException $e) {
            // Handle invalid signature
            return $this->createJsonResponse(401, 'Invalid token signature');
        } catch (ExpiredException $e) {
            // Handle expired token
            return $this->createJsonResponse(401, 'Token has expired');
        } catch (\Exception $e) {
            // Handle other exceptions
            return $this->createJsonResponse(401, 'Invalid token');
        }
    
        // If the token is valid, pass the request to the next middleware or handler
        return $handler->handle($request);
    }
    

    // Helper method to create a JSON response with error code and message
    private function createJsonResponse(int $statusCode, string $message): Response {
        $data = ['code' => $statusCode, 'message' => $message];
        $response = new Psr7Response();
        $streamFactory = new StreamFactory();
        $body = $streamFactory->createStream(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json')
                        ->withStatus($statusCode)
                        ->withBody($body);
    }
}
