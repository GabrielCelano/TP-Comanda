<?php
require_once './db/AccesoDb.php';
require_once './exceptions/PropiedadInvalidaExcepcion.php';

class Pedido{

    public $codigo;
    public $codigoMesa;
    public $estado;
    public $tiempoEstimado;
    public $idMozo;
    public $foto;

    public function setCodigo($codigo) {
        if (preg_match('/^[a-zA-Z0-9]{5}$/', $codigo)) {
            $this->codigo = $codigo;
        } else {
            throw new PropiedadInvalidaException("El código no es válido.");
        }
    }

    public function setCodigoMesa($codigoMesa) {
        if (preg_match('/^[a-zA-Z0-9]{5}$/', $codigoMesa)) {
            $this->codigoMesa = $codigoMesa;
        } else {
            throw new PropiedadInvalidaException("El código de mesa no es válido.");
        }
    }

    public function setEstado($estado) {
        $estado = strtolower($estado);
        $estadosValidos = array(
            'pendiente',
            'en preparacion',
            'listo para servir'
        );
    
        if (in_array($estado, $estadosValidos)) {
            $this->estado = $estado;
        } else {
            throw new PropiedadInvalidaException("El estado no es válido.");
        }
    }

    public function setTiempoEstimado($tiempoEstimado) {
        if (is_int($tiempoEstimado)) {
            $this->tiempoEstimado = $tiempoEstimado;
        } else {
            throw new PropiedadInvalidaException("El tiempo estimado no es válido.");
        }
    }

    public function setIdMozo($idMozo){
        $idMozo = intval($idMozo);
        if (is_int($idMozo)) {
            $this->idMozo = $idMozo;
        } else {
            throw new PropiedadInvalidaException("El ID del mozo no es válido.");
        }
    }

    public function setFoto($foto) {
        if (is_string($foto)) {
            $this->foto = $foto;
        } else {
            throw new PropiedadInvalidaException("La ruta de la foto no es válida.");
        }
    }


    public function Alta()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO pedidos (codigo, codigoMesa, estado, tiempoEstimado, idMozo, foto) 
                                                        VALUES (:codigo, :codigoMesa, :estado, :tiempoEstimado, :idMozo, :foto)");
        $consulta->bindValue(':codigo', $this->codigo, PDO::PARAM_STR);
        $consulta->bindValue(':codigoMesa', $this->codigoMesa, PDO::PARAM_STR);
        $consulta->bindValue(':estado', $this->estado, PDO::PARAM_STR);
        $consulta->bindValue(':tiempoEstimado', $this->tiempoEstimado, PDO::PARAM_INT);
        $consulta->bindValue(':idMozo', $this->idMozo, PDO::PARAM_INT);
        $consulta->bindValue(':foto', $this->foto, PDO::PARAM_STR);
        $consulta->execute();
    }

    public static function obtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT codigo, codigoMesa, estado, tiempoEstimado, idMozo, foto FROM pedidos");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Pedido');
    }

    public static function obtenerPedido($idPedido)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT codigo, codigoMesa, estado, tiempoEstimado, idMozo, foto FROM pedidos WHERE id = :idPedido");
        $consulta->bindParam(':idPedido', $idPedido, PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Pedido');
    }

    public static function ObtenerPedidosListos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consultaUpdate = $objAccesoDatos->prepararConsulta("UPDATE mesas m
                                                        JOIN pedidos AS p ON p.CodigoMesa = m.Codigo
                                                        SET m.Estado = 'cliente comiendo'
                                                        WHERE p.Estado = 'listo para servir'");
        $consultaUpdate->execute();


        $consultaSelect = $objAccesoDatos->prepararConsulta("SELECT codigo, codigoMesa, estado, tiempoEstimado, idMozo, foto FROM pedidos WHERE Estado = 'listo para servir'");
        $consultaSelect->execute();

        return $consultaSelect->fetchAll(PDO::FETCH_CLASS, 'Pedido');
    }

    public static function ModificarEstadoPedido($codigo, $estado)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consultaSelect = $objAccesoDatos->prepararConsulta("UPDATE pedidos AS p
                                                                SET p.tiempoEstimado = (
                                                                    SELECT MAX(pp.tiempoPreparacion) 
                                                                    FROM producto_pedido AS pp 
                                                                    WHERE pp.idPedido = p.ID
                                                                ),
                                                                p.estado = :estado
                                                                WHERE EXISTS (
                                                                    SELECT 1 
                                                                    FROM producto_pedido AS pp 
                                                                    WHERE pp.idPedido = p.ID AND p.codigo = :codigo
                                                                );");
        $consultaSelect->bindParam(':estado', $estado, PDO::PARAM_STR);
        $consultaSelect->bindParam(':codigo', $codigo, PDO::PARAM_STR);
        $consultaSelect->execute();
    }

    public static function ConsultaCliente($codigoMesa, $codigoPedido)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT TiempoEstimado
                                                        FROM pedidos
                                                        WHERE Codigo = :codigoPedido AND CodigoMesa = :codigoMesa");
        $consulta->bindParam(':codigoPedido', $codigoPedido, PDO::PARAM_STR);
        $consulta->bindParam(':codigoMesa', $codigoMesa, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchColumn();
    }

}