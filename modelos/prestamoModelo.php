<?php 
    require_once "MainModel.php";

    class prestamoModelo extends MainModel {
        /*----------- Modelo para agregar prestamos -----------*/
        protected static function agregar_prestamo_modelo($datos) {
            $sql = MainModel::conectar()->prepare("INSERT INTO prestamo (prestamo_codigo, prestamo_fecha_inicio, prestamo_hora_inicio, prestamo_fecha_final, prestamo_hora_final, prestamo_cantidad, prestamo_total, prestamo_pagado, prestamo_estado, prestamo_observacion, usuario_id, cliente_id) VALUES (:Codigo, :FechaInicio, :HoraInicio, :FechaFinal, :HoraFinal, :Cantidad, :Total, :Pagado, :Estado, :Observacion, :Usuario, :Cliente)");

            $sql->bindParam(":Codigo", $datos["Codigo"]);
            $sql->bindParam(":FechaInicio", $datos["FechaInicio"]);
            $sql->bindParam(":HoraInicio", $datos["HoraInicio"]);
            $sql->bindParam(":FechaFinal", $datos["FechaFinal"]);
            $sql->bindParam(":HoraFinal", $datos["HoraFinal"]);
            $sql->bindParam(":Cantidad", $datos["Cantidad"]);
            $sql->bindParam(":Total", $datos["Total"]);
            $sql->bindParam(":Pagado", $datos["Pagado"]);
            $sql->bindParam(":Estado", $datos["Estado"]);
            $sql->bindParam(":Observacion", $datos["Observacion"]);
            $sql->bindParam(":Usuario", $datos["Usuario"]);
            $sql->bindParam(":Cliente", $datos["Cliente"]);

            $sql->execute();
            return $sql;
        }

        /*----------- Modelo para agregar detalle de prestamo -----------*/
        protected static function agregar_detalle_modelo($datos) {
            $sql = MainModel::conectar()->prepare("INSERT INTO detalle (detalle_cantidad, detalle_formato, detalle_tiempo, detalle_costo_tiempo, detalle_descripcion, prestamo_codigo, item_id) VALUES (:Cantidad, :Formato, :Tiempo, :Costo, :Descripcion, :Prestamo, :Item)");

            $sql->bindParam(":Cantidad", $datos["Cantidad"]);
            $sql->bindParam(":Formato", $datos["Formato"]);
            $sql->bindParam(":Tiempo", $datos["Tiempo"]);
            $sql->bindParam(":Costo", $datos["Costo"]);
            $sql->bindParam(":Descripcion", $datos["Descripcion"]);
            $sql->bindParam(":Prestamo", $datos["Prestamo"]);
            $sql->bindParam(":Item", $datos["Item"]);

            $sql->execute();
            return $sql;
        }

        /*----------- Modelo para agregar pago de prestamo -----------*/
        protected static function agregar_pago_modelo($datos) {
            $sql = MainModel::conectar()->prepare("INSERT INTO pago (pago_total, pago_fecha, prestamo_codigo) VALUES (:Total, :Fecha, :Codigo)");

            $sql->bindParam(":Total", $datos["Total"]);
            $sql->bindParam(":Fecha", $datos["Fecha"]);
            $sql->bindParam(":Codigo", $datos["Codigo"]);

            $sql->execute();
            return $sql;
        }

        /*----------- Modelo para eliminar prestamo -----------*/
        protected static function eliminar_prestamo_modelo($codigo, $tipo) {
            if ($tipo == "Prestamo") {
                $sql = MainModel::conectar()->prepare("DELETE FROM prestamo WHERE prestamo_codigo=:Codigo");                
            } else if ($tipo == "Detalle") {
                $sql = MainModel::conectar()->prepare("DELETE FROM detalle WHERE prestamo_codigo=:Codigo");
            } else if ($tipo == "Pago") {
                $sql = MainModel::conectar()->prepare("DELETE FROM pago WHERE prestamo_codigo=:Codigo");
            }

            $sql->bindParam(":Codigo", $codigo);
            $sql->execute();

            return $sql;
        }

        /*----------- Modelo para seleccionar datos de prestamo -----------*/
        protected static function datos_prestamo_modelo($tipo, $id) {
            if ($tipo == "Unico") {
                $sql = MainModel::conectar()->prepare("SELECT * FROM prestamo WHERE prestamo_id=:Id");
                $sql->bindParam(":Id", $id);
            } else if ($tipo == "Conteo_Reservacion") {
                $sql = MainModel::conectar()->prepare("SELECT prestamo_id FROM prestamo WHERE prestamo_estado='Reservacion'");
            } else if ($tipo == "Conteo_Prestamos") {
                $sql = MainModel::conectar()->prepare("SELECT prestamo_id FROM prestamo WHERE prestamo_estado='Prestamo'");
            } else if ($tipo == "Conteo_Finalizado") {
                $sql = MainModel::conectar()->prepare("SELECT prestamo_id FROM prestamo WHERE prestamo_estado='Finalizado'");
            } else if ($tipo == "Conteo") {
                $sql = MainModel::conectar()->prepare("SELECT prestamo_id FROM prestamo");
            } else if ($tipo == "Detalle") {
                $sql = MainModel::conectar()->prepare("SELECT * FROM detalle WHERE prestamo_codigo=:Codigo");
                $sql->bindParam(":Codigo", $id);
            } else if ($tipo == "Pago") {
                $sql = MainModel::conectar()->prepare("SELECT * FROM pago WHERE prestamo_codigo=:Codigo");
                $sql->bindParam(":Codigo", $id);
            }

            $sql->execute();
            return $sql;
        }

        /*----------- Modelo para actualizar datos de prestamo -----------*/
        protected static function actualizar_prestamo_modelo($datos) {
            if ($datos['Tipo'] == "Pago") {
                $sql = MainModel::conectar()->prepare("UPDATE prestamo SET prestamo_pagado=:Monto WHERE prestamo_codigo=:Codigo");
                $sql->bindParam(":Monto", $datos['Monto']);
            } else if ($datos['Tipo'] == "Prestamo") {
                $sql = MainModel::conectar()->prepare("UPDATE prestamo SET prestamo_estado=:Estado, prestamo_observacion=:Observacion WHERE prestamo_codigo=:Codigo");
                $sql->bindParam(":Estado", $datos['Estado']);
                $sql->bindParam(":Observacion", $datos['Observacion']);
            }

            $sql->bindParam(":Codigo", $datos['Codigo']);
            $sql->execute();

            return $sql;
        }
    }
?>