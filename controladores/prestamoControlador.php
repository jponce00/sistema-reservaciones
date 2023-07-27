<?php 
    if ($peticionAjax) {
        require_once "../modelos/prestamoModelo.php";
    } else {
        require_once "./modelos/prestamoModelo.php";
    }

    class prestamoControlador extends prestamoModelo {
        
        /*=== Funcion para enviar alertas de error ===*/
        private function enviar_alerta_error($texto) {
            $alerta = [
                "Alerta"=> "simple",
                "Titulo"=> "Ocurrió un error inesperado",
                "Texto"=> $texto,
                "Tipo"=> "error"
            ];
            echo json_encode($alerta);
            exit();
        }

        /*----------- Controlador para buscar clientes para prestamos -----------*/
        public function buscar_cliente_prestamo_controlador() {
            /* Recuperar el texto que estamos enviando */
            $cliente = MainModel::limpiar_cadena($_POST["buscar_cliente"]);

            /* Comprobar que el texto no este vacio */
            if ($cliente == "") {
                return '<div class="alert alert-warning" role="alert">
                    <p class="text-center mb-0">
                        <i class="fas fa-exclamation-triangle fa-2x"></i><br>
                        Debes introducir el DNI, Nombre, Apellido o Teléfono
                    </p>
                </div>';
                exit();
            }

            /* Seleccionado clientes en la bd */
            $datos_cliente = MainModel::ejecutar_consulta_simple("SELECT * FROM cliente WHERE cliente_dni LIKE '%$cliente%' OR cliente_nombre LIKE '%$cliente%' OR cliente_apellido LIKE '%$cliente%' OR cliente_telefono LIKE '%$cliente%' ORDER BY cliente_nombre ASC");

            if ($datos_cliente->rowCount() >= 1) {
                $datos_cliente = $datos_cliente->fetchAll();

                $tabla = '<div class="table-responsive"><table class="table table-hover table-bordered table-sm"><tbody>';

                foreach($datos_cliente as $rows) {
                    $tabla .= '<tr class="text-center">
                        <td>'.$rows["cliente_nombre"].' '.$rows["cliente_apellido"].' - '.$rows["cliente_dni"].'</td>
                        <td>
                            <button type="button" class="btn btn-primary" onclick="agregar_cliente('.$rows["cliente_id"].')"><i class="fas fa-user-plus"></i></button>
                        </td>
                    </tr>';
                }

                $tabla .= '</tbody></table></div>';

                return $tabla;
            } else {
                return '<div class="alert alert-warning" role="alert">
                    <p class="text-center mb-0">
                        <i class="fas fa-exclamation-triangle fa-2x"></i><br>
                        No hemos encontrado ningún cliente en el sistema que coincida con <strong>"'.$cliente.'"</strong>
                    </p>
                </div>';
                exit();
            }

        } /* Fin del controlador */

        /*----------- Controlador para agregar cliente para prestamos -----------*/
        public function agregar_cliente_prestamo_controlador() {
            /* Recuperar el id */
            $id = MainModel::limpiar_cadena($_POST["id_agregar_cliente"]);

            /* Comprobando que el cliente este en la bd */
            $check_cliente = MainModel::ejecutar_consulta_simple("SELECT * FROM cliente WHERE cliente_id='$id'");

            if ($check_cliente->rowCount() <= 0) {
                $this->enviar_alerta_error("No hemos podido encontrar el cliente en la base de datos.");
            } else {
                $campos = $check_cliente->fetch();
            }

            /* Iniciando la sesion */
            session_start(['name'=> 'SPM']);
            
            if (empty($_SESSION['datos_cliente'])) {
                $_SESSION['datos_cliente'] = [
                    "ID"=> $campos["cliente_id"],
                    "DNI"=> $campos["cliente_dni"],
                    "Nombre"=> $campos["cliente_nombre"],
                    "Apellido"=> $campos["cliente_apellido"]
                ];
                $alerta = [
                    "Alerta"=> "recargar",
                    "Titulo"=> "Cliente agregado",
                    "Texto"=> "El cliente se agregó para realizar un préstamo o reservación.",
                    "Tipo"=> "success"
                ];
                echo json_encode($alerta);
            } else {
                $this->enviar_alerta_error("No hemos podido agregar el cliente al préstamo.");
            }


        } /* Fin del controlador */

        /*----------- Controlador para eliminar cliente para prestamos -----------*/
        public function eliminar_cliente_prestamo_controlador() {
            /* Iniciando la sesion */
            session_start(['name'=> 'SPM']);

            unset($_SESSION["datos_cliente"]);

            if (empty($_SESSION["datos_cliente"])) {
                $alerta = [
                    "Alerta"=> "recargar",
                    "Titulo"=> "Cliente removido",
                    "Texto"=> "Los datos del cliente se han removido con éxito.",
                    "Tipo"=> "success"
                ];
                echo json_encode($alerta);
            } else {
                $this->enviar_alerta_error("No hemos podido remover los datos del cliente.");
            }


        } /* Fin del controlador */

        /*----------- Controlador para buscar item para prestamos -----------*/
        public function buscar_item_prestamo_controlador() {
            /* Recuperar el texto que estamos enviando */
            $item = MainModel::limpiar_cadena($_POST["buscar_item"]);

            /* Comprobar que el texto no este vacio */
            if ($item == "") {
                return '<div class="alert alert-warning" role="alert">
                    <p class="text-center mb-0">
                        <i class="fas fa-exclamation-triangle fa-2x"></i><br>
                        Debes introducir el DNI, Nombre, Apellido o Teléfono
                    </p>
                </div>';
                exit();
            }

            /* Seleccionado items en la bd */
            $datos_item = MainModel::ejecutar_consulta_simple("SELECT * FROM item WHERE (item_codigo LIKE '%$item%' OR item_nombre LIKE '%$item%') AND (item_estado = 'Habilitado') ORDER BY item_nombre ASC");

            if ($datos_item->rowCount() >= 1) {
                $datos_item = $datos_item->fetchAll();

                $tabla = '<div class="table-responsive"><table class="table table-hover table-bordered table-sm"><tbody>';

                foreach($datos_item as $rows) {
                    $tabla .= '<tr class="text-center">
                        <td>'.$rows["item_codigo"].' - '.$rows["item_nombre"].'</td>
                        <td>
                            <button type="button" class="btn btn-primary" onclick="modal_agregar_item('.$rows["item_id"].')"><i class="fas fa-box-open"></i>
                        </td>
                    </tr>';
                }

                $tabla .= '</tbody></table></div>';

                return $tabla;
            } else {
                return '<div class="alert alert-warning" role="alert">
                    <p class="text-center mb-0">
                        <i class="fas fa-exclamation-triangle fa-2x"></i><br>
                        No hemos encontrado ningún ítem en el sistema que coincida con <strong>"'.$item.'"</strong>
                    </p>
                </div>';
                exit();
            }

        } /* Fin del controlador */

        /*----------- Controlador para agregar item para prestamos -----------*/
        public function agregar_item_prestamo_controlador() {
            /* Recuperando id del item */
            $id = MainModel::limpiar_cadena($_POST["id_agregar_item"]);

            /* Comprobando item en la bd */
            $check_item = MainModel::ejecutar_consulta_simple("SELECT * FROM item WHERE item_id='$id' AND item_estado='Habilitado'");

            if ($check_item->rowCount() <= 0) {
                $this->enviar_alerta_error("No hemos podido seleccionar el ítem, por favor intente nuevamente.");
            } else {
                $campos = $check_item->fetch();
            }

            /* Recuperando detalles del prestamo */
            $formato = MainModel::limpiar_cadena($_POST["detalle_formato"]);
            $cantidad = MainModel::limpiar_cadena($_POST["detalle_cantidad"]);
            $tiempo = MainModel::limpiar_cadena($_POST["detalle_tiempo"]);
            $costo = MainModel::limpiar_cadena($_POST["detalle_costo_tiempo"]);

            /* Comprobando campos vacios */
            if ($cantidad == "" || $tiempo == "" || $costo == "") {
                $this->enviar_alerta_error("No ha llenado todos los campos obligatorios.");
            }

            /* Verificando integridad de los datos */
            if (MainModel::verificar_datos("[0-9]{1,7}", $cantidad)) {
                $this->enviar_alerta_error("La Cantidad no coincide con el formato solicitado");
            }

            if (MainModel::verificar_datos("[0-9]{1,7}", $tiempo)) {
                $this->enviar_alerta_error("El Tiempo no coincide con el formato solicitado.");
            }

            if (MainModel::verificar_datos("[0-9.]{1,15}", $costo)) {
                $this->enviar_alerta_error("El Costo no coincide con el formato solicitado.");
            }

            if ($formato != "Horas" && $formato != "Dias" && $formato != "Evento" && $formato != "Mes") {
                $this->enviar_alerta_error("El Formato ingresado no es válido.");
            }

            session_start(['name'=> 'SPM']);

            if (empty($_SESSION['datos_item'][$id])) {
                $costo = number_format($costo, 2, '.', '');

                $_SESSION["datos_item"][$id] = [
                    "ID"=> $campos['item_id'],
                    "Codigo"=> $campos['item_codigo'],
                    "Nombre"=> $campos['item_nombre'],
                    "Detalle"=> $campos['item_detalle'],
                    "Formato"=> $formato,
                    "Cantidad"=> $cantidad,
                    "Tiempo"=> $tiempo,
                    "Costo"=> $costo
                ];

                $alerta = [
                    "Alerta"=> "recargar",
                    "Titulo"=> "Ítem agregado",
                    "Texto"=> "El ítem ha sido agregado para realizar un préstamo.",
                    "Tipo"=> "success"
                ];
                echo json_encode($alerta);
                exit();
            } else {
                $this->enviar_alerta_error("El ítem que intenta agregar ya se encuentra seleccionado.");
            }

        } /* Fin del controlador */

        /*----------- Controlador para eliminar item para prestamos -----------*/
        public function eliminar_item_prestamo_controlador() {
            /* Recuperar el id del item */
            $id = MainModel::limpiar_cadena($_POST["id_eliminar_item"]);

            /* Iniciando la sesion */
            session_start(['name'=> 'SPM']);

            /* Limpiamos la variable de sesion */
            unset($_SESSION['datos_item'][$id]);

            if (empty($_SESSION['datos_item'][$id])) {
                $alerta = [
                    "Alerta"=> "recargar",
                    "Titulo"=> "Ítem removido",
                    "Texto"=> "Los datos del ítem se han removido con éxito.",
                    "Tipo"=> "success"
                ];
                echo json_encode($alerta);
                exit();
            } else {
                $this->enviar_alerta_error("No hemos podido remover los datos del ítem.");
            }

        } /* Fin del controlador */

        /*----------- Controlador para datos de prestamos -----------*/
        public function datos_prestamo_controlador($tipo, $id) {
            $tipo = MainModel::limpiar_cadena($tipo, $id);

            $id = MainModel::decryption($id);
            $id = MainModel::limpiar_cadena($id);

            return prestamoModelo::datos_prestamo_modelo($tipo, $id);

        } /* Fin del controlador */

        /*----------- Controlador para agregar prestamos -----------*/
        public function agregar_prestamo_controlador() {
            /* Iniciando la sesion */
            session_start(['name'=> 'SPM']);

            /* Comprobando items */
            if ($_SESSION["prestamo_items"] == 0) {
                $this->enviar_alerta_error("No has seleccionado ningún ítem para realizar el préstamo.");
            }

            /* Comprobando cliente */
            if (empty($_SESSION["datos_cliente"])) {
                $this->enviar_alerta_error("No ha seleccionado ningún cliente.");
            }

            /* Recibiendo datos del formulario */
            $fecha_inicio = MainModel::limpiar_cadena($_POST["prestamo_fecha_inicio_reg"]);
            $hora_inicio = MainModel::limpiar_cadena($_POST["prestamo_hora_inicio_reg"]);
            $fecha_final = MainModel::limpiar_cadena($_POST["prestamo_fecha_final_reg"]);
            $hora_final = MainModel::limpiar_cadena($_POST["prestamo_hora_final_reg"]);
            $estado = MainModel::limpiar_cadena($_POST["prestamo_estado_reg"]);
            $total_pagado = MainModel::limpiar_cadena($_POST["prestamo_pagado_reg"]);
            $observacion = MainModel::limpiar_cadena($_POST["prestamo_observacion_reg"]);

            /* Comprobando integridad de los datos */
            if (MainModel::verificar_fecha($fecha_inicio)) {
                $this->enviar_alerta_error("La fecha de inicio no coincide con el formato solicitado.");
            }
            if (MainModel::verificar_datos("([0-1][0-9]|[2][0-3])[\:]([0-5][0-9])", $hora_inicio)) {
                $this->enviar_alerta_error("La hora de inicio no coincide con el formato solicitado.");
            }
            if (MainModel::verificar_fecha($fecha_final)) {
                $this->enviar_alerta_error("La fecha final no coincide con el formato solicitado.");
            }
            if (MainModel::verificar_datos("([0-1][0-9]|[2][0-3])[\:]([0-5][0-9])", $hora_final)) {
                $this->enviar_alerta_error("La hora final no coincide con el formato solicitado.");
            }
            if (MainModel::verificar_datos("[0-9.]{1,10}", $total_pagado)) {
                $this->enviar_alerta_error("El total depositado no coincide con el formato solicitado");
            }
            if ($observacion != "") {
                if (MainModel::verificar_datos("[a-zA-z0-9áéíóúÁÉÍÓÚñÑ#() ]{1,400}", $observacion)) {
                    $this->enviar_alerta_error("La observación no coincide con el formato solicitado");
                }
            }
            if ($estado != "Reservacion" && $estado != "Prestamo" && $estado != "Finalizado") {
                $this->enviar_alerta_error("El estado seleccionado no es correcto.");
            }

            /* Comprobar que la fecha final sea mayor a la fecha inicial */
            if (strtotime($fecha_final) < strtotime($fecha_inicio)) {
                $this->enviar_alerta_error("La fecha de entrega no puede ser menor que la fecha de inicio del préstamo.");
            }

            /* Formatenado totakes, fechas y horas */
            $total_prestamo = number_format($_SESSION["prestamo_total"], 2, '.', '');
            $total_pagado = number_format($total_pagado, 2, '.', '');

            $fecha_inicio = date("Y-m-d", strtotime($fecha_inicio));
            $fecha_final = date("Y-m-d", strtotime($fecha_final));

            $hora_inicio = date("h:i a", strtotime($hora_inicio));
            $hora_final = date("h:i a", strtotime($hora_final));

            /* Generando codigo para el prestamo */
            $correlativo = MainModel::ejecutar_consulta_simple("SELECT prestamo_id from prestamo");
            $correlativo = ($correlativo->rowCount()) + 1;
            $codigo = MainModel::generar_codigo_aleatorio("CP", 7, $correlativo);

            $datos_prestamo_reg = [
                "Codigo"=> $codigo,
                "FechaInicio"=> $fecha_inicio,
                "HoraInicio"=> $hora_inicio,
                "FechaFinal"=> $fecha_final,
                "HoraFinal"=> $hora_final,
                "Cantidad"=> $_SESSION["prestamo_items"],
                "Total"=> $total_prestamo,
                "Pagado"=> $total_pagado,
                "Estado"=> $estado,
                "Observacion"=> $observacion,
                "Usuario"=> $_SESSION["id_spm"],
                "Cliente"=> $_SESSION["datos_cliente"]["ID"]
            ];

            /* Agregar los datos a la tabla prestamo */
            $agregar_prestamo = prestamoModelo::agregar_prestamo_modelo($datos_prestamo_reg);

            if ($agregar_prestamo->rowCount() != 1) {
                $this->enviar_alerta_error("No hemos podido registrar el préstamo (Error: 001), por favor intente nuevamente.");
            }

            /* Agregar el pago */
            if ($total_pagado > 0) {
                $datos_pago_reg = [
                    "Total"=> $total_pagado,
                    "Fecha"=> $fecha_inicio,
                    "Codigo"=> $codigo
                ];
                $agregar_pago = prestamoModelo::agregar_pago_modelo($datos_pago_reg);

                if ($agregar_pago->rowCount() != 1) {
                    prestamoModelo::eliminar_prestamo_modelo($codigo, "Prestamo");
                    $this->enviar_alerta_error("No hemos podido registrar el préstamo (Error: 002), por favor intente nuevamente.");
                }
            }

            /* Agregar el detalle del prestamo */
            $errores_detalle = 0;
            foreach($_SESSION["datos_item"] as $items) {
                $costo = number_format($items["Costo"], 2, '.', '');
                $descripcion = $items["Codigo"] . " " . $items["Nombre"];

                $datos_detalle_reg = [
                    "Cantidad"=> $items["Cantidad"],
                    "Formato"=> $items["Formato"],
                    "Tiempo"=> $items["Tiempo"],
                    "Costo"=> $costo,
                    "Descripcion"=> $descripcion,
                    "Prestamo"=> $codigo,
                    "Item"=> $items["ID"]
                ];

                $agregar_detalle = prestamoModelo::agregar_detalle_modelo($datos_detalle_reg);

                if ($agregar_detalle->rowCount() != 1) {
                    $errores_detalle = 1;
                    break;
                }
            }

            if ($errores_detalle == 0) {
                unset($_SESSION["datos_cliente"]);
                unset($_SESSION["datos_item"]);
                $alerta = [
                    "Alerta"=> "recargar",
                    "Titulo"=> "Préstamo registrado",
                    "Texto"=> "Los datos del préstamo han sido registrados en el sistema.",
                    "Tipo"=> "success"
                ];
                echo json_encode($alerta);                
            } else {
                prestamoModelo::eliminar_prestamo_modelo($codigo, "Detalle");
                prestamoModelo::eliminar_prestamo_modelo($codigo, "Pago");
                prestamoModelo::eliminar_prestamo_modelo($codigo, "Prestamo");
                $this->enviar_alerta_error("No hemos podido registrar el préstamo (Error: 003), por favor intente nuevamente.");
            }

        } /* Fin del controlador */

        /*----------- Controlador para paginar prestamos -----------*/
        public function paginador_prestamos_controlador($pagina, $registros, $privilegio, $url, $tipo, $fecha_inicio, $fecha_final) {
            $pagina = MainModel::limpiar_cadena($pagina);
            $registros = MainModel::limpiar_cadena($registros);
            $privilegio = MainModel::limpiar_cadena($privilegio);

            $url = MainModel::limpiar_cadena($url);
            $url = SERVERURL . $url . "/";

            $tipo = MainModel::limpiar_cadena($tipo);
            $fecha_inicio = MainModel::limpiar_cadena($fecha_inicio);
            $fecha_final = MainModel::limpiar_cadena($fecha_final);

            $tabla = "";

            $pagina = (isset($pagina) && $pagina>0) ? (int) $pagina : 1;
            $inicio = ($pagina>0) ? (($pagina*$registros)-$registros) : 0;

            /*=== Comprobar que las fechas esten correctas ===*/
            if ($tipo == "Busqueda") {
                if (MainModel::verificar_fecha($fecha_inicio) || MainModel::verificar_fecha($fecha_final)) {
                    return '
                        <div class="alert alert-danger text-center" role="alert">
                            <p><i class="fas fa-exclamation-triangle fa-5x"></i></p>
                            <h4 class="alert-heading">¡Ocurrió un error inesperado!</h4>
                            <p class="mb-0">Lo sentimos, no podemos realizar la búsqueda ya que ha ingresado una fecha incorrecta.</p>
                        </div>
                    ';
                    exit();
                }
            }

            $campos = "p.prestamo_id, p.prestamo_codigo, p.prestamo_fecha_inicio, p.prestamo_fecha_final, p.prestamo_total, p.prestamo_pagado, p.prestamo_estado, p.usuario_id, p.cliente_id, c.cliente_nombre, c.cliente_apellido";

            /*=== Asignamos el tipo de consulta que haremos a la base de datos ===*/
            if ($tipo == "Busqueda" && $fecha_inicio != "" && $fecha_final != "") {
                $consulta = "SELECT SQL_CALC_FOUND_ROWS $campos FROM prestamo as p INNER JOIN cliente as c ON p.cliente_id=c.cliente_id WHERE (p.prestamo_fecha_inicio BETWEEN '$fecha_inicio' AND '$fecha_final') ORDER BY p.prestamo_fecha_inicio DESC LIMIT $inicio, $registros";
            } else {
                $consulta = "SELECT SQL_CALC_FOUND_ROWS $campos FROM prestamo as p INNER JOIN cliente as c ON p.cliente_id=c.cliente_id WHERE p.prestamo_estado='$tipo' ORDER BY p.prestamo_fecha_inicio DESC LIMIT $inicio, $registros";
            }

            /*=== Hacemos la consulta a la DB ===*/
            $conexion = MainModel::conectar();
            $datos = $conexion->query($consulta);
            $datos = $datos->fetchAll();

            // Total de registros:
            $total = $conexion->query("SELECT FOUND_ROWS()");
            $total = (int) $total->fetchColumn();

            // Total de paginas:
            $Npaginas = ceil($total / $registros);

            $tabla .= '
            <div class="table-responsive">
                <table class="table table-dark table-sm">
                    <thead>
                        <tr class="text-center roboto-medium">
                            <th>#</th>
                            <th>CLIENTE</th>
                            <th>FECHA DE PRÉSTAMO</th>
                            <th>FECHA DE ENTREGA</th>
                            <th>TIPO</th>
                            <th>ESTADO</th>
                            <th>FACTURA</th>
            ';
            if ($privilegio == 1 || $privilegio == 2) {
                $tabla .= '<th>ACTUALIZAR</th>';
            }                            
            
            if ($privilegio == 1) {
                $tabla .= '<th>ELIMINAR</th>';
            }

            $tabla .= '
                        </tr>
                    </thead>
                    <tbody>
            ';

            if ($total >= 1 && $pagina <= $Npaginas) {
                $contador = $inicio + 1;
                $reg_inicio = $inicio + 1;
                foreach($datos as $rows) {
                    $tabla .= '
                    <tr class="text-center" >
                        <td>'.$contador.'</td>
                        <td>'.$rows["cliente_nombre"].' '.$rows["cliente_apellido"].'</td>
                        <td>'.date('d-m-Y', strtotime($rows["prestamo_fecha_inicio"])).'</td>
                        <td>'.date('d-m-Y', strtotime($rows["prestamo_fecha_final"])).'</td>
                        <td>'.$rows["prestamo_estado"].'</td>
                    ';
                    
                    if ($rows["prestamo_pagado"] < $rows["prestamo_total"]) {
                        $tabla .= '<td>Pendiente: <span class="badge badge-light">'.MONEDA . number_format(($rows["prestamo_total"] - $rows["prestamo_pagado"]), 2, '.', ',') .'</span></td>';
                    } else {
                        $tabla .= '<td><span class="badge badge-light">Cancelado</span></td>';
                    }

                    $tabla .= '
                        <td>
                            <a href="'.SERVERURL.'facturas/invoice.php?id='.MainModel::encryption($rows["prestamo_id"]).'" class="btn btn-info" target="_blank">
                                    <i class="fas fa-file-pdf"></i> 
                            </a>
                        </td>
                    ';

                    if ($privilegio == 1 || $privilegio == 2) {
                        if ($rows["prestamo_estado"] == "Finalizado" && $rows["prestamo_pagado"]==$rows["prestamo_total"]) {
                            $tabla .= '
                                <td>
                                    <button class="btn btn-success" disabled>
                                        <i class="fas fa-sync-alt"></i>
                                    </button>
                                </td>
                            ';
                        } else {
                            $tabla .= '
                                <td>
                                    <a href="'.SERVERURL.'reservation-update/'.MainModel::encryption($rows["prestamo_id"]).'" class="btn btn-success">
                                        <i class="fas fa-sync-alt"></i>
                                    </a>
                                </td>
                            ';
                        }                        
                    }
                    if ($privilegio == 1) {
                        $tabla .= '
                        <td>
                            <form class="FormularioAjax" action="'.SERVERURL.'ajax/prestamoAjax.php" method="POST" data-form="delete" autocomplete="off">
                                <input type="hidden" name="prestamo_codigo_del" value="'.MainModel::encryption($rows["prestamo_codigo"]).'">
                                <button type="submit" class="btn btn-warning">
                                    <i class="far fa-trash-alt"></i>
                                </button>
                            </form>
                        </td>
                        ';
                    }
                    $tabla .= '</tr>';
                    $contador ++;
                }
                $reg_final = $contador - 1;
            } else {
                if ($total >= 1) {
                    $tabla .= '<tr class="text-center"><td colspan="9">
                                <a href="'.$url.'" class="btn btn-raised btn-primary btn-sm">Haga clic aquí para actualizar el listado.</a>
                                </td></tr>';
                } else {
                    $tabla .= '<tr class="text-center"><td colspan="8">No hay registros en el sistema</td></tr>';
                }
            }

            $tabla .= '</tbody></table></div>';

            if ($total >= 1 && $pagina <= $Npaginas) {
                $tabla .= '<p class="text-right">Mostrando préstamos '.$reg_inicio.' al '.$reg_final.' de un total de '.$total.'</p>';
                $tabla .= MainModel::paginador_tablas($pagina, $Npaginas, $url, 7);
            }

            return $tabla;


        } /* Fin del controlador */

        /*----------- Controlador para eliminar prestamos -----------*/
        public function eliminar_prestamo_controlador() {
            /* Recibiendo codigo del prestamo */
            $codigo = MainModel::decryption($_POST["prestamo_codigo_del"]);
            $codigo = MainModel::limpiar_cadena($codigo);

            /* Asegurarse de que el codigo exista en la base de datos */
            $check_prestamo = MainModel::ejecutar_consulta_simple("SELECT prestamo_codigo FROM prestamo WHERE prestamo_codigo='$codigo'");
            if ($check_prestamo->rowCount() <= 0) {
                $this->enviar_alerta_error("El préstamo que intenta eliminar no se encuentra registrado en el sistema.");
            }

            /* Comprobar los privilegios del usuario */
            session_start(['name'=> 'SPM']);
            if ($_SESSION["privilegio_spm"] != 1) {
                $this->enviar_alerta_error("No cuenta con los permisos necesarios para ejecutar esta acción.");
            }

            /* Comprobando y eliminando los pagos */
            $check_pagos = MainModel::ejecutar_consulta_simple("SELECT prestamo_codigo FROM pago WHERE prestamo_codigo='$codigo'");
            $check_pagos = $check_pagos->rowCount();
            if ($check_pagos > 0) {
                $eliminar_pagos = prestamoModelo::eliminar_prestamo_modelo($codigo, "Pago");
                if ($eliminar_pagos->rowCount() != $check_pagos) {
                    $this->enviar_alerta_error("No hemos podido eliminar el préstamo, por favor intente nuevamente.");
                }
            }

            /* Comprobando y eliminando los detalles del prestamo */
            $check_detalles = MainModel::ejecutar_consulta_simple("SELECT prestamo_codigo FROM detalle WHERE prestamo_codigo='$codigo'");
            $check_detalles = $check_detalles->rowCount();
            if ($check_detalles > 0) {
                $eliminar_detalles = prestamoModelo::eliminar_prestamo_modelo($codigo, "Detalle");
                if ($eliminar_detalles->rowCount() != $check_detalles) {
                    $this->enviar_alerta_error("No hemos podido eliminar el préstamo, por favor intente nuevamente.");
                }
            }

            /* Eliminando de la tabla prestamo */
            $eliminar_prestamo = prestamoModelo::eliminar_prestamo_modelo($codigo, "Prestamo");
            if ($eliminar_prestamo->rowCount() == 1) {
                $alerta = [
                    "Alerta"=> "recargar",
                    "Titulo"=> "Préstamo eliminado",
                    "Texto"=> "El préstamo ha sido eliminado del sistema correctamente.",
                    "Tipo"=> "success"
                ];
                echo json_encode($alerta);
                exit();
            } else {
                $this->enviar_alerta_error("No hemos podido eliminar el préstamo, por favor intente nuevamente.");
            }

        } /* Fin del controlador */

        /*----------- Controlador para agregar pagos -----------*/
        public function agregar_pago_controlador() {
            /* Recibiendo datos */
            $codigo = MainModel::decryption($_POST["pago_codigo_reg"]);
            $codigo = MainModel::limpiar_cadena($codigo);
            $monto = MainModel::limpiar_cadena($_POST["pago_monto_reg"]);
            $monto = number_format($monto, 2, '.', '');

            /* Comprobando que el pago sea mayor a cero */
            if ($monto <= 0) {
                $this->enviar_alerta_error("El pago debe de ser mayor a 0.");
            }

            /* Comprobar que el prestamo exista en la base de datos */
            $datos_prestamo = MainModel::ejecutar_consulta_simple("SELECT * FROM prestamo WHERE prestamo_codigo='$codigo'");
            if ($datos_prestamo->rowCount() <= 0) {
                $this->enviar_alerta_error("El préstamo al cual intenta agregar el pago no existe en el sistema.");
            } else {
                $datos_prestamo = $datos_prestamo->fetch();
            }

            /* Comprobar que el monto no sea mayor a lo que falta por pagar */
            $pendiente = number_format(($datos_prestamo["prestamo_total"] - $datos_prestamo["prestamo_pagado"]), 2, '.', '');
            if ($monto > $pendiente) {
                $this->enviar_alerta_error("El monto que acaba de ingresar supera el saldo pendiente que tiene este préstamo.");
            }

            /* Comprobar privilegios del usuario */
            session_start(['name'=> 'SPM']);
            if ($_SESSION["privilegio_spm"] < 1 || $_SESSION["privilegio_spm"] > 2) {
                $this->enviar_alerta_error("No tienes los permisos necesarios para realizar esta operación.");
            }

            /* Calculando el total a pagar y la fecha */
            $total_pagado = number_format(($monto + $datos_prestamo["prestamo_pagado"]), 2, '.', '');
            $fecha = date('Y-m-d');

            $datos_pago_reg = [
                "Total"=> $monto,
                "Fecha"=> $fecha,
                "Codigo"=> $codigo
            ];

            $agregar_pago = prestamoModelo::agregar_pago_modelo($datos_pago_reg);

            if ($agregar_pago->rowCount() == 1) {
                $datos_prestamo_up = [
                    "Tipo"=> "Pago",
                    "Monto"=> $total_pagado,
                    "Codigo"=> $codigo
                ];
                if (prestamoModelo::actualizar_prestamo_modelo($datos_prestamo_up)) {
                    $alerta = [
                        "Alerta"=> "recargar",
                        "Titulo"=> "Préstamo actualizado",
                        "Texto"=> "El pago de ".MONEDA.$monto." se ha realizado con éxito.",
                        "Tipo"=> "success"
                    ];
                    echo json_encode($alerta);
                    exit();
                } else {
                    prestamoModelo::eliminar_prestamo_modelo($codigo, "Pago");
                    $this->enviar_alerta_error("No hemos podido registrar el pago.");
                }
            } else {
                $this->enviar_alerta_error("No hemos podido agregar el pago.");
            }

        } /* Fin del controlador */

        /*----------- Controlador para actualizar prestamos -----------*/
        public function actualizar_prestamo_controlador() {
            /* Recibiendo codigo */
            $codigo = MainModel::decryption($_POST["prestamo_codigo_up"]);
            $codigo = MainModel::limpiar_cadena($codigo);

            /* Comprobando que el prestamo exista en la base de datos */
            $check_prestamo = MainModel::ejecutar_consulta_simple("SELECT prestamo_codigo FROM prestamo WHERE prestamo_codigo='$codigo'");
            if ($check_prestamo->rowCount() <= 0) {
                $this->enviar_alerta_error("El préstamo que intenta actualizar no existe en el sistema.");
            }

            /* Recibir los datos a actualizar del prestamo */
            $estado = MainModel::limpiar_cadena($_POST["prestamo_estado_up"]);
            $observacion = MainModel::limpiar_cadena($_POST["prestamo_observacion_up"]);

            /* Comprobar la integridad de los datos */
            if ($observacion != "") {
                if (MainModel::verificar_datos("[a-zA-z0-9áéíóúÁÉÍÓÚñÑ#() ]{1,400}", $observacion)) {
                    $this->enviar_alerta_error("La Observación no cuenta con el formato solicitado.");
                }
            }

            if ($estado != "Reservacion" && $estado != "Prestamo" && $estado != "Finalizado") {
                $this->enviar_alerta_error("El Estado no cuenta con el formato solicitado.");
            }

            /* Comprobando privilegios */
            session_start(['name'=> 'SPM']);
            if ($_SESSION["privilegio_spm"] < 1 || $_SESSION["privilegio_spm"] > 2) {
                $this->enviar_alerta_error("No cuenta con los permisos requeridos para realizar esta operación");
            }

            $datos_prestamo_up = [
                "Tipo"=> "Prestamo",
                "Estado"=> $estado,
                "Observacion"=> $observacion,
                "Codigo"=> $codigo
            ];

            if (prestamoModelo::actualizar_prestamo_modelo($datos_prestamo_up)) {
                $alerta = [
                    "Alerta"=> "recargar",
                    "Titulo"=> "Préstamo actualizado",
                    "Texto"=> "El préstamo ha sido actualizado con éxito.",
                    "Tipo"=> "success"
                ];
                echo json_encode($alerta);
                exit();
            } else {
                $this->enviar_alerta_error("No hemos podido actualizar el préstamo.");
            }


        } /* Fin del controlador */

    }

?>