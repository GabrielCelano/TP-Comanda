<?php
require_once './models/Encuesta.php';
require_once './models/Mesa.php';

class EncuestaController extends Encuesta
{
    public function CargarEncuesta($request, $response, $args)
    {
        try {
            $parametros = $request->getParsedBody();
    
            $codigoMesa = $parametros['codigoMesa'];
            $codigoPedido = $parametros['codigoPedido'];
            $valoracionMesa = $parametros['valoracionMesa'];
            $valoracionRestaurante = $parametros['valoracionRestaurante'];
            $valoracionMozo = $parametros['valoracionMozo'];
            $valoracionCocinero = $parametros['valoracionCocinero'];
            $opinion = $parametros['opinion'];
    
            $encuesta = new Encuesta();
            $encuesta->setCodigoMesa($codigoMesa);
            $encuesta->setCodigoPedido($codigoPedido);
            $encuesta->setValoracionMesa($valoracionMesa);
            $encuesta->setValoracionRestaurante($valoracionRestaurante);
            $encuesta->setValoracionMozo($valoracionMozo);
            $encuesta->setValoracionCocinero($valoracionCocinero);
            $encuesta->setOpinion($opinion);

            if(Mesa::ObtenerMesaEstado($codigoMesa)){
                $encuesta->Cargar();
                $payload = json_encode(array("mensaje" => "Encuesta envidada."));
            }else{
                $payload = json_encode(array("mensaje" => "La mesa no esta cerrada."));
            }
    
    
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