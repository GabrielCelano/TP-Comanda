<?php
require_once './db/AccesoDb.php';
require_once './exceptions/PropiedadInvalidaExcepcion.php';

class Mesa
{
    public $codigo;
    public $estado;
    public $importeTotal;

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

    public function setImporteTotal($importe) {
        if (is_numeric($importe)) {
            $this->importeTotal = $importe;
        } else {
            throw new PropiedadInvalidaException("Importe total incorrecto.");
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

    public static function Cobrar($codigo)
    {
        $importeTotal = ProductoPedido::ObtenerImporteTotal($codigo);
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consultaUpdate = $objAccesoDatos->prepararConsulta("UPDATE mesas
                                                        SET Estado = 'cliente pagando',
                                                            ImporteTotal = :importeTotal
                                                        WHERE codigo = :codigo");
        $consultaUpdate->bindValue(':codigo', $codigo, PDO::PARAM_STR);
        $consultaUpdate->bindValue(':importeTotal', $importeTotal, PDO::PARAM_INT);
        $consultaUpdate->execute();

        $consultaSelect = $objAccesoDatos->prepararConsulta("SELECT codigo, estado, importeTotal FROM mesas WHERE codigo = :codigo");
        $consultaSelect->bindValue(':codigo', $codigo, PDO::PARAM_STR);
        $consultaSelect->execute();

        return $consultaSelect->fetchAll(PDO::FETCH_CLASS, 'Mesa');
    }

    
    public static function Cerrar($codigo)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consultaUpdate = $objAccesoDatos->prepararConsulta("UPDATE mesas
                                                                SET Estado = 'cerrada'
                                                                WHERE codigo = :codigo");
        $consultaUpdate->bindValue(':codigo', $codigo, PDO::PARAM_STR);
        $consultaUpdate->execute();

        $consultaSelect = $objAccesoDatos->prepararConsulta("SELECT codigo, estado, importeTotal FROM mesas WHERE codigo = :codigo");
        $consultaSelect->bindValue(':codigo', $codigo, PDO::PARAM_STR);
        $consultaSelect->execute();

        return $consultaSelect->fetchAll(PDO::FETCH_CLASS, 'Mesa');
    }

    public static function ObtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT codigo, estado, importeTotal FROM mesas");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Mesa');
    }

    public static function ObtenerMesaEstado($codigo){
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT CASE WHEN estado = 'cerrada' THEN TRUE ELSE FALSE END AS estado_mesa FROM mesas WHERE codigo = :codigo");
        $consulta->bindValue(':codigo', $codigo, PDO::PARAM_STR);
        $consulta->execute();
        
        return (bool) $consulta->fetchColumn();
    }
}