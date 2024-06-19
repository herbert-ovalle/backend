<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use DI\Container;


// Configuración de Dotenv para cargar las variables de entorno
$envFilePath = __DIR__ . '/../';
$dotenv      = Dotenv::createImmutable( $envFilePath );
$dotenv->load();

return function ( Container $container ) {
    $container->set( 'db', function () {
        $host   = $_ENV[ 'DB_HOST' ];
        $user   = $_ENV[ 'DB_USERNAME' ];
        $pass   = $_ENV[ 'DB_PASSWORD' ];
        $dbname = $_ENV[ 'DB_DATABASE' ];
        $dsn    = "mysql:host=$host;dbname=$dbname;charset=utf8mb4;collation=utf8mb4_spanish_ci";

        $reintentos = 3;
        $intento    = 0;
        $esperar    = 1000;

        while ( $intento < $reintentos ) {
            try {
                $pdo = new PDO( $dsn, $user, $pass );
                $pdo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
                return $pdo;
            } catch ( PDOException $e ) {
                $intento++;
                if ( $intento >= $reintentos ) {
                    // Manejo de error
                    error_log( "Error de conexión a la base de datos: " . $e->getMessage() );
                    throw $e;
                }
                // Espera exponencial
                // usleep espera en microsegundos
                usleep( $esperar * 1000 );
                // Duplicar el tiempo de espera para el próximo reintento de conexión
                $esperar *= 2;
            }
        }
        throw new Exception( "Error inesperado al intentar la conexión a la base de datos" );
    } );
};
