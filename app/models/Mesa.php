<?php
require_once './db/AccesoDb.php';
require_once './exceptions/PropiedadInvalidaExcepcion.php';

class Mesa
{
    public $codigo;
    public $estado;

    public function setCodigo($codigo) {
        if (preg_match('/^[a-zA-Z0-9]{5}$/', $codigo)) {
            $this->codigo = $codigo;
        } else {
            throw new PropiedadInvalidaException("Codigo no es válido.");
        }
    }
    
    public function setEstado($estado) {
        $estado = strtolower($estado);
        $estadosValidos = array(
            'cliente esperando pedido',
            'cliente comiendo',
            'cliente pagando',
            'cerrada'
        );
    
        if (in_array($estado, $estadosValidos)) {
            $this->estado = $estado;
        } else {
            throw new PropiedadInvalidaException("Estado no es válido.");
        }
    }


    public function Alta()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO mesas (codigo, estado) VALUES (:codigo, :estado)");
        $consulta->bindValue(':codigo', $this->codigo, PDO::PARAM_STR);
        $consulta->bindValue(':estado', $this->estado, PDO::PARAM_STR);
        $consulta->execute();
    }

    public static function obtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT codigo, estado FROM mesas");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Mesa');
    }
}