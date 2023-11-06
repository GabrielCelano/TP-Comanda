<?php
require_once './models/Usuario.php';

class UsuarioController extends Usuario
{
    public function Cargar($request, $response, $args)
    {
        try {
            $parametros = $request->getParsedBody();
    
            $currentDateTime = date("Y-m-d H:i:s");
            $nombre = $parametros['nombre'];
            $sector = $parametros['sector'];
            $rol = $parametros['rol'];
            
    
            $usuario = new Usuario();
            $usuario->setNombre($nombre);
            $usuario->setRol($rol);
            $usuario->setFechaIngreso($currentDateTime);
            $usuario->setOperaciones(0);
            $usuario->setSuspendido(false);
            $usuario->setSector($sector);
            $usuario->Alta();
    
            $payload = json_encode(array("mensaje" => "Usuario creado con exito"));
    
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
        $lista = Usuario::obtenerTodos();
        $payload = json_encode(array("listaUsuarios" => $lista));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}