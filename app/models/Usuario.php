<?php
require_once './db/AccesoDb.php';
require_once './exceptions/PropiedadInvalidaExcepcion.php';

class Usuario
{
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

    public static function obtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, nombre, rol, fechaIngreso,  operaciones, suspendido, sector FROM usuarios");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function obtenerUsuario($idUsuario)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT nombre, rol, fechaIngreso,  operaciones, suspendido, sector FROM usuarios WHERE id = :idUsuario");
        $consulta->bindParam(':idUsuario', $idUsuario, PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Usuario');
    }
}