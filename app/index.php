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

// require_once './middlewares/Logger.php';

require_once './interfaces/IApiUse.php';
require_once './controllers/UserController.php';
require_once './controllers/ProductController.php';
require_once './controllers/TableController.php';
require_once './controllers/OrderController.php';


// Load ENV
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

// Instantiate App
$app = AppFactory::create();
$app->setBasePath('/app');

// Add error middleware
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


$app->group('/users', function (RouteCollectorProxy $group) {
    $group->get('[/]', UserController::class . ':GetAll');
    $group->get('/{id}', UserController::class . ':Get');
    $group->post('[/]', UserController::class . ':Add');
    $group->put('/{id}', UserController::class . '::Update');
    $group->delete('/{id}', UserController::class . '::Delete');
});

$app->group('/products', function (RouteCollectorProxy $group) {
    $group->get('[/]', ProductController::class . ':GetAll');
    $group->get('/{id}', ProductController::class . ':Get');
    $group->post('[/]', ProductController::class . ':Add');
    $group->put('/{id}', ProductController::class . ':Update');
    $group->delete('/{id}', ProductController::class . ':Delete');
    //$group->post('/load', ProductController::class . '::Load');
    //$group->get('/download', ProductController::class . '::Download');
});

$app->group('/tables', function (RouteCollectorProxy $group) {
    $group->get('[/]', TableController::class . ':GetAll');
    $group->get('/{id}', TableController::class . ':Get');
    $group->post('[/]', TableController::class . ':Add');
    $group->put('/{id}', TableController::class . ':Update');
    $group->delete('/{id}', TableController::class . ':Delete');
    //$group->get('/cuenta/{codigoPedido}', TableController::class . '::CuentaMesa')->add(\Autentificador::class . '::ValidarMozo');
    //$group->get('/cobrar/{codigoPedido}', TableController::class . '::CobrarMesa')->add(\Autentificador::class . '::ValidarMozo');
    //$group->get('/cerrar/{id}', TableController::class . '::CerrarMesa')->add(\Autentificador::class . '::ValidarSocio');
    //$group->get('/usos', TableController::class . '::UsosMesa')->add(\Autentificador::class . '::ValidarSocio');
});

$app->group('/orders', function (RouteCollectorProxy $group) {
    $group->get('[/]', OrderController::class . '::GetAll');
    $group->get('/{id}', OrderController::class . '::Get');
    $group->post('[/]', OrderController::class . '::Add');
    $group->put('[/{id}]', OrderController::class . '::Update');
    $group->delete('[/{id}]', OrderController::class . '::Delete');
    //$group->get('/listos', OrderController::class . '::TraerListos');
    //$group->get('/pendientes', OrderController::class . '::TraerPendientes');
    //$group->post('/inicio/{id}', OrderController::class . '::IniciarPedido');
    //$group->post('/final/{id}', OrderController::class . '::FinalizarPedido');
    //$group->post('/entregar/{id}', OrderController::class . '::EntregarPedido');
    //$group->get('/{codigoMesa}-{codigoPedido}', OrderController::class . '::TraerPedidosMesa');

});

$app->get('[/]', function (Request $request, Response $response) {    
    $payload = json_encode(array("mensaje" => "API Comandas funcionando."));
    
    $response->getBody()->write($payload);
    return $response->withHeader('Content-Type', 'application/json');
});

$app->run();
