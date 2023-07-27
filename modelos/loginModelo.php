<?php
    require_once "mainModel.php";

    class loginModelo extends mainModel {
        /*----------- Modelo para iniciar sesion -----------*/
        protected static function iniciar_sesion_modelo($datos) {
            $sql = mainModel::conectar()->prepare("SELECT * FROM usuario WHERE usuario_usuario = :usuario AND usuario_clave = :clave AND usuario_estado = 'Activa'");
            $sql->bindParam(":usuario", $datos["usuario"]);
            $sql->bindParam(":clave", $datos["clave"]);
            $sql->execute();
            return $sql;
        }
    }

?>