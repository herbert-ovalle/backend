<?php

use Respect\Validation\Validator as v;
use Slim\Psr7\Message;
use Slim\Psr7\Response;

/**
 *
 */
class Validador
{
    /**
     * @param $data
     * @param $validationRules
     * @return Message|Response
     */
    public function validar($data, $validationRules): Response|Message
    {
        $response = new Response();
        foreach ($validationRules as $field => $rule) {
            // Verifica si $rule es una instancia de Validator
            if (!$rule instanceof v) {
                throw new InvalidArgumentException("La regla para $field no es una instancia de Respect\Validation\Validator.");
            }

            if (!$rule->validate($data[$field])) {
                $responseBody = [
                    'respuesta' => 'Warning',
                    'mensaje' => "Dato invalido para $field , verificar ",
                ];
                $response->getBody()->write(json_encode($responseBody));
                return $response
                    ->withHeader('content-type', 'application/json')
                    ->withStatus(200);
            }
        }

        $responseBody = [
            'respuesta' => 'success',
            'mensaje' => 'Excelente',
        ];
        $response->getBody()->write(json_encode($responseBody));
        return $response->withHeader('content-type', 'application/json')->withStatus(200);
    }
}
