<?php
require_once './db/AccesoDb.php';
require_once './exceptions/PropiedadInvalidaExcepcion.php';

class Encuesta{
    public $codigoMesa;
    public $codigoPedido;
    public $valoracionMesa;
    public $valoracionRestaurante;
    public $valoracionMozo;
    public $valoracionCocinero;
    public $opinion;

    public function setCodigoMesa($codigo) {
        if (preg_match('/^[a-zA-Z0-9]{5}$/', $codigo)) {
            $this->codigoMesa = $codigo;
        } else {
            throw new PropiedadInvalidaException("Codigo de mesa no válido.");
        }
    }

    public function setCodigoPedido($codigo) {
        if (preg_match('/^[a-zA-Z0-9]{5}$/', $codigo)) {
            $this->codigoPedido = $codigo;
        } else {
            throw new PropiedadInvalidaException("Codigo de pedido no válido.");
        }
    }

    public function setValoracionMesa($valoracion) {
        if (is_numeric($valoracion) && $valoracion >= 1 && $valoracion <= 10) {
            $this->valoracionMesa = $valoracion;
        } else {
            throw new PropiedadInvalidaException("Valoración de mesa no válida. Debe estar entre 1 y 10.");
        }
    }

    public function setValoracionRestaurante($valoracion) {
        if (is_numeric($valoracion) && $valoracion >= 1 && $valoracion <= 10) {
            $this->valoracionRestaurante = $valoracion;
        } else {
            throw new PropiedadInvalidaException("Valoración de restaurante no válida. Debe estar entre 1 y 10.");
        }
    }

    public function setValoracionMozo($valoracion) {
        if (is_numeric($valoracion) && $valoracion >= 1 && $valoracion <= 10) {
            $this->valoracionMozo = $valoracion;
        } else {
            throw new PropiedadInvalidaException("Valoración de mozo no válida. Debe estar entre 1 y 10.");
        }
    }

    public function setValoracionCocinero($valoracion) {
        if (is_numeric($valoracion) && $valoracion >= 1 && $valoracion <= 10) {
            $this->valoracionCocinero = $valoracion;
        } else {
            throw new PropiedadInvalidaException("Valoración de cocinero no válida. Debe estar entre 1 y 10.");
        }
    }

    public function setOpinion($opinion) {
        if (strlen($opinion) <= 66) {
            $this->opinion = $opinion;
        } else {
            throw new PropiedadInvalidaException("La opinión debe tener como máximo 66 caracteres.");
        }
    }

    public function Cargar()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO encuesta (codigoMesa, codigoPedido, valoracionMesa, valoracionRestaurante, valoracionMozo, valoracionCocinero, opinion) 
                                                        VALUES (:codigoMesa, :codigoPedido, :valoracionMesa, :valoracionRestaurante, :valoracionMozo, :valoracionCocinero, :opinion)");
        $consulta->bindValue(':codigoMesa', $this->codigoMesa, PDO::PARAM_STR);
        $consulta->bindValue(':codigoPedido', $this->codigoPedido, PDO::PARAM_STR);
        $consulta->bindValue(':valoracionMesa', $this->valoracionMesa, PDO::PARAM_INT);
        $consulta->bindValue(':valoracionRestaurante', $this->valoracionRestaurante, PDO::PARAM_INT);
        $consulta->bindValue(':valoracionMozo', $this->valoracionMozo, PDO::PARAM_INT);
        $consulta->bindValue(':valoracionCocinero', $this->valoracionCocinero, PDO::PARAM_INT);
        $consulta->bindValue(':opinion', $this->opinion, PDO::PARAM_STR);
        $consulta->execute();
    }

    public static function MejoresComentarios(){
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT *
                                                        FROM Encuesta
                                                        WHERE CodigoMesa IN (
                                                            SELECT CodigoMesa
                                                            FROM Encuesta
                                                            GROUP BY CodigoMesa
                                                            HAVING AVG((ValoracionMesa + ValoracionRestaurante + ValoracionMozo + ValoracionCocinero) / 4) > 7
                                                        )");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

}