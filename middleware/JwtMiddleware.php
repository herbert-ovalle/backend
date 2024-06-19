<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response as SlimResponse;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 *
 */
class JwtMiddleware {
    /**
     * @param Request $request PSR-7 request
     * @param RequestHandler $handler PSR-15 request handler
     *
     * @return Response
     */
    public function __invoke( Request $request, RequestHandler $handler ) : Response {
        $header = $request->getHeaderLine( 'Authorization' ); // Recupera la cabecera de autorización
        $token  = substr( $header, 7 ); // Supone que el token está precedido por 'Bearer '

        if ( !$token ) {
            // No se encontró el token
            $response = new SlimResponse();
            return $response->withStatus( 401 )->withHeader( 'Content-Type', 'application/json' )
                ->write( json_encode( [ 'error' => 'Token no proporcionado o inválido' ] ) );
        }

        try {
            $key     = $_ENV[ 'JWT_SECRET' ]; // La clave usada para firmar el token
            $decoded = JWT::decode( $token, new Key( $key, 'HS256' ) );
            // El token es válido. Añade el usuario decodificado a la petición si es necesario
            $request = $request->withAttribute( 'decoded', $decoded );
            return $handler->handle( $request );
        } catch ( Exception $e ) {
            // Error al decodificar el token
            $response = new SlimResponse();
            return $response->withStatus( 401 )->withHeader( 'Content-Type', 'application/json' )
                ->write( json_encode( [ 'error' => 'Token no válido' ] ) );
        }
    }
}
