<?php
class PropiedadInvalidaException extends Exception {
    public function __construct($mensaje = "Error en la construcción del objeto", $codigo = 0, Exception $anterior = null) {
        parent::__construct($mensaje, $codigo, $anterior);
    }
}
?>