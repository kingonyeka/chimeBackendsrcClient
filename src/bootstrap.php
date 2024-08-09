<?php

use App\Middleware\CorsMiddleware;
use Slim\Factory\AppFactory;
use Selective\BasePath\BasePathMiddleware;
use DI\Container;
use Dotenv\Dotenv;
use Slim\Exception\HttpMethodNotAllowedException;
use Slim\Exception\HttpNotFoundException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Routing\RouteContext;

date_default_timezone_set('Africa/Lagos');

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$container = new Container();

$settings = require __DIR__ . '/settings.php';
foreach ($settings as $key => $value) {
    $container->set($key, $value);
}

$container->set('App\Controllers\PasswordResetController', function ($container) {
    return new App\Controllers\PasswordResetController($container->get('settings')['jwt']['secret']);
});

$container->set('App\Controllers\Admins\AdminAuthController', function ($container) {
    return new App\Controllers\Admins\AdminAuthController($container->get('settings')['jwt']['secret']);
});

$container->set('App\Controllers\Admins\AdminUsersController', function ($container) {
    return new App\Controllers\Admins\AdminUsersController($container->get('settings')['jwt']['secret']);
});

$container->set('App\Controllers\Admins\AdminCoursesController', function ($container) {
    return new App\Controllers\Admins\AdminCoursesController($container->get('settings')['jwt']['secret']);
});

$container->set('App\Controllers\Admins\AdminCategoryController', function ($container) {
    return new App\Controllers\Admins\AdminCategoryController($container->get('settings')['jwt']['secret']);
});

$container->set('App\Controllers\Admins\AdminRobotsController', function ($container) {
    return new App\Controllers\Admins\AdminRobotsController($container->get('settings')['jwt']['secret']);
});

$container->set('App\Controllers\Admins\AdminPaymentController', function ($container) {
    return new App\Controllers\Admins\AdminPaymentController($container->get('settings')['jwt']['secret']);
});

$container->set('App\Controllers\AuthController', function ($container) {
    return new App\Controllers\AuthController($container->get('settings')['jwt']['secret']);
});


$logger = new Logger('app_logger');
$logger->pushHandler(new StreamHandler(__DIR__ . '/../logs/app.log', Logger::DEBUG));
$container->set(Logger::class, $logger);

AppFactory::setContainer($container);
$app = AppFactory::create();

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Expose-Headers: ');
    header('Access-Control-Allow-Headers: *');
     exit;
 }


$app->addBodyParsingMiddleware();

$app->add(function (Request $request, RequestHandlerInterface $handler): Response {
    $routeContext = RouteContext::fromRequest($request);
    $routingResults = $routeContext->getRoutingResults();
    $methods = $routingResults->getAllowedMethods();
    $requestHeaders = $request->getHeaderLine('Access-Control-Request-Headers');

    $response = $handler->handle($request);

    $response = $response->withHeader('Access-Control-Allow-Origin', '*');
    $response = $response->withHeader('Access-Control-Allow-Methods', implode(',', $methods));
    $response = $response->withHeader('Access-Control-Allow-Headers', $requestHeaders);

    return $response;
});

$app->options('/{routes:.+}', function (Request $request, Response $response) {
    return $response->withHeader('Access-Control-Allow-Origin', '*')
                    ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS')
                    ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization');
});

$app->addRoutingMiddleware();
$app->add(new BasePathMiddleware($app));

(require __DIR__ . '/routes.php')($app);

$errorMiddleware = $app->addErrorMiddleware(true, true, true);

$errorMiddleware->setErrorHandler(HttpNotFoundException::class, function (Request $request, HttpNotFoundException $exception, bool $displayErrorDetails) use ($app, $logger) {
    $responseData = [
        'error' => 'Not Found',
        'message' => 'The requested resource was not found.',
    ];
    $response = $app->getResponseFactory()->createResponse();
    $response->getBody()->write(json_encode($responseData));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
});

$errorMiddleware->setErrorHandler(HttpMethodNotAllowedException::class, function (Request $request, HttpMethodNotAllowedException $exception, bool $displayErrorDetails) use ($app, $logger) {
    $referrer = $request->getHeaderLine('Referer');
    $ipAddress = $request->getServerParams()['REMOTE_ADDR'] ?? '';
    $userAgent = $request->getHeaderLine('User-Agent');
    $url = (string)$request->getUri();

    $logger->error($exception->getMessage(), [
        'referrer' => $referrer,
        'ip_address' => $ipAddress,
        'user_agent' => $userAgent,
        'url' => $url,
    ]);

    $responseData = [
        'error' => 'Method Not Allowed',
        'message' => 'The request method is not allowed for this endpoint.',
    ];
    $response = $app->getResponseFactory()->createResponse();
    $response->getBody()->write(json_encode($responseData));

    return $response->withHeader('Content-Type', 'application/json')->withStatus(405);
});

$errorMiddleware->setErrorHandler(RuntimeException::class, function (Request $request, RuntimeException $exception, bool $displayErrorDetails) use ($app, $logger) {

    $referrer = $request->getHeaderLine('Referer');
    $ipAddress = $request->getServerParams()['REMOTE_ADDR'] ?? '';
    $userAgent = $request->getHeaderLine('User-Agent');
    $url = (string)$request->getUri();

    $logger->error($exception->getMessage(), [
        'referrer' => $referrer,
        'ip_address' => $ipAddress,
        'user_agent' => $userAgent,
        'url' => $url,
    ]);


    $responseData = [
        'error' => 'Error Occured',
        'message' => "an error has occured. contact the admin",
    ];
    $response = $app->getResponseFactory()->createResponse();
    $response->getBody()->write(json_encode($responseData));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
});

$errorMiddleware->setErrorHandler(TypeError::class, function (Request $request, TypeError $exception, bool $displayErrorDetails) use ($app, $logger) {

    $referrer = $request->getHeaderLine('Referer');
    $ipAddress = $request->getServerParams()['REMOTE_ADDR'] ?? '';
    $userAgent = $request->getHeaderLine('User-Agent');
    $url = (string)$request->getUri();

    $logger->error($exception->getMessage(), [
        'referrer' => $referrer,
        'ip_address' => $ipAddress,
        'user_agent' => $userAgent,
        'url' => $url,
        'trace' => $exception->getTraceAsString()
    ]);


    $responseData = [
        'error' => 'Error Occured',
        'message' => "an error has occured. contact the admin",
    ];
    $response = $app->getResponseFactory()->createResponse();
    $response->getBody()->write(json_encode($responseData));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
});

$errorMiddleware->setErrorHandler(DI\Definition\Exception\InvalidDefinition::class, function (Request $request, DI\Definition\Exception\InvalidDefinition $exception, bool $displayErrorDetails) use ($app, $logger) {

    $referrer = $request->getHeaderLine('Referer');
    $ipAddress = $request->getServerParams()['REMOTE_ADDR'] ?? '';
    $userAgent = $request->getHeaderLine('User-Agent');
    $url = (string)$request->getUri();

    $logger->error($exception->getMessage(), [
        'referrer' => $referrer,
        'ip_address' => $ipAddress,
        'user_agent' => $userAgent,
        'url' => $url,
        'trace' => $exception->getTraceAsString()
    ]);


    $responseData = [
        'error' => 'Error Occured',
        'message' => "an error has occured. contact the admin",
    ];
    $response = $app->getResponseFactory()->createResponse();
    $response->getBody()->write(json_encode($responseData));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
});

$errorMiddleware->setErrorHandler(FastRoute\BadRouteException::class, function (Request $request, FastRoute\BadRouteException $exception, bool $displayErrorDetails) use ($app, $logger) {

    $referrer = $request->getHeaderLine('Referer');
    $ipAddress = $request->getServerParams()['REMOTE_ADDR'] ?? '';
    $userAgent = $request->getHeaderLine('User-Agent');
    $url = (string)$request->getUri();

    $logger->error($exception->getMessage(), [
        'referrer' => $referrer,
        'ip_address' => $ipAddress,
        'user_agent' => $userAgent,
        'url' => $url,
        'trace' => $exception->getTraceAsString()
    ]);


    $responseData = [
        'error' => 'Error Occured',
        'message' => "an error has occured. contact the admin",
    ];
    $response = $app->getResponseFactory()->createResponse();
    $response->getBody()->write(json_encode($responseData));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
});

$errorMiddleware->setErrorHandler(PDOException::class, function (Request $request, PDOException $exception, bool $displayErrorDetails) use ($app, $logger) {

    $referrer = $request->getHeaderLine('Referer');
    $ipAddress = $request->getServerParams()['REMOTE_ADDR'] ?? '';
    $userAgent = $request->getHeaderLine('User-Agent');
    $url = (string)$request->getUri();

    $logger->error($exception->getMessage(), [
        'referrer' => $referrer,
        'ip_address' => $ipAddress,
        'user_agent' => $userAgent,
        'url' => $url,
        'trace' => $exception->getTraceAsString()
    ]);


    $responseData = [
        'error' => 'Error Occured',
        'message' => "an error has occured. contact the admin",
    ];
    $response = $app->getResponseFactory()->createResponse();
    $response->getBody()->write(json_encode($responseData));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
});

$errorMiddleware->setErrorHandler(ExpiredException::class, function (Request $request, ExpiredException $exception, bool $displayErrorDetails) use ($app, $logger) {
    $logger->error($exception->getMessage());
    $responseData = [
        'error' => 'Unauthorized',
        'message' => 'Access token has expired.',
    ];
    $response = $app->getResponseFactory()->createResponse();
    $response->getBody()->write(json_encode($responseData));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
});

$errorMiddleware->setErrorHandler(SignatureInvalidException::class, function (Request $request, SignatureInvalidException $exception, bool $displayErrorDetails) use ($app, $logger) {
    $logger->error($exception->getMessage());
    $responseData = [
        'error' => 'Unauthorized',
        'message' => 'Invalid access token signature.',
    ];
    $response = $app->getResponseFactory()->createResponse();
    $response->getBody()->write(json_encode($responseData));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
});

return $app;
