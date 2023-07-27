<?php
    require_once "MainModel.php";

    class clienteModelo extends MainModel {
        /*----------- Modelo para agregar cliente -----------*/
        protected static function agregar_cliente_modelo($datos) {
            $sql = MainModel::conectar()->prepare("INSERT INTO cliente (cliente_dni, cliente_nombre, cliente_apellido, cliente_telefono, cliente_direccion) 
                                    VALUES (:dni, :nombre, :apellido, :telefono, :direccion)");

            $sql->bindParam(":dni", $datos["dni"]);
            $sql->bindParam(":nombre", $datos["nombre"]);
            $sql->bindParam(":apellido", $datos["apellido"]);
            $sql->bindParam(":telefono", $datos["telefono"]);
            $sql->bindParam(":direccion", $datos["direccion"]);
            $sql->execute();

            return $sql;
        }
        
        /*----------- Modelo para eliminar cliente -----------*/
        protected static function eliminar_cliente_modelo($id) {
            $sql = MainModel::conectar()->prepare("DELETE FROM cliente WHERE cliente_id=:id");

            $sql->bindParam(":id", $id);
            $sql->execute();

            return $sql;
        }

        /*----------- Modelo para mostrar datos del cliente -----------*/
        protected static function datos_cliente_modelo($tipo, $id) {
            if ($tipo == "Unico") {
                $sql = MainModel::conectar()->prepare("SELECT * FROM cliente WHERE cliente_id=:id");
                $sql->bindParam(":id", $id);
            } else if ($tipo == "Conteo") {
                $sql = MainModel::conectar()->prepare("SELECT cliente_id FROM cliente");
            }     
            $sql->execute();
            return $sql;
        }

        /*----------- Modelo para actualizar datos del cliente -----------*/
        protected static function actualizar_cliente_modelo($datos) {
            $sql = MainModel::conectar()->prepare("UPDATE cliente SET cliente_dni=:dni, cliente_nombre=:nombre, cliente_apellido=:apellido,
                    cliente_telefono=:telefono, cliente_direccion=:direccion WHERE cliente_id=:id");
            
            $sql->bindParam(":dni", $datos["dni"]);
            $sql->bindParam(":nombre", $datos["nombre"]);
            $sql->bindParam(":apellido", $datos["apellido"]);
            $sql->bindParam(":telefono", $datos["telefono"]);
            $sql->bindParam(":direccion", $datos["direccion"]);
            $sql->bindParam(":id", $datos["id"]);
            $sql->execute();

            return $sql;
        }

    }

?>