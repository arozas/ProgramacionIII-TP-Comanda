<?php
// Error Handling
error_reporting(-1);
ini_set('display_errors', 1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;
use Slim\Routing\RouteContext;

require __DIR__ . '/../vendor/autoload.php';

require_once './middlewares/Logger.php';
require_once './middlewares/AuthMiddleware.php';
require_once './middlewares/Validator.php';

require_once './interfaces/IApiUse.php';
require_once './controllers/UserController.php';
require_once './controllers/ProductController.php';
require_once './controllers/TableController.php';
require_once './controllers/OrderController.php';

// Set Timezone
date_default_timezone_set('America/Argentina/Buenos_Aires');

// Load ENV
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

// Instantiate App
$app = AppFactory::create();
$app->setBasePath('/app');

// Add error handler middleware
$errorMiddleware = function ($request, $exception, $displayErrorDetails) use ($app) {
    $statusCode = 500;
    $errorMessage = $exception->getMessage();
    $response = $app->getResponseFactory()->createResponse($statusCode);
    $response->getBody()->write(json_encode(['error' => $errorMessage]));

    return $response->withHeader('Content-Type', 'application/json');
};

$app->addErrorMiddleware(true, true, true)
    ->setDefaultErrorHandler($errorMiddleware);

// Add parse body
$app->addBodyParsingMiddleware();


//ABM.
$app->group('/users', function (RouteCollectorProxy $group) {
    $group->get('[/]', UserController::class . ':GetAll')->add(new AuthMiddleware());
    $group->get('/{id}', UserController::class . ':Get')->add(new AuthMiddleware());
    $group->post('[/]', UserController::class . ':Add')->add(new AuthMiddleware())->add(Validator::class . '::NewUserValidation');
    $group->put('/{id}', UserController::class . '::Update')->add(new AuthMiddleware());
    $group->delete('/{id}', UserController::class . '::Delete')->add(new AuthMiddleware());
});

$app->group('/products', function (RouteCollectorProxy $group) {
    $group->get('[/]', ProductController::class . ':GetAll')->add(new AuthMiddleware());
    $group->get('/{id}', ProductController::class . ':Get')->add(new AuthMiddleware());
    $group->get('/download/', ProductController::class . ':DownloadFile')->add(new AuthMiddleware());
    $group->post('[/]', ProductController::class . ':Add')->add(new AuthMiddleware());
    $group->post('/load/', ProductController::class . ':LoadFile')->add(new AuthMiddleware());
    $group->put('/{id}', ProductController::class . ':Update')->add(new AuthMiddleware());
    $group->delete('/{id}', ProductController::class . ':Delete')->add(new AuthMiddleware());
});

$app->group('/tables', function (RouteCollectorProxy $group) {
    $group->get('[/]', TableController::class . ':GetAll')->add(new AuthMiddleware());
    $group->get('/{id}', TableController::class . ':Get')->add(new AuthMiddleware());
    $group->get('/manageBill/{orderId}', TableController::class . ':ManageBill')->add(new AuthMiddleware());
    $group->post('[/]', TableController::class . ':Add')->add(new AuthMiddleware());
    $group->put('/{id}', TableController::class . ':Update')->add(new AuthMiddleware());
    $group->delete('/{id}', TableController::class . ':Delete')->add(new AuthMiddleware());
});

$app->group('/orders', function (RouteCollectorProxy $group) {
    $group->get('[/]', OrderController::class . '::GetAll')->add(new AuthMiddleware());
    $group->get('/{id}', OrderController::class . '::Get')->add(new AuthMiddleware());
    $group->get('/{mesaId}/{orderId}', OrderController::class . '::GetTableOrders');
    $group->post('[/]', OrderController::class . '::Add')->add(new AuthMiddleware());
    $group->put('/{id}', OrderController::class . '::Update')->add(new AuthMiddleware());
    $group->put('/prepare/{id}', OrderController::class . '::PrepareOrder')->add(new AuthMiddleware());
    $group->put('/completed/{id}', OrderController::class . '::CompletedOrder')->add(new AuthMiddleware());
    $group->delete('/{id}', OrderController::class . '::Delete')->add(new AuthMiddleware());
});

$app->group('/surveys', function (RouteCollectorProxy $group) {
    $group->get('[/]', SurveyController::class . '::GetAll')->add(new AuthMiddleware());
    $group->get('/{id}', SurveyController::class . '::Get')->add(new AuthMiddleware());
    $group->post('[/]', SurveyController::class . '::Add');
});

// LOG IN
$app->group('/login', function (RouteCollectorProxy $group) {
    $group->post('[/]', UserController::class . '::LogIn')->add(Logger::class . '::LoginValidation');
});

$app->add(Logger::class . '::UserLogger');

// API RUN CONFIRMATION
$app->get('[/]', function (Request $request, Response $response) {    
    $payload = json_encode(array("mensaje" => "API Comandas funcionando."));
    
    $response->getBody()->write($payload);
    return $response->withHeader('Content-Type', 'application/json');
});

$app->run();
