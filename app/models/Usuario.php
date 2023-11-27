<?php
require_once './db/AccesoDb.php';
require_once './exceptions/PropiedadInvalidaExcepcion.php';

class Usuario
{
    public $id;
    public $nombre;
    public $rol;
    public $fechaIngreso;
    public $operaciones;
    public $suspendido;
    public $sector;

    public function setFechaIngreso($fecha) {
        $timestamp = strtotime($fecha);

        if ($timestamp !== false && date('Y-m-d H:i:s', $timestamp) === $fecha) {
            $this->fechaIngreso = $fecha;
        } else {
            throw new PropiedadInvalidaException("La fecha no es válida o no tiene el formato 'Y-m-d H:i:s'");
        }
    }

    public function setNombre($nombre) {
        if (preg_match('/^[a-zA-Z\s]+$/', $nombre)) {
            $this->nombre = $nombre;
        } else {
            throw new PropiedadInvalidaException("Nombre no válido.");
        }
    }

    public function setRol($rol) {
        $roles = array('bartender', 'cervecero', 'cocinero', 'mozo', 'socio');
        $strLow = strtolower($rol);
        if (in_array($strLow, $roles)) {
            $this->rol = $rol;
        } else {
            throw new PropiedadInvalidaException("Rol no válido.");
        }
    }

    public function setSuspendido($suspendido) {
        if (is_bool($suspendido)) {
            $this->suspendido = $suspendido;
        } else {
            throw new PropiedadInvalidaException("El valor de suspendido no es válido.");
        }
    }

    public function setOperaciones($operaciones) {
        if (is_int($operaciones)) {
            $this->operaciones = $operaciones;
        } else {
            throw new PropiedadInvalidaException("El valor de operaciones no es válido.");
        }
    }

    public function setSector($sector) {
        $sectores = array('barra','choperas','cocina','candybar', '');
        $strLow = strtolower($sector);
        if (in_array($strLow, $sectores)) {
            $this->sector = $strLow;
        } else {
            throw new PropiedadInvalidaException("Sector no válido.");
        }
    }

    public function Alta()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO usuarios (nombre, rol, fechaIngreso,  operaciones, suspendido, sector) 
                                                        VALUES (:nombre, :rol, :fechaIngreso, :operaciones, :suspendido, :sector)");
        $consulta->bindValue(':nombre', $this->nombre, PDO::PARAM_STR);
        $consulta->bindValue(':rol', $this->rol, PDO::PARAM_STR);
        $consulta->bindValue(':fechaIngreso', $this->fechaIngreso, PDO::PARAM_STR);
        $consulta->bindValue(':operaciones', $this->operaciones, PDO::PARAM_INT);
        $consulta->bindValue(':suspendido', $this->suspendido, PDO::PARAM_BOOL);
        $consulta->bindValue(':sector', $this->sector, PDO::PARAM_STR);
        $consulta->execute();
    }

    public static function Modificar($usuario){
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("UPDATE usuarios
                                                        SET nombre = :nombre, rol = :rol, suspendido = :suspendido, sector = :sector
                                                        WHERE id = :id");
        $consulta->bindValue(':id', $usuario->id, PDO::PARAM_INT);
        $consulta->bindValue(':nombre', $usuario->nombre, PDO::PARAM_STR);
        $consulta->bindValue(':rol', $usuario->rol, PDO::PARAM_STR);
        $consulta->bindValue(':suspendido', $usuario->suspendido, PDO::PARAM_BOOL);
        $consulta->bindValue(':sector', $usuario->sector, PDO::PARAM_STR);
        $consulta->execute();
    }

    public static function Eliminar($id){
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("DELETE FROM usuarios WHERE id = :id");
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();
    }

    public static function ObtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, nombre, rol, fechaIngreso,  operaciones, suspendido, sector FROM usuarios");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function ObtenerUsuarioId($id)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, nombre, rol, fechaIngreso,  operaciones, suspendido, sector 
                                                        FROM usuarios 
                                                        WHERE id = :id");
        $consulta->bindParam(':id', $id, PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Usuario');
    }

    public static function ObtenerUsuario($nombre, $rol)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, nombre, rol, fechaIngreso,  operaciones, suspendido, sector 
                                                        FROM usuarios 
                                                        WHERE nombre = :nombre AND rol = :rol");
        $consulta->bindParam(':nombre', $nombre, PDO::PARAM_STR);
        $consulta->bindParam(':rol', $rol, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Usuario');
    }

    public static function Exportar()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM usuarios");
        $consulta->execute();

        $timestampBefore = time();
        $csv_filename = './backups/usuarios_' . $timestampBefore . '.csv';
        if (file_exists($csv_filename)) {
            $timestampAfter = time(); // Obtener la marca de tiempo actual
            $csv_filename = './backups/usuarios_' . $timestampAfter . '.csv';
        }

        $csv_file = fopen($csv_filename, 'w');

        while ($row = $consulta->fetch(PDO::FETCH_ASSOC)) {
            fputcsv($csv_file, $row);
        }
    
        fclose($csv_file);
        return true;
    }

    public static function Importar($csv_filename)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
    
        if (!file_exists($csv_filename)) {
            return false;
        }
        
        $csv_file = fopen($csv_filename, 'r');
    
        while (($row = fgetcsv($csv_file)) !== false) {
            $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO usuarios (Nombre, Rol, FechaIngreso, Operaciones, Suspendido, Sector) VALUES (:1, :2, :3, :4, :5, :6)");
            
            $consulta->bindParam(1, $row[1]); // Nombre
            $consulta->bindParam(2, $row[2]); // Rol
            $consulta->bindParam(3, $row[3]); // FechaIngreso
            $consulta->bindParam(4, $row[4]); // Operaciones
            $consulta->bindParam(5, $row[5]); // Suspendido
            $consulta->bindParam(6, $row[6]); // Sector
            
            $consulta->execute();
        }
    
        fclose($csv_file);
        return true;
    }
}