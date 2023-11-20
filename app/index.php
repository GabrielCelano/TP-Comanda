<?php
// Error Handling
error_reporting(-1);
ini_set('display_errors', 1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;

require __DIR__ . '/../vendor/autoload.php';

require_once './controllers/UsuarioController.php';
require_once './controllers/ProductoController.php';
require_once './controllers/MesaController.php';
require_once './controllers/PedidoController.php';
require_once './controllers/ProductoPedidoController.php';
require_once './controllers/EncuestaController.php';
require_once './controllers/DbController.php';
require_once './middlewares/AuthMiddleware.php';
require_once './middlewares/LoggerSocio.php';
require_once './middlewares/LoggerMozo.php';

define("AUTHSOCIO", \AuthMiddleware::class . ':verificarRolSocio');
define("AUTHMOZO", \AuthMiddleware::class . ':verificarRolMozo');
define("AUTHEMPLEADO", \AuthMiddleware::class . ':verificarRolEmpleado');
define("AUTHTOKEN", \AuthMiddleware::class . ':verificarToken');

// Instantiate App
$app = AppFactory::create();

// Add error middleware
$app->addErrorMiddleware(true, true, true);

// Add parse body
$app->addBodyParsingMiddleware();

$app->get('/', function (Request $request, Response $response) {
    $payload = json_encode(array('Mensaje' => "Bienvenido a la Comanda"));
    $response->getBody()->write($payload);
    return $response;
});

// Groups
$app->group('/usuarios', function (RouteCollectorProxy $group) {
    $group->get('[/]', \UsuarioController::class . ':TraerTodos')->add(AUTHSOCIO)->add(AUTHTOKEN);
    $group->post('[/]', \UsuarioController::class . ':Cargar');
    $group->get('/exportar', \UsuarioController::class . ':ExportarUsuarios')->add(AUTHSOCIO)->add(AUTHTOKEN);
    $group->post('/importar', \UsuarioController::class . ':ImportarUsuarios')->add(AUTHSOCIO)->add(AUTHTOKEN);
});

$app->group('/productos', function (RouteCollectorProxy $group) {
    $group->get('[/]', \ProductoController::class . ':TraerTodos')->add(AUTHSOCIO)->add(AUTHTOKEN);
    $group->post('[/]', \ProductoController::class . ':Cargar');
});

$app->group('/mesas', function (RouteCollectorProxy $group) {
    $group->get('[/]', \MesaController::class . ':TraerTodos')->add(AUTHSOCIO)->add(AUTHTOKEN);
    $group->post('[/]', \MesaController::class . ':Cargar');
    $group->get('/cobrar', \MesaController::class . ':CobrarMesa')->add(AUTHMOZO)->add(AUTHTOKEN);
    $group->get('/cerrar', \MesaController::class . ':CerrarMesa')->add(AUTHSOCIO)->add(AUTHTOKEN);
});

$app->group('/pedido', function (RouteCollectorProxy $group) {
    $group->get('[/]', \PedidoController::class . ':TraerTodos')->add(AUTHSOCIO);
    $group->post('[/]', \PedidoController::class . ':Cargar')->add(AUTHMOZO);
    $group->post('/cargarProductos', \ProductoPedidoController::class . ':CargarProductos')->add(AUTHMOZO);
    $group->post('/asignarProductos', \ProductoPedidoController::class . ':AsignarProductos')->add(AUTHEMPLEADO);
    $group->post('/finalizarProductos', \ProductoPedidoController::class . ':FinalizarProductos')->add(AUTHEMPLEADO);
    $group->post('/foto', \PedidoController::class . ':TomarFoto')->add(AUTHMOZO);
    $group->get('/listos', \PedidoController::class . ':Listos')->add(AUTHMOZO);
})->add(AUTHTOKEN);

$app->group('/cliente', function (RouteCollectorProxy $group) {
    $group->post('/encuesta', \EncuestaController::class . ':CargarEncuesta');
    $group->get('/demora', \PedidoController::class . ':Demora');
});

// JWT test
$app->group('/jwt', function (RouteCollectorProxy $group) {

    $group->post('/crearToken', function (Request $request, Response $response) {    
    $parametros = $request->getParsedBody();

    $usuario = $parametros['usuario'];
    $perfil = $parametros['perfil'];
    $alias = $parametros['alias'];

    $datos = array('usuario' => $usuario, 'perfil' => $perfil, 'alias' => $alias);

    $token = AutentificadorJWT::CrearToken($datos);
    $payload = json_encode(array('jwt' => $token));

    $response->getBody()->write($payload);
    return $response->withHeader('Content-Type', 'application/json');
    });

    $group->get('/devolverPayLoad', function (Request $request, Response $response) {
    $header = $request->getHeaderLine('Authorization');
    $token = trim(explode("Bearer", $header)[1]);

    try {
    $payload = json_encode(array('payload' => AutentificadorJWT::ObtenerPayLoad($token)));
    } catch (Exception $e) {
    $payload = json_encode(array('error' => $e->getMessage()));
    }

    $response->getBody()->write($payload);
    return $response->withHeader('Content-Type', 'application/json');
    });

    $group->get('/devolverDatos', function (Request $request, Response $response) {
    $header = $request->getHeaderLine('Authorization');
    $token = trim(explode("Bearer", $header)[1]);

    try {
    $payload = json_encode(array('datos' => AutentificadorJWT::ObtenerData($token)));
    } catch (Exception $e) {
    $payload = json_encode(array('error' => $e->getMessage()));
    }

    $response->getBody()->write($payload);
    return $response->withHeader('Content-Type', 'application/json');
    });

    $group->get('/verificarToken', function (Request $request, Response $response) {
    $header = $request->getHeaderLine('Authorization');
    $token = trim(explode("Bearer", $header)[1]);
    $esValido = false;

    try {
    AutentificadorJWT::verificarToken($token);
    $esValido = true;
    } catch (Exception $e) {
    $payload = json_encode(array('error' => $e->getMessage()));
    }

    if ($esValido) {
    $payload = json_encode(array('valid' => $esValido));
    }

    $response->getBody()->write($payload);
    return $response
        ->withHeader('Content-Type', 'application/json');
    });
});

  // JWT en login
$app->group('/auth', function (RouteCollectorProxy $group) {
    $group->post('/login', function (Request $request, Response $response) {    
    $parametros = $request->getParsedBody();

    $usuario = $parametros['usuario'];
    $rol = $parametros['rol'];

    if(DbController::AuthLogin($usuario, $rol)){ // EJEMPLO!!! Acá se deberia ir a validar el usuario contra la DB
        if(DbController::AuthSuspendido($usuario, $rol)){
            $datos = array('usuario' => $usuario, 'rol' => $rol);
            $token = AutentificadorJWT::CrearToken($datos);
            $payload = json_encode(array('jwt' => $token));
        }else {
            $payload = json_encode(array('error' => 'El usuario esta suspendido.'));
            }
    } else {
    $payload = json_encode(array('error' => 'El usuario no existe.'));
    }

    $response->getBody()->write($payload);
    return $response->withHeader('Content-Type', 'application/json');
    });
});

$app->run();
