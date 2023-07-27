<?php
    if ($peticionAjax) {
        require_once "../modelos/itemModelo.php";
    } else {
        require_once "./modelos/itemModelo.php";
    }

    class itemControlador extends itemModelo {

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

        /*----------- Controlador para agregar items -----------*/
        public function agregar_item_controlador() {
            /*=== Almacenar en variables y limpiar cadenas ===*/
            $codigo = MainModel::limpiar_cadena($_POST["item_codigo_reg"]);
            $nombre = MainModel::limpiar_cadena($_POST["item_nombre_reg"]);
            $stock = MainModel::limpiar_cadena($_POST["item_stock_reg"]);
            $estado = MainModel::limpiar_cadena($_POST["item_estado_reg"]);
            $detalle = MainModel::limpiar_cadena($_POST["item_detalle_reg"]);

            /*=== Comprobar cadenas vacias ===*/
            if ($codigo == "" || $nombre == "" || $stock == "") {
                $this->enviar_alerta_error("No ha llenado todos los campos obligatorios.");
            }

            /*=== Comprobar la validez de los datos ===*/
            if (MainModel::verificar_datos("[a-zA-Z0-9-]{1,45}", $codigo)) {
                $this->enviar_alerta_error("El Código no coincide con el formato solicitado.");
            }

            if (MainModel::verificar_datos("[a-zA-záéíóúÁÉÍÓÚñÑ0-9 ]{1,140}", $nombre)) {
                $this->enviar_alerta_error("El Nombre no coincide con el formato solicitado.");
            }

            if (MainModel::verificar_datos("[0-9]{1,9}", $stock)) {
                $this->enviar_alerta_error("El Stock no coincide con el formato solicitado.");
            }            

            if ($detalle != "") {
                if (MainModel::verificar_datos("[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ().,#\- ]{1,190}", $detalle)) {
                    $this->enviar_alerta_error("El Detalle no coincide con el formato solicitado.");
                }
            }

            /*=== Comprobar codigo y nombre repetidos ===*/
            $check_codigo = MainModel::ejecutar_consulta_simple("SELECT item_codigo FROM item WHERE item_codigo='$codigo'");
            if ($check_codigo->rowCount() > 0) {
                $this->enviar_alerta_error("El código del ítem ingresado ya se encuentra registrado en el sistema.");
            }

            $check_nombre = MainModel::ejecutar_consulta_simple("SELECT item_nombre FROM item WHERE item_nombre='$nombre'");
            if ($check_nombre->rowCount() > 0) {
                $this->enviar_alerta_error("El nombre del ítem ingresado ya se encuentra registrado.");
            }

            /*=== Comprobar que el estado solo pueda ser Habilitado o Deshabilitado ===*/
            if ($estado != "Habilitado" && $estado != "Deshabilitado") {
                $this->enviar_alerta_error("El Estado seleccionado no es válido.");
            }

            /*=== Preparar datos que se enviaran al modelo ===*/
            $datos_item_reg = [
                "codigo"=> $codigo,
                "nombre"=> $nombre,
                "stock"=> $stock,
                "estado"=> $estado,
                "detalle"=> $detalle
            ];

            $agregar_item = itemModelo::agregar_item_modelo($datos_item_reg);       

            if ($agregar_item->rowCount() == 1) {
                $alerta = [
                    "Alerta"=> "limpiar",
                    "Titulo"=> "Ítem registrado",
                    "Texto"=> "Los datos del ítem se han registrado correctamente.",
                    "Tipo"=> "success"
                ];
                echo json_encode($alerta);
            } else {
                $this->enviar_alerta_error("No se ha podido registrar el ítem.");
            }

        } /* Fin del controlador */

        /*----------- Controlador para paginar items -----------*/
        public function paginador_item_controlador($pagina, $registros, $privilegio, $url, $busqueda) {
            $pagina = MainModel::limpiar_cadena($pagina); // pagina actual
            $registros = MainModel::limpiar_cadena($registros); // Cantidad de registros a mostrar en una pagina
            $privilegio = MainModel::limpiar_cadena($privilegio); // Privilegio que tiene el usuario activo
            $url = MainModel::limpiar_cadena($url); // Nombre de la vista en que estamos
            $url = SERVERURL . $url . "/";
            $busqueda = MainModel::limpiar_cadena($busqueda); // Cadena de busqueda en caso de que el usuario haga una busqueda

            $tabla = "";

            $pagina = (isset($pagina) && $pagina>0) ? (int) $pagina : 1;
            $inicio = ($pagina>0) ? (($pagina*$registros)-$registros) : 0;

            /*=== Asignamos el tipo de consulta que haremos a la base de datos ===*/
            if (isset($busqueda) && $busqueda != "") {
                $consulta = "SELECT SQL_CALC_FOUND_ROWS * FROM item
                    WHERE item_codigo LIKE '%$busqueda%' OR item_nombre LIKE '%$busqueda%'
                    OR item_detalle LIKE '%$busqueda%' ORDER BY item_codigo ASC LIMIT $inicio, $registros";
            } else {
                $consulta = "SELECT SQL_CALC_FOUND_ROWS * FROM item ORDER BY item_codigo ASC LIMIT $inicio, $registros";
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
                            <th>CÓDIGO</th>
                            <th>NOMBRE</th>
                            <th>STOCK</th>
                            <th>DETALLE</th>
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
                        <td>'.$rows["item_codigo"].'</td>
                        <td>'.$rows["item_nombre"].'</td>
                        <td>'.$rows["item_stock"].'</td>
                        <td>
                            <button type="button" class="btn btn-info" data-toggle="popover" data-trigger="hover" title="'.$rows["item_nombre"].'" data-content="'.$rows["item_detalle"].'">
                                <i class="fas fa-info-circle"></i>
                            </button>
                        </td>
                    ';
                    if ($privilegio == 1 || $privilegio == 2) {
                        $tabla .= '
                        <td>
                            <a href="'.SERVERURL.'item-update/'.MainModel::encryption($rows["item_id"]).'" class="btn btn-success">
                                <i class="fas fa-sync-alt"></i>
                            </a>
                        </td>
                        ';
                    }
                    if ($privilegio == 1) {
                        $tabla .= '
                        <td>
                            <form class="FormularioAjax" action="'.SERVERURL.'ajax/itemAjax.php" method="POST" data-form="delete" autocomplete="off">
                                <input type="hidden" name="item_id_del" value="'.MainModel::encryption($rows["item_id"]).'">
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
                    $tabla .= '<tr class="text-center"><td colspan="8">
                                <a href="'.$url.'" class="btn btn-raised btn-primary btn-sm">Haga clic aquí para actualizar el listado.</a>
                                </td></tr>';
                } else {
                    $tabla .= '<tr class="text-center"><td colspan="8">No hay registros en el sistema</td></tr>';
                }
            }

            $tabla .= '</tbody></table></div>';

            if ($total >= 1 && $pagina <= $Npaginas) {
                $tabla .= '<p class="text-right">Mostrando ítem '.$reg_inicio.' al '.$reg_final.' de un total de '.$total.'</p>';
                $tabla .= MainModel::paginador_tablas($pagina, $Npaginas, $url, 7);
            }

            return $tabla;


        } /* Fin del controlador */

        /*----------- Controlador para eliminar items -----------*/
        public function eliminar_item_controlador() {
            $id = MainModel::decryption($_POST["item_id_del"]);
            $id = MainModel::limpiar_cadena($id);

            /*=== Asegurarse de que el item exista ===*/
            $check_item = MainModel::ejecutar_consulta_simple("SELECT item_id from item WHERE item_id='$id'");
            if ($check_item->rowCount() <= 0) {
                $this->enviar_alerta_error("El ítem que desea eliminar no se encuentra en el sistema.");
            }

            /*=== Asegurarse de que el item no este algun prestamo ===*/
            $check_prestamos = MainModel::ejecutar_consulta_simple("SELECT item_id FROM detalle WHERE item_id='$id'");
            if ($check_prestamos->rowCount() >= 1) {
                $this->enviar_alerta_error("No se puede eliminar el ítem ya que está relacionado con un préstamo.");
            }

            /*=== Comprobar privilegio del usuario que esta eliminando ===*/
            session_start(['name'=> 'SPM']);
            if ($_SESSION['privilegio_spm'] != 1) {
                $this->enviar_alerta_error("No cuenta con los permisos requeridos para realizar esta acción");
            }

            /*=== Proceder a eliminar ===*/
            $eliminar_item = itemModelo::eliminar_item_modelo($id);

            if ($eliminar_item->rowCount() == 1) {
                $alerta = [
                    "Alerta"=> "recargar",
                    "Titulo"=> "Ítem eliminado",
                    "Texto"=> "El ítem ha sido eliminado del sistema correctamente.",
                    "Tipo"=> "success"
                ];
                echo json_encode($alerta);
            } else {
                $this->enviar_alerta_error("No se ha podido eliminar el ítem, por favor intente nuevamente.");
            }

        } /* Fin del controlador */


        /*----------- Controlador para mostrar datos de item -----------*/
        public function datos_item_controlador($tipo, $id) {
            $tipo = MainModel::limpiar_cadena($tipo);
            $id = MainModel::decryption($id);
            $id = MainModel::limpiar_cadena($id);

            return itemModelo::datos_item_modelo($tipo, $id);
        } /* Fin del controlador */


        /*----------- Controlador para actualizar items -----------*/
        public function actualizar_item_controlador() {
            $id = MainModel::decryption($_POST["item_id_up"]);
            $id = MainModel::limpiar_cadena($id);

            /*=== Corroborar que el item exista en la base de datos ===*/
            $check_item = MainModel::ejecutar_consulta_simple("SELECT * FROM item WHERE item_id='$id'");
            if ($check_item->rowCount() <= 0) {
                $this->enviar_alerta_error("No hemos encontrado el ítem en el sistema.");
            } else {
                $campos = $check_item->fetch();
            }

            $codigo = MainModel::limpiar_cadena($_POST["item_codigo_up"]);
            $nombre = MainModel::limpiar_cadena($_POST["item_nombre_up"]);
            $stock = MainModel::limpiar_cadena($_POST["item_stock_up"]);
            $estado = MainModel::limpiar_cadena($_POST["item_estado_up"]);
            $detalle = MainModel::limpiar_cadena($_POST["item_detalle_up"]);

            /*=== Comprobar datos vacios ===*/
            if ($codigo == "" || $nombre == "" || $stock == "") {
                $this->enviar_alerta_error("No se han llenado todos los campos obligatorios.");
            }

            /*=== Verificar integridad de los datos ===*/
            if (MainModel::verificar_datos("[a-zA-Z0-9-]{1,45}", $codigo)) {
                $this->enviar_alerta_error("El código no coincide con el formato solicitado.");
            }

            if (MainModel::verificar_datos("[a-zA-záéíóúÁÉÍÓÚñÑ0-9 ]{1,140}", $nombre)) {
                $this->enviar_alerta_error("El Nombre no coincide con el formato solicitado.");
            }

            if (MainModel::verificar_datos("[0-9]{1,9}", $stock)) {
                $this->enviar_alerta_error("El Stock no coincide con el formato solicitado.");
            }

            if ($detalle != "") {
                if (MainModel::verificar_datos("[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ().,#\- ]{1,190}", $detalle)) {
                    $this->enviar_alerta_error("El Detalle no coincide con el formato solicitado.");
                }
            }

            /*=== Comprobar que el codigo y el nombre no vayan repetidos ===*/
            if ($codigo != $campos["item_codigo"]) {
                $check_codigo = MainModel::ejecutar_consulta_simple("SELECT item_codigo FROM item WHERE item_codigo='$codigo'");
                if ($check_codigo->rowCount() > 0) {
                    $this->enviar_alerta_error("El código ingresado ya se encuentra registrado en el sistema.");
                }
            }

            if ($nombre != $campos["item_nombre"]) {
                $check_nombre = MainModel::ejecutar_consulta_simple("SELECT item_nombre FROM item WHERE item_nombre='$nombre'");
                if ($check_nombre->rowCount() > 0) {
                    $this->enviar_alerta_error("El Nombre ingresado ya se encuentra registrado en el sistema.");
                }
            }

            /*=== Comprobar que el estado solo se Habilitado o Deshabilitado ===*/
            if ($estado != "Habilitado" && $estado != "Deshabilitado") {
                $this->enviar_alerta_error("El Estado seleccionado no es válido.");
            }

            /*=== Preparar y enviar los datos al modelo ===*/
            $datos_item_up = [
                "codigo"=> $codigo,
                "nombre"=> $nombre,
                "stock"=> $stock,
                "estado"=> $estado,
                "detalle"=> $detalle,
                "id"=> $id
            ];

            if (itemModelo::actualizar_item_modelo($datos_item_up)) {
                $alerta = [
                    "Alerta"=> "recargar",
                    "Titulo"=> "Datos actualizados",
                    "Texto"=> "Los datos han sido actualizados con éxito.",
                    "Tipo"=> "success"
                ];
                echo json_encode($alerta);
            } else {
                $this->enviar_alerta_error("No hemos podido actualizar los datos, por favor intente nuevamente.");
            }

        } /* Fin del controlador */
    }

?>