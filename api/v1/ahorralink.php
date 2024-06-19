<?php
global $app;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

// Obtener departamentos
$app->get( '/ahorralink/getDepartamentos', function ( Request $request, Response $response ) {
    $connect = $this->get('db');
    $sql = "SELECT id_departamento, departamento FROM crm_negocios_pruebas.departamento WHERE id_departamento IN(13, 15, 20, 21) ORDER BY id_departamento;";
    try {
        $stmt          = $connect->query( $sql );
        $departamentos = $stmt->fetchAll( PDO::FETCH_OBJ );

        $response->getBody()->write( json_encode( $departamentos ) );

        return $response
            ->withHeader( 'content-type', 'application/json' )
            ->withStatus( 200 );
    } catch ( PDOException $e ) {
        $error = [
            'respuesta' => 'danger',
            'mensaje'   => $e->getMessage()
        ];

        $response->getBody()->write( json_encode( $error ) );
        return $response
            ->withHeader( 'content-type', 'application/json' )
            ->withStatus( 500 );
    }
} );

//Obtener el listado de agencias
$app->get( '/ahorralink/getAgencias', function ( Request $request, Response $response ) {
    $connect = $this->get('db');
    try {
        $sql = "SELECT id_agencia AS 'idAgencia', nombre AS 'agencia', direccion FROM usuarios.agencia WHERE id_agencia < 11 ORDER BY id_agencia;";
        $stmt          = $connect->query( $sql );
        $listadoAgencias = $stmt->fetchAll( PDO::FETCH_OBJ );

        $response->getBody()->write( json_encode( $listadoAgencias ) );

        return $response
            ->withHeader( 'content-type', 'application/json' )
            ->withStatus( 200 );
    } catch ( PDOException $e ) {
        $error = [
            'respuesta' => 'danger',
            'mensaje'   => $e->getMessage()
        ];

        $response->getBody()->write( json_encode( $error ) );
        return $response
            ->withHeader( 'content-type', 'application/json' )
            ->withStatus( 500 );
    }
});


//Obtener los estados de la solicitud
$app->get( '/ahorralink/getListadoEstadosSolicitud', function ( Request $request, Response $response ) {
    $connect = $this->get('db');
    $sql = "SELECT idEstadoSolicitud, estadoSolicitud FROM gestorCreditos.estadoSolicitud ORDER BY idEstadoSolicitud;";
    try {
        $stmt          = $connect->query( $sql );
        $estadoSolicitud = $stmt->fetchAll( PDO::FETCH_OBJ );

        $response->getBody()->write( json_encode( $estadoSolicitud ) );

        return $response
            ->withHeader( 'content-type', 'application/json' )
            ->withStatus( 200 );
    } catch ( PDOException $e ) {
        $error = [
            'respuesta' => 'danger',
            'mensaje'   => $e->getMessage()
        ];

        $response->getBody()->write( json_encode( $error ) );
        return $response
            ->withHeader( 'content-type', 'application/json' )
            ->withStatus( 500 );
    }
} );

/*-- Consultar todos los municipios del departamento seleccionado*/
$app->post( '/ahorralink/getMunicipios', function ( Request $request, Response $response ) {
    $connect = $this->get('db');
    $data = $request->getParsedBody();

    $idDepartamento = $data[ 'idDepartamento' ];

    $sql = "SELECT id_municipio, municipio, id_departamento FROM crm_negocios_pruebas.municipio WHERE id_departamento = {$idDepartamento};";

    try {
        $stmt       = $connect->query( $sql );
        $municipios = $stmt->fetchAll( PDO::FETCH_OBJ );

        $response->getBody()->write( json_encode( $municipios ) );

        return $response
            ->withHeader( 'content-type', 'application/json' )
            ->withStatus( 200 );
    } catch ( PDOException $e ) {
        $error = [
            'respuesta' => 'danger',
            'mensaje'   => $e->getMessage()
        ];

        $response->getBody()->write( json_encode( $error ) );
        return $response
            ->withHeader( 'content-type', 'application/json' )
            ->withStatus( 500 );
    }
} );

/*-- Guardar nueva solicitud*/
$app->post( '/ahorralink/guardarFormulario', function ( Request $request, Response $response, array $args ) {
    $connect = $this->get('db');
    $data            = $request->getParsedBody();
    $datosFormulario = $data[ 'formulario' ];

    $observaciones = $connect->quote( $data[ 'observaciones' ] );

    $jsonData = json_encode( $datosFormulario );

    try {
        $sql = "CALL gestorCreditos.proGuardarSolicitud('insert', NULL, '{$jsonData}', $observaciones);";

        $stmt = $connect->prepare( $sql );
        $stmt->execute();

        $stmt->bindColumn( 'respuesta', $respuesta );
        $stmt->bindColumn( 'mensaje', $mensaje );
        $stmt->bindColumn( 'idSolicitud', $idSolicitud );
        $stmt->fetch();

        $responseBody = [
            'respuesta'   => $respuesta,
            'mensaje'     => $mensaje,
            'idSolicitud' => $idSolicitud
        ];

        $response->getBody()->write( json_encode( $responseBody ) );
        return $response
            ->withHeader( 'content-type', 'application/json' )
            ->withStatus( 200 );
    } catch ( PDOException $e ) {
        $error = [
            'respuesta'   => 'danger',
            'mensaje'     => $e->getMessage(),
            'idSolicitud' => NULL
        ];

        $response->getBody()->write( json_encode( $error ) );

        return $response
            ->withHeader( 'content-type', 'application/json' )
            ->withStatus( 500 );
    }

} );