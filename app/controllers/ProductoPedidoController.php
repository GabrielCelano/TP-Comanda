<?php
require_once './models/Usuario.php';
require_once './models/Producto.php';
require_once './models/Pedido.php';
require_once './models/ProductoPedido.php';

class ProductoPedidoController extends ProductoPedido
{
    public function CargarProductos($request, $response, $args){
        $parametros = $request->getParsedBody();

        $idProducto = $parametros['idProducto'];
        $idPedido = $parametros['idPedido'];
        $cantidad = $parametros['cantidad'];
        
        if(count(Producto::ObtenerProducto($idProducto)) == 1 &&
        count(Pedido::ObtenerPedido($idPedido)) == 1){
            ProductoPedido::CompletarPedido($idPedido, $idProducto, $cantidad);
            $payload = json_encode(array("mensaje" => "Se cargaron los productos al pedido."));
        }else{
            $payload = json_encode(array("mensaje" => "No se pudieron cargar los productos al pedido."));
        }

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function AsignarProductos($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        $codigo = $parametros['codigo'];
        $idEmpleado = intval($parametros['idEmpleado']);

        if(count(Usuario::obtenerUsuario($idEmpleado)) == 1){
            $result = ProductoPedido::Asignar($codigo, $idEmpleado);
            $payload = json_encode(array("Producto asignado correctamente" => $result));
        }else{
            $payload = json_encode(array("mensaje" => "El empleado no existe."));
        }

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function FinalizarProductos($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        $codigo = $parametros['codigo'];
        $idEmpleado = intval($parametros['idEmpleado']);

        if(count(Usuario::obtenerUsuario($idEmpleado)) == 1){
            $result = ProductoPedido::Finalizar($codigo, $idEmpleado);
            $payload = json_encode(array("Producto listo para servir" => $result));
        }else{
            $payload = json_encode(array("mensaje" => "El empleado no existe."));
        }

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}