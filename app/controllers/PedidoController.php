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
            $datosToken = $request->getAttribute('datosToken');
            $nombre = $datosToken->usuario;
            $rol = $datosToken->rol;
            $usuario = Usuario::ObtenerUsuario($nombre, $rol);
            $codigoMesa = $parametros['codigoMesa'];
            $estado = $parametros['estado'];
    
            $pedido = new Pedido();
            $pedido->setCodigo($codigo);
            $pedido->setCodigoMesa($codigoMesa);
            $pedido->setEstado($estado);
            $pedido->setTiempoEstimado(0);
            $pedido->setIdMozo($usuario[0]->id);
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

    public function TomarFoto($request, $response, $args)
    {
        try {
            $parametrosBody = $request->getParsedBody();
            $codigoPedido = $parametrosBody['codigoPedido'];

            $fotos = $request->getUploadedFiles();
            $ruta = '';
            $file = '';
            foreach ($fotos as $foto) {
                if ($foto instanceof \Psr\Http\Message\UploadedFileInterface) {
                    $nuevoNombre = $codigoPedido . '.' . pathinfo($foto->getClientFilename(), PATHINFO_EXTENSION);
                    $ruta = "./pedidos/" . $nuevoNombre;
                    $file = $foto->getStream()->getMetadata('uri');
                    break;
                }
            }
            
            move_uploaded_file($file , $ruta);

            Pedido::Foto($codigoPedido, $ruta);
    
            $payload = json_encode(array("mensaje" => "Foto tomada con exito."));
    
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