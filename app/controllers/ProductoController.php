<?php
require_once './models/Producto.php';

class ProductoController extends Producto
{
    public function Cargar($request, $response, $args)
    {
        try {
            $parametros = $request->getParsedBody();
    
            $nombre = $parametros['nombre'];
            $tipo = $parametros['tipo'];
            $precio = $parametros['precio'];
            $sector = $parametros['sector'];
            $tiempoPreparacion = $parametros['tiempoPreparacion'];
    
            $producto = new Producto();
            $producto->setNombre($nombre);
            $producto->setTipo($tipo);
            $producto->setPrecio($precio);
            $producto->setSector($sector);
            $producto->setTiempoPreparacion($tiempoPreparacion);
            $producto->Alta();
    
            $payload = json_encode(array("mensaje" => "Producto creado con exito"));
    
            $response->getBody()->write($payload);
            return $response
            ->withHeader('Content-Type', 'application/json');
        } catch (Exception $e) {
            $payload = json_encode(array("error" => $e->getMessage()));
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
    }

    public function EliminarProducto($request, $response, $args)
    {
        try {
            $parametros = $request->getParsedBody();
            $id = $parametros['id'];

            $productos = Producto::ObtenerProducto($id);
            if(!empty($productos)){
                Producto::Eliminar($id);
                $payload = json_encode(array("mensaje" => "Producto eliminado con exito."));
            }else{
                $payload = json_encode(array("mensaje" => "El producto no existe."));
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

    public function ModificarProducto($request, $response, $args)
    {
        try {
            $parametros = $request->getParsedBody();
            $id = $parametros['id'];
            $nombre = $parametros['nombre'];
            $tipo = $parametros['tipo'];
            $precio = $parametros['precio'];
            $sector = $parametros['sector'];
            $tiempoPreparacion = $parametros['tiempoPreparacion'];

            $productos = Producto::ObtenerProducto($id);

            if(!empty($productos)){
                $producto = new Producto();
                $producto->setNombre($nombre);
                $producto->setTipo($tipo);
                $producto->setPrecio($precio);
                $producto->setSector($sector);
                $producto->setTiempoPreparacion($tiempoPreparacion);
                $producto->Modificar($id);
        
                $payload = json_encode(array("mensaje" => "Producto modificado con exito."));
            }else{
                $payload = json_encode(array("mensaje" => "El producto no existe."));
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

    public function TraerTodos($request, $response, $args)
    {
        $lista = Producto::ObtenerTodos();
        $payload = json_encode(array("listaProductos" => $lista));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}