<?php
require_once './models/Pedido.php';
require_once './models/Producto.php';
require_once './models/Usuario.php';
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
            $tiempoEstimado = intval($parametros['tiempoEstimado']);
            $idMozo= $parametros['idMozo'];
            $foto = $parametros['foto'];
    
            $pedido = new Pedido();
            $pedido->setCodigo($codigo);
            $pedido->setCodigoMesa($codigoMesa);
            $pedido->setEstado($estado);
            $pedido->setTiempoEstimado($tiempoEstimado);
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

    public function CargarProductos($request, $response, $args){
        $parametros = $request->getParsedBody();

        $idProducto = $parametros['idProducto'];
        $idPedido = $parametros['idPedido'];
        $cantidad = $parametros['cantidad'];
        $estadoProducto = $parametros['estadoProducto'];
        $idEmpleado = $parametros['idEmpleado'];
        
        if(count(Producto::obtenerProducto($idProducto)) == 1 &&
        count(Pedido::obtenerPedido($idPedido)) == 1 &&
        count(Usuario::obtenerUsuario($idEmpleado)) == 1){
            Pedido::CompletarPedido($idPedido, $idProducto, $cantidad, $estadoProducto, $idEmpleado);
        }

        $payload = json_encode(array("mensaje" => "Se cargaron los productos al pedido."));
    
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}