<?php

// namespace App\Middleware;

// use Psr\Http\Message\ResponseInterface as Response;
// use Psr\Http\Message\ServerRequestInterface as Request;

// class CorsMiddleware
// {
//     public function __invoke(Request $request, Response $response, callable $next): Response
//     {
//         var_dump("Hello");

//         // Add CORS headers to the response
//         $response = $next($request, $response)
//             ->withHeader('Access-Control-Allow-Origin', '*')
//             ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization')
//             ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');

//         // Return the modified response
//         return $response;
//     }
// }

namespace App\Middleware;

use Slim\Psr7\Response; // Import Slim's Response class
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteRunner;

class CorsMiddleware
{
    private $routeRunner;

    public function __construct(RouteRunner $routeRunner)
    {
        $this->routeRunner = $routeRunner;
    }

    public function __invoke(Request $request, Response $response, callable $next)
    {
        // Pass the request to Slim's route runner to get the response
        $response = $this->routeRunner->handle($request);

        // Add CORS headers to the response
        $response = $response->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');

        return $response;
    }
}