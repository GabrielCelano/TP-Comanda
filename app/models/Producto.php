<?php
require_once './db/AccesoDb.php';
require_once './exceptions/PropiedadInvalidaExcepcion.php';

class Producto
{
    public $nombre;
    public $tipo;
    public $precio;
    public $sector;
    public $tiempoPreparacion;

    public function setNombre($nombre) {
        if (preg_match('/^[a-zA-Z\s]+$/', $nombre)) {
            $this->nombre = $nombre;
        } else {
            throw new PropiedadInvalidaException("Nombre no válido.");
        }
    }

    public function setTipo($tipo) {
        $tipos = array('comida','bebida');
        $strLow = strtolower($tipo);
        if (in_array($strLow, $tipos)) {
            $this->tipo = $strLow;
        } else {
            throw new PropiedadInvalidaException("Tipo no válido.");
        }
    }

    public function setPrecio($precio) {
        if (is_numeric($precio) && $precio >= 0) {
            $this->precio = $precio;
        } else {
            throw new PropiedadInvalidaException("Precio no válido.");
        }
    }

    public function setSector($sector) {
        $sectores = array('barra','choperas','cocina','candybar');
        $strLow = strtolower($sector);
        if (in_array($strLow, $sectores)) {
            $this->sector = $strLow;
        } else {
            throw new PropiedadInvalidaException("Sector no válido.");
        }
    }

    public function setTiempoPreparacion($tiempoPreparacion) {
        $tiempoPreparacion = intval($tiempoPreparacion);
        if (is_numeric($tiempoPreparacion) && is_int($tiempoPreparacion)) {
            $this->tiempoPreparacion = $tiempoPreparacion;
        } else {
            throw new PropiedadInvalidaException("El tiempo de preparacion no es válido.");
        }
    }

    public function Alta()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO productos (nombre, tipo, precio, sector, tiempopreparacion) 
                                                        VALUES (:nombre, :tipo, :precio, :sector, :tiempopreparacion)");
        $consulta->bindValue(':nombre', $this->nombre, PDO::PARAM_STR);
        $consulta->bindValue(':tipo', $this->tipo, PDO::PARAM_STR);
        $consulta->bindValue(':precio', $this->precio, PDO::PARAM_INT);
        $consulta->bindValue(':sector', $this->sector, PDO::PARAM_STR);
        $consulta->bindValue(':tiempopreparacion', $this->tiempoPreparacion, PDO::PARAM_INT);
        $consulta->execute();
    }

    public static function obtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT nombre, tipo, precio, sector, tiempoPreparacion FROM productos");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Producto');
    }
}