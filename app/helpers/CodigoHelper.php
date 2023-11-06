<?php
class CodigoHelper {
    public static function generarCodigoUnico($longitud = 5, $caracteres = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ') {
        $codigo = '';
        $caracteresLength = strlen($caracteres);

        // Genera el código alfanumérico de la longitud especificada
        for ($i = 0; $i < $longitud; $i++) {
            $codigo .= $caracteres[rand(0, $caracteresLength - 1)];
        }

        // Verifica si el código generado ya existe y, en caso afirmativo, lo regenera
        while (self::codigoYaExiste($codigo)) {
            $codigo = self::generarCodigoUnico($longitud, $caracteres);
        }

        return $codigo;
    }

    public static function codigoYaExiste($codigo) {
        $codigosExistentes = self::obtenerCodigosExistentes(); // Debes implementar esta función en tu aplicación

        return in_array($codigo, $codigosExistentes);
    }

    // Implementa esta función para obtener la lista de códigos existentes
    private static function obtenerCodigosExistentes() {
        // Aquí puedes implementar la lógica para obtener la lista de códigos existentes desde tu aplicación
        // Por ejemplo, desde una base de datos o un sistema de almacenamiento.
        return []; // Por defecto, devuelve una lista vacía.
    }
}