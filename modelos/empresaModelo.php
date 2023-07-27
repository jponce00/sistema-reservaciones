<?php
    require_once "MainModel.php";

    class empresaModelo extends MainModel {
        /*----------- Modelo para datos de empresa -----------*/
        protected static function datos_empresa_modelo() {
            $sql = MainModel::conectar()->prepare("SELECT * FROM empresa");
            $sql->execute();
            return $sql;
        }

        /*----------- Modelo para crear empresa -----------*/
        protected static function agregar_empresa_modelo($datos) {
            $sql = MainModel::conectar()->prepare("INSERT INTO empresa (empresa_nombre, empresa_email, empresa_telefono, empresa_direccion) VALUES (:nombre, :email, :telefono, :direccion)");

            $sql->bindParam(":nombre", $datos["nombre"]);
            $sql->bindParam(":email", $datos["email"]);
            $sql->bindParam(":telefono", $datos["telefono"]);
            $sql->bindParam(":direccion", $datos["direccion"]);

            $sql->execute();
            return $sql;
        }

        /*----------- Modelo para actualizar empresa -----------*/
        protected static function actualizar_empresa_modelo($datos) {
            $sql = MainModel::conectar()->prepare("UPDATE empresa SET empresa_nombre=:nombre, empresa_email=:email, empresa_telefono=:telefono, empresa_direccion=:direccion WHERE empresa_id=:id");

            $sql->bindParam(":nombre", $datos["nombre"]);
            $sql->bindParam(":email", $datos["email"]);
            $sql->bindParam(":telefono", $datos["telefono"]);
            $sql->bindParam(":direccion", $datos["direccion"]);
            $sql->bindParam(":id", $datos["id"]);

            $sql->execute();
            return $sql;
        }
    }

?>