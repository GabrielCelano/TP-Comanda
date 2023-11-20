<?php
require_once './models/Pedido.php';
require_once './helpers/CodigoHelper.php';

class PedidoController extends Pedido
{
    public function Cargar($request, $response, $args)
    {
        try {
            $parametros = $request->getParsedBody();
    
            $codigo = CodigoHelper::generarCodigoUnico();
            $codigoMesa = $parametros['codigoMesa'];
            $estado = $parametros['estado'];
            $idMozo= $parametros['idMozo'];
            $foto = $parametros['foto'];
    
            $pedido = new Pedido();
            $pedido->setCodigo($codigo);
            $pedido->setCodigoMesa($codigoMesa);
            $pedido->setEstado($estado);
            $pedido->setTiempoEstimado(0);
            $pedido->setIdMozo($idMozo);
            $pedido->setFoto($foto);
            $pedido->Alta();
    
            $payload = json_encode(array("mensaje" => "Pedido creado con exito"));
    
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json');
        } catch (Exception $e) {
            $payload = json_encode(array("error" => $e->getMessage()));
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
    }

    public function TraerTodos($request, $response, $args)
    {
        $lista = Pedido::obtenerTodos();
        $payload = json_encode(array("listaPedidos" => $lista));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function Demora($request, $response, $args){
        $parametros = $request->getQueryParams();

        $codigoMesa = $parametros['codigoMesa'];
        $codigoPedido = $parametros['codigoPedido'];
        
        $result = Pedido::ConsultaCliente($codigoMesa, $codigoPedido);
        
        $payload = json_encode(array("Tiempo de demora" => $result));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function Listos($request, $response, $args){
        $lista = Pedido::ObtenerPedidosListos();
        $payload = json_encode(array("Pedidos listos a servir" => $lista));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}