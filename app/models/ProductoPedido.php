<?php
require_once './db/AccesoDb.php';
require_once './exceptions/PropiedadInvalidaExcepcion.php';
require_once './models/Producto.php';
require_once './models/Pedido.php';

class ProductoPedido{

    public $codigoPedido;
    public $idProducto;
    public $cantidad;
    public $estadoProducto;
    public $idEmpleado;
    public $tiempoPreparacion;

    public function setCodigoPedido($codigoPedido) {
        if (preg_match('/^[a-zA-Z0-9]{5}$/', $codigoPedido)) {
            $this->codigoPedido = $codigoPedido;
        } else {
            throw new PropiedadInvalidaException("El código no es válido.");
        }
    }

    public function setIdProducto($idProducto) {
        $idProducto = intval($idProducto);
        if (is_int($idProducto)) {
            $this->$idProducto = $idProducto;
        } else {
            throw new PropiedadInvalidaException("El ID Producto no es válido.");
        }
    }

    public function setCantidad($cantidad) {
        $cantidad = intval($cantidad);
        if (is_int($cantidad)) {
            $this->$cantidad = $cantidad;
        } else {
            throw new PropiedadInvalidaException("La cantidad ingresada no es válida.");
        }
    }

    public function setEstado($estadoProducto) {
        $estadoProducto = strtolower($estadoProducto);
        $estadosValidos = array(
            'pendiente',
            'en preparacion',
            'listo para servir'
        );
    
        if (in_array($estadoProducto, $estadosValidos)) {
            $this->estadoProducto = $estadoProducto;
        } else {
            throw new PropiedadInvalidaException("El estado no es válido.");
        }
    }

    
    public function setIdEmpleado($idEmpleado) {
        $idEmpleado = intval($idEmpleado);
        if (is_int($idEmpleado)) {
            $this->$idEmpleado = $idEmpleado;
        } else {
            throw new PropiedadInvalidaException("El ID Empleado no es válido.");
        }
    }

    public function setTiempoPreparacion($tiempoPreparacion) {
        $tiempoPreparacion = intval($tiempoPreparacion);
        if (is_int($tiempoPreparacion)) {
            $this->tiempoPreparacion = $tiempoPreparacion;
        } else {
            throw new PropiedadInvalidaException("El tiempo de preparacion no es válido.");
        }
    }

    public static function CompletarPedido($codigoPedido, $idProducto, $cantidad)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $tiempoPreparacion = Producto::ObtenerTiempoDePreparacion($idProducto);
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO producto_pedido (codigoPedido, idProducto, cantidad, estadoProducto, tiempoPreparacion) 
                                                        VALUES (:codigoPedido, :idProducto, :cantidad, :estadoProducto, :tiempoPreparacion)");
        $consulta->bindValue(':codigoPedido', $codigoPedido, PDO::PARAM_STR);
        $consulta->bindValue(':idProducto', $idProducto, PDO::PARAM_INT);
        $consulta->bindValue(':cantidad', $cantidad, PDO::PARAM_INT);
        $consulta->bindValue(':estadoProducto', 'pendiente', PDO::PARAM_STR);
        $consulta->bindValue(':tiempoPreparacion', $tiempoPreparacion, PDO::PARAM_INT);
        $consulta->execute();
    }

    public static function Asignar($codigo, $idEmpleado)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consultaUpdate = $objAccesoDatos->prepararConsulta("UPDATE producto_pedido AS pp
                                                                JOIN pedidos AS p ON pp.codigoPedido = p.codigo
                                                                JOIN usuarios AS u ON u.ID = :id
                                                                JOIN productos AS pr ON pp.idProducto = pr.ID
                                                                SET pp.idEmpleado = :updateId,
                                                                    pp.estadoProducto = 'en preparacion'
                                                                WHERE u.ID = :idWhere
                                                                    AND p.codigo = :codigo
                                                                    AND pr.sector = u.Sector
                                                                    AND pp.estadoProducto = 'pendiente'
                                                                LIMIT 1");
        $consultaUpdate->bindParam(':codigo', $codigo, PDO::PARAM_STR);
        $consultaUpdate->bindParam(':updateId', $idEmpleado, PDO::PARAM_INT);
        $consultaUpdate->bindParam(':idWhere', $idEmpleado, PDO::PARAM_INT);
        $consultaUpdate->bindParam(':id', $idEmpleado, PDO::PARAM_INT);
        $consultaUpdate->execute();

        $consultaSelect = $objAccesoDatos->prepararConsulta("SELECT codigoPedido, idProducto, cantidad, estadoProducto, idEmpleado, tiempoPreparacion 
                                                                FROM producto_pedido WHERE idEmpleado = :idEmpleado");
        $consultaSelect->bindParam(':idEmpleado', $idEmpleado, PDO::PARAM_INT);
        $consultaSelect->execute();

        if(self::ComprobarEstado($codigo, 'en preparacion')){
            Pedido::ModificarEstadoPedido($codigo, 'en preparacion');
        }

        return $consultaSelect->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function Finalizar($codigoPedido, $idEmpleado)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consultaUpdate = $objAccesoDatos->prepararConsulta("UPDATE producto_pedido AS pp
                                                                JOIN pedidos AS p ON pp.codigoPedido = p.codigo
                                                                SET pp.estadoProducto = 'listo para servir',
                                                                    pp.tiempoPreparacion = 0
                                                                WHERE pp.codigoPedido = :codigoPedido
                                                                AND pp.idEmpleado = :idEmpleado;");
        $consultaUpdate->bindParam(':codigoPedido', $codigoPedido, PDO::PARAM_STR);
        $consultaUpdate->bindParam(':idEmpleado', $idEmpleado, PDO::PARAM_INT);
        $consultaUpdate->execute();

        $consultaSelect = $objAccesoDatos->prepararConsulta("SELECT codigoPedido, idProducto, cantidad, estadoProducto, idEmpleado, tiempoPreparacion 
                                                                FROM producto_pedido WHERE idEmpleado = :idEmpleado");
        $consultaSelect->bindParam(':idEmpleado', $idEmpleado, PDO::PARAM_INT);
        $consultaSelect->execute();

        if(self::ComprobarEstado($codigoPedido, 'listo para servir')){
            Pedido::ModificarEstadoPedido($codigoPedido, 'listo para servir');
        }

        return $consultaSelect->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function ObtenerImporteTotal($codigoMesa){
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT SUM(p.precio) AS ImporteTotal
                                                        FROM mesas m
                                                        JOIN pedidos pe ON m.Codigo = pe.codigoMesa
                                                        JOIN producto_pedido pp ON pe.codigo = pp.codigoPedido
                                                        JOIN productos p ON pp.idProducto = p.ID
                                                        WHERE m.Codigo = :codigoMesa;");
        $consulta->bindParam(':codigoMesa', $codigoMesa, PDO::PARAM_STR);  
        $consulta->execute();

        return $consulta->fetchColumn();
    }

    private static function ComprobarEstado($codigo, $estado){
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT
                                                        CASE
                                                            WHEN COUNT(*) = SUM(CASE WHEN EstadoProducto = :estado THEN 1 ELSE 0 END) AND COUNT(*) > 0 THEN 1
                                                            ELSE 0
                                                        END AS todosEnPreparacion
                                                        FROM producto_pedido pp
                                                        JOIN pedidos p ON pp.codigoPedido = p.codigo
                                                        WHERE p.codigo = :codigo;");
        $consulta->bindParam(':estado', $estado, PDO::PARAM_STR);                                              
        $consulta->bindParam(':codigo', $codigo, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchColumn();
    }

    public static function EmpleadoOcupado($idEmpleado){
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT COUNT(*) AS NumPedidosAsociados
                                                        FROM producto_pedido
                                                        WHERE IdEmpleado = :id
                                                        AND estadoProducto = 'en preparacion';");
        $consulta->bindParam(':id', $idEmpleado, PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->fetchColumn();
    }
}