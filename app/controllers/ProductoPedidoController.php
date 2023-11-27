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
        $codigoPedido = $parametros['codigoPedido'];
        $cantidad = $parametros['cantidad'];
        
        if(count(Producto::ObtenerProducto($idProducto)) == 1 &&
        count(Pedido::ObtenerPedido($codigoPedido)) == 1){
            ProductoPedido::CompletarPedido($codigoPedido, $idProducto, $cantidad);
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
        $datosToken = $request->getAttribute('datosToken');
        $codigo = $parametros['codigoPedido'];
        $nombre = $datosToken->usuario;
        $rol = $datosToken->rol;

        $usuario = Usuario::ObtenerUsuario($nombre, $rol);

        if(!empty($usuario)){
            if(ProductoPedido::EmpleadoOcupado($usuario[0]->id) == 0){
                $result = ProductoPedido::Asignar($codigo, $usuario[0]->id);
                $payload = json_encode(array("Producto asignado correctamente" => $result));
            }else{
                $payload = json_encode(array("mensaje" => "El empleado ya esta preparando un pedido, asignar otro."));
            }
        }else{
            $payload = json_encode(array("mensaje" => "El empleado no existe."));
        }

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function FinalizarProductos($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        $datosToken = $request->getAttribute('datosToken');
        $codigoPedido = $parametros['codigoPedido'];
        $nombre = $datosToken->usuario;
        $rol = $datosToken->rol;
        
        $usuario = Usuario::ObtenerUsuario($nombre, $rol);

        if(!empty($usuario)){
            if(ProductoPedido::EmpleadoOcupado($usuario[0]->id) != 0){
                $result = ProductoPedido::Finalizar($codigoPedido, $usuario[0]->id);
                $payload = json_encode(array("Producto listo para servir" => $result));
            }else{
                $payload = json_encode(array("mensaje" => "El empleado no tiene ningun producto asignado."));
            }
        }else{
            $payload = json_encode(array("mensaje" => "El empleado no existe."));
        }

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}