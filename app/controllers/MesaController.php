<?php
require_once './models/Mesa.php';
require_once './helpers/CodigoHelper.php';

class MesaController extends Mesa
{
    public function Cargar($request, $response, $args)
    {
        try {
            $parametros = $request->getParsedBody();
    
            $codigo = CodigoHelper::generarCodigoUnico();
            $estado = $parametros['estado'];
    
            $mesa = new Mesa();
            $mesa->setCodigo($codigo);
            $mesa->setEstado($estado);

            $mesa->Alta();
    
            $payload = json_encode(array("mensaje" => "Mesa creada con exito"));
    
            $response->getBody()->write($payload);
            return $response
            ->withHeader('Content-Type', 'application/json');
        } catch (Exception $e) {
            $payload = json_encode(array("error" => $e->getMessage()));
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
    }

    public function TraerTodos($request, $response, $args)
    {
        $lista = Mesa::obtenerTodos();
        $payload = json_encode(array("listaMesas" => $lista));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}