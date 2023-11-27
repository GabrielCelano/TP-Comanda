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

    public function CobrarMesa($request, $response, $args)
    {
        try {
            $parametros = $request->getQueryParams();
    
            $codigo = $parametros['codigo'];

            $result = Mesa::Cobrar($codigo);
    
            $payload = json_encode(array("Mesa pagando" => $result));
    
            $response->getBody()->write($payload);
            return $response
            ->withHeader('Content-Type', 'application/json');
        } catch (Exception $e) {
            $payload = json_encode(array("error" => $e->getMessage()));
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
    }

    public function CerrarMesa($request, $response, $args)
    {
        try {
            $parametros = $request->getQueryParams();
    
            $codigo = $parametros['codigo'];

            $result = Mesa::Cerrar($codigo);
    
            $payload = json_encode(array("Mesa cerrada" => $result));
    
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
        $lista = Mesa::ObtenerTodos();
        $payload = json_encode(array("listaMesas" => $lista));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function MesaMasUsada($request, $response, $args)
    {
        try {
            $result = Mesa::MasUsada();
            $payload = json_encode(array("Mesa mas usada" => $result));
            
            $response->getBody()->write($payload);
            return $response
            ->withHeader('Content-Type', 'application/json');
        } catch (Exception $e) {
            $payload = json_encode(array("error" => $e->getMessage()));
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
    }
}