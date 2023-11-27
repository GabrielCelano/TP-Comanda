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

    public function ModificarUsuario($request, $response, $args)
    {
        try {
            $parametros = $request->getParsedBody();
            $id = $parametros['id'];
            $nombre = $parametros['nombre'];
            $rol = $parametros['rol'];
            $sector = $parametros['sector'];
            $suspendido = ($parametros['suspendido'] === 'si') ? true : false;

            $usuarios = Usuario::ObtenerUsuarioId($id);

            if(!empty($usuarios)){
                $usuarios[0]->setNombre($nombre);
                $usuarios[0]->setRol($rol);
                $usuarios[0]->setSector($sector);
                $usuarios[0]->setSuspendido($suspendido);
                Usuario::Modificar($usuarios[0]);
                $payload = json_encode(array("mensaje" => "Usuario modificado con exito"));
            }else{
                $payload = json_encode(array("mensaje" => "El usuario no existe."));
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

    public function EliminarUsuario($request, $response, $args)
    {
        try {
            $parametros = $request->getParsedBody();
            $id = $parametros['id'];

            $usuarios = Usuario::ObtenerUsuarioId($id);
            if(!empty($usuarios)){
                Usuario::Eliminar($id);
                $payload = json_encode(array("mensaje" => "Usuario eliminado con exito"));
            }else{
                $payload = json_encode(array("mensaje" => "El usuario no existe."));
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
        $lista = Usuario::ObtenerTodos();
        $payload = json_encode(array("listaUsuarios" => $lista));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function ExportarUsuarios($request, $response, $args)
    {
        try {
        if(Usuario::Exportar()){
            $payload = json_encode(array("Mensaje" => 'Usuarios exportados exitosamente.'));
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json');
        }
        }catch (Exception $e) {
            $payload = json_encode(array("error" => $e->getMessage()));
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
    }

    public function ImportarUsuarios($request, $response, $args)
    {
        try {
            $archivos = $request->getUploadedFiles();
            $ruta = null;
            foreach ($archivos as $archivo) {
                if ($archivo instanceof \Psr\Http\Message\UploadedFileInterface) {
                    $ruta = "./backups/" . $archivo->getClientFilename();
                    break;
                }
            }
            if(Usuario::Importar($ruta)){
                $payload = json_encode(array("Mensaje" => 'Usuarios importados exitosamente.'));
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json');
            }
        }catch (Exception $e) {
            $payload = json_encode(array("error" => $e->getMessage()));
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
    }
}