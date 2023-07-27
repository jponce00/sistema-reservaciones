<?php
    require_once "MainModel.php";

    class itemModelo extends MainModel {
        /*----------- Modelo para agregar items -----------*/
        protected static function agregar_item_modelo($datos) {
            $sql = MainModel::conectar()->prepare("INSERT INTO item (item_codigo, item_nombre, item_stock, item_estado, item_detalle) 
                    VALUES (:codigo, :nombre, :stock, :estado, :detalle)");
            
            $sql->bindParam(":codigo", $datos["codigo"]);
            $sql->bindParam(":nombre", $datos["nombre"]);
            $sql->bindParam(":stock", $datos["stock"]);
            $sql->bindParam(":estado", $datos["estado"]);
            $sql->bindParam(":detalle", $datos["detalle"]);
            $sql->execute();

            return $sql;
        }

        /*----------- Modelo para eliminar items -----------*/
        protected static function eliminar_item_modelo($id) {
            $sql = MainModel::conectar()->prepare("DELETE FROM item WHERE item_id=:id");

            $sql->bindParam(":id", $id);
            $sql->execute();

            return $sql;
        }

        /*----------- Modelo para mostrar datos de un item -----------*/
        protected static function datos_item_modelo($tipo, $id) {
            if ($tipo == "Unico") {
                $sql = MainModel::conectar()->prepare("SELECT * FROM item WHERE item_id=:id");
                $sql->bindParam(":id", $id);
            } else if ($tipo == "Conteo") {
                $sql = MainModel::conectar()->prepare("SELECT item_id FROM item");
            }

            $sql->execute();
            return $sql;
        }

        /*----------- Modelo para actualizar datos de un item -----------*/
        protected static function actualizar_item_modelo($datos) {
            $sql = MainModel::conectar()->prepare("UPDATE item SET item_codigo=:codigo, item_nombre=:nombre, item_stock=:stock, item_estado=:estado, item_detalle=:detalle WHERE item_id=:id");

            $sql->bindParam(":codigo", $datos["codigo"]);
            $sql->bindParam(":nombre", $datos["nombre"]);
            $sql->bindParam(":stock", $datos["stock"]);
            $sql->bindParam(":estado", $datos["estado"]);
            $sql->bindParam(":detalle", $datos["detalle"]);
            $sql->bindParam(":id", $datos["id"]);
            $sql->execute();

            return $sql;
        }

    }

?>