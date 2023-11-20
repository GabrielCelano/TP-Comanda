<?php
require_once './db/AccesoDb.php';

class DbController{
    public static function AuthLogin($usuario, $rol){
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT EXISTS (
                                                            SELECT 1 FROM usuarios
                                                            WHERE Nombre = :usuario AND Rol = :rol
                                                        ) AS user_exists;");
        $consulta->bindValue(':usuario', $usuario, PDO::PARAM_STR);
        $consulta->bindValue(':rol', $rol, PDO::PARAM_STR);
        $consulta->execute();
        return $consulta->fetchColumn();
    }

    public static function AuthSuspendido($usuario, $rol){
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT EXISTS (
                                                            SELECT 1 FROM usuarios
                                                            WHERE Nombre = :usuario AND Rol = :rol AND Suspendido != 1
                                                        ) AS user_exists;");
        $consulta->bindValue(':usuario', $usuario, PDO::PARAM_STR);
        $consulta->bindValue(':rol', $rol, PDO::PARAM_STR);
        $consulta->execute();
        return $consulta->fetchColumn();
    }
}