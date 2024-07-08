<?php
ini_set( 'display_errors', 1 );
ini_set( 'display_startup_errors', 1 );
error_reporting( E_ALL );

//iniciar la sesion
session_start();

use Slim\Factory\AppFactory;
use DI\Container;
use Firebase\JWT\JWT;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../middleware/JwtMiddleware.php';
//Conexion a base de datos
$container = new Container();
(require __DIR__ . '/../config/database.php')($container);
//funciones
require __DIR__ . '/../functions/funciones.php';

//header( "Access-Control-Allow-Origin: https://inversiones.micoopebienestar.com.gt" );
header( "Access-Control-Allow-Origin: http://localhost:4200" );
header( "Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS" );
header( "Access-Control-Allow-Headers: Content-Type" );

// Configurar Slim para usar este contenedor
AppFactory::setContainer($container);

// Inicializar SlimFramework
$app = AppFactory::create();

// JWT
$jwtMiddleware = new JwtMiddleware();

//modificar la ruta del backend para el servidor
$app->setBasePath( "/ahorralink/backend/public" );
//$app->setBasePath( "/backend/public" );

$app->options( '/{routes:.+}', function ( $request, $response, $args ) {
    return $response;
} );

$app->add( function ( $request, $handler ) {
    $response = $handler->handle( $request );
    return $response
        //->withHeader( 'Access-Control-Allow-Origin', 'https://inversiones.micoopebienestar.com.gt' )
        ->withHeader( 'Access-Control-Allow-Origin', '*' )
        ->withHeader( 'Access-Control-Allow-Headers', 'Content-Type, X-Requested-With, Accept, Origin, Authorization' )
        ->withHeader( 'Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS' );
} );

/**
 * The routing middleware should be added before the ErrorMiddleware
 * Otherwise exceptions thrown from it will not be handled
 */
$app->addRoutingMiddleware();

$app->addBodyParsingMiddleware();

//Rutas del ahorralink
require __DIR__ . '/../api/v1/ahorralink.php';

/**
 * Add Error Handling Middleware
 *
 * @param bool $displayErrorDetails -> Should be set to false in production
 * @param bool $logErrors -> Parameter is passed to the default ErrorHandler
 * @param bool $logErrorDetails -> Display error details in error log
 * which can be replaced by a callable of your choice.
 * Note: This middleware should be added last. It will not handle any exceptions/errors
 * for middleware added after it.
 */
$app->addErrorMiddleware( TRUE, TRUE, TRUE );

$app->run();
