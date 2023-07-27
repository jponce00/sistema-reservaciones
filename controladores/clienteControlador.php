<?php
    if ($peticionAjax) {
        require_once "../modelos/clienteModelo.php";
    } else {
        require_once "./modelos/clienteModelo.php";
    }

    class clienteControlador extends clienteModelo {
        /*=== Funcion para mandar alertas de error ===*/
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

        /*----------- Controlador para agregar cliente -----------*/
        public function agregar_cliente_controlador() {
            /*=== Almacenar en variables lo que se manda del cliente y limpiarlo ===*/
            $dni = MainModel::limpiar_cadena($_POST["cliente_dni_reg"]);
            $nombre = MainModel::limpiar_cadena($_POST["cliente_nombre_reg"]);
            $apellido = MainModel::limpiar_cadena($_POST["cliente_apellido_reg"]);
            $telefono = MainModel::limpiar_cadena($_POST["cliente_telefono_reg"]);
            $direccion = MainModel::limpiar_cadena($_POST["cliente_direccion_reg"]);

            /*=== Comprobar cadenas vacias ===*/
            if ($dni == "" || $nombre == "" || $apellido == "" || $telefono == "") {
                $this->enviar_alerta_error("No ha llenado todos los campos obligatorios.");
            }

            /*=== Comprobar la validez de los datos ===*/
            if (MainModel::verificar_datos("[0-9-]{10,27}", $dni)) {
                $this->enviar_alerta_error("El DNI no coincide con el formato solicitado.");
            }

            if (MainModel::verificar_datos("[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{1,40}", $nombre)) {
                $this->enviar_alerta_error("El Nombre no coincide con el formato solicitado.");
            }

            if (MainModel::verificar_datos("[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{1,40}", $apellido)) {
                $this->enviar_alerta_error("El Apellido no coincide con el formato solicitado.");
            }

            if (MainModel::verificar_datos("[0-9()+]{8,20}", $telefono)) {
                $this->enviar_alerta_error("El Teléfono no coincide con el formato solicitado.");
            }

            if ($direccion != "") {
                if (MainModel::verificar_datos("[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ().,#\- ]{1,150}", $direccion)) {
                    $this->enviar_alerta_error("La Dirección no coincide con el formato solicitado.");
                }
            }

            /*=== Verificar que no haya otro registro con el mismo DNI ===*/
            $check_dni = MainModel::ejecutar_consulta_simple("SELECT cliente_dni FROM cliente WHERE cliente_dni='$dni'");
            if ($check_dni->rowCount() > 0) {
                $this->enviar_alerta_error("El DNI ingresado ya se encuentra registrado en el sistema.");
            }

            /*=== Preparar y enviar los datos al modelo ===*/
            $datos_cliente_reg = [
                "dni"=> $dni,
                "nombre"=> $nombre,
                "apellido"=> $apellido,
                "telefono"=> $telefono,
                "direccion"=> $direccion
            ];

            $agregar_cliente = clienteModelo::agregar_cliente_modelo($datos_cliente_reg);

            if ($agregar_cliente->rowCount() == 1) {
                $alerta = [
                    "Alerta"=> "limpiar",
                    "Titulo"=> "Cliente registrado",
                    "Texto"=> "Los datos del cliente han sido agregados con éxito.",
                    "Tipo"=> "success"
                ];
                echo json_encode($alerta);
            } else {
                $this->enviar_alerta_error("No hemos podido registrar el cliente.");
            }

        } /* Fin del controlador */

        /*----------- Controlador para paginar la tabla de clientes -----------*/
        public function paginador_cliente_controlador($pagina, $registros, $privilegio, $url, $busqueda) {
            $pagina = MainModel::limpiar_cadena($pagina); // Pagina actual
            $registros = MainModel::limpiar_cadena($registros); // Numero de registros a mostrar en una pagina
            $privilegio = MainModel::limpiar_cadena($privilegio); // Privilegios con que cuenta el usuario
            $url = MainModel::limpiar_cadena($url); // Nombre de la vista en que estamos
            $url = SERVERURL . $url . "/";
            $busqueda = MainModel::limpiar_cadena($busqueda); // Cadena con la busqueda en caso de que el usuario haga una busqueda

            $tabla = "";

            $pagina = (isset($pagina) && $pagina>0) ? (int) $pagina : 1;
            $inicio = ($pagina>0) ? (($pagina*$registros)-$registros) : 0;

            /*=== Asiganamos el tipo de consulta a la bd si viene o no una busqueda ===*/
            if (isset($busqueda) && $busqueda != "") {
                $consulta = "SELECT SQL_CALC_FOUND_ROWS * FROM cliente
                    WHERE cliente_dni LIKE '%$busqueda%' OR cliente_nombre LIKE '%$busqueda%' OR 
                    cliente_apellido LIKE '%$busqueda%' OR cliente_telefono LIKE '%$busqueda%' OR 
                    cliente_direccion LIKE '%$busqueda%' ORDER BY cliente_nombre ASC LIMIT $inicio, $registros";
            } else {
                $consulta = "SELECT SQL_CALC_FOUND_ROWS * FROM cliente ORDER BY cliente_nombre ASC LIMIT $inicio, $registros";
            }
            
            /*=== Hacemos la consulta a la base de datos ===*/
            $conexion = MainModel::conectar();
            $datos = $conexion->query($consulta);
            $datos = $datos->fetchAll();
            
            $total = $conexion->query("SELECT FOUND_ROWS()");
            $total = (int) $total->fetchColumn();

            $Npaginas = ceil($total / $registros);

            /*=== Rellenamos la tabla ===*/
            $tabla .= '
            <div class="table-responsive">
                <table class="table table-dark table-sm">
                    <thead>
                        <tr class="text-center roboto-medium">
                            <th>#</th>
                            <th>DNI</th>
                            <th>NOMBRE</th>                            
                            <th>TELEFONO</th>
                            <th>DIRECCIÓN</th>                                                       
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
                            <td>'.$rows['cliente_dni'].'</td>
                            <td>'.$rows['cliente_nombre'].' '.$rows['cliente_apellido'].'</td>                            
                            <td>'.$rows['cliente_telefono'].'</td>
                            <td>
                                <button type="button" class="btn btn-info" data-toggle="popover" data-trigger="hover" title="'.$rows['cliente_nombre'].'" data-content="'.$rows['cliente_direccion'].'">
                                    <i class="fas fa-info-circle"></i>
                                </button>
                            </td>                                                    
                    ';
                    if ($privilegio == 1 || $privilegio == 2) {
                        $tabla .= '
                            <td>
                                <a href="'.SERVERURL.'client-update/'.MainModel::encryption($rows['cliente_id']).'/" class="btn btn-success">
                                    <i class="fas fa-sync-alt"></i>	
                                </a>
                            </td>
                        ';
                    }
                    if ($privilegio == 1) {
                        $tabla .= '
                            <td>
                                <form class="FormularioAjax" action="'.SERVERURL.'ajax/clienteAjax.php" method="POST" data-form="delete" autocomplete="off">
                                    <input type="hidden" name="cliente_id_del" value="'.MainModel::encryption($rows['cliente_id']).'">
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
                                <a href="'.$url.'" class="btn btn-raised btn-primary btn-sm">Haga clic aquí para recargar el listado</a>
                                </td></tr>';
                } else {
                    $tabla .= '<tr class="text-center"><td colspan="8">No hay registros en el sistema</td></tr>';
                }
            }

            $tabla .= '</tbody></table></div>';

            if ($total >= 1 && $pagina <= $Npaginas) {
                $tabla .= '<p class="text-right">Mostrando cliente '.$reg_inicio.' al '.$reg_final.' de un total de '.$total.'</p>';
            }

            if ($total >= 1 && $pagina <= $Npaginas) {
                $tabla .= MainModel::paginador_tablas($pagina, $Npaginas, $url, 7);
            }

            return $tabla;

        } /* Fin del controlador */
        
        /*----------- Controlador para eliminar cliente -----------*/
        public function eliminar_cliente_controlador() {
            $id = MainModel::decryption($_POST["cliente_id_del"]);
            $id = MainModel::limpiar_cadena($id);

            /*=== Asegurarse que el cliente exista ===*/
            $check_cliente = MainModel::ejecutar_consulta_simple("SELECT cliente_id FROM cliente WHERE cliente_id='$id'");
            if ($check_cliente->rowCount() <= 0) {
                $this->enviar_alerta_error("El cliente que desea eliminar no existe en el sistema.");
            }

            /*=== Asegurarse que el cliente no tenga prestamos ===*/
            $check_prestamos = MainModel::ejecutar_consulta_simple("SELECT cliente_id FROM prestamo WHERE cliente_id='$id'");
            if ($check_prestamos->rowCount() >= 1) {
                $this->enviar_alerta_error("No se puede eliminar el cliente debido a que está relacionado con un préstamo.");
            }

            /*=== Comprobar privilegio del usuario que esta eliminando ===*/
            session_start(['name'=> 'SPM']);
            if ($_SESSION["privilegio_spm"] != 1) {
                $this->enviar_alerta_error("No cuenta con los permisos requeridos para realizar esta acción.");
            }

            /*=== Proceder a eliminar ===*/
            $eliminar_cliente = clienteModelo::eliminar_cliente_modelo($id);

            if ($eliminar_cliente->rowCount() == 1) {
                $alerta = [
                    "Alerta"=> "recargar",
                    "Titulo"=> "Cliente eliminado",
                    "Texto"=> "El cliente ha sido eliminado del sistema correctamente.",
                    "Tipo"=> "success"
                ];
                echo json_encode($alerta);
            } else {
                $this->enviar_alerta_error("No se ha podido eliminar el cliente, por favor intente nuevamente.");
            }

        } /* Fin del controlador */

        /*----------- Controlador para mostrar datos de cliente -----------*/
        public function datos_cliente_controlador($tipo, $id) {
            $tipo = MainModel::limpiar_cadena($tipo);
            $id = MainModel::decryption($id);
            $id = MainModel::limpiar_cadena($id);

            return clienteModelo::datos_cliente_modelo($tipo, $id);
        } /* Fin del controlador */

        /*----------- Controlador para actualizar datos de cliente -----------*/
        public function actualizar_cliente_controlador() {
            $id = MainModel::decryption($_POST["cliente_id_up"]);
            $id = MainModel::limpiar_cadena($id);

            /*=== Aseguarse de que el cliente exista en la base de datos ===*/
            $check_client = MainModel::ejecutar_consulta_simple("SELECT * FROM cliente WHERE cliente_id='$id'");
            if ($check_client->rowCount() <= 0) {
                $this->enviar_alerta_error("No hemos encontrado el cliente en el sistema.");
            } else {
                $campos = $check_client->fetch();
            }

            $dni = MainModel::limpiar_cadena($_POST["cliente_dni_up"]);
            $nombre = MainModel::limpiar_cadena($_POST["cliente_nombre_up"]);
            $apellido = MainModel::limpiar_cadena($_POST["cliente_apellido_up"]);
            $telefono = MainModel::limpiar_cadena($_POST["cliente_telefono_up"]);
            $direccion = MainModel::limpiar_cadena($_POST["cliente_direccion_up"]);

            /*=== Comprobar campos vacios ===*/
            if ($dni == "" || $nombre == "" || $apellido == "" || $telefono == "") {
                $this->enviar_alerta_error("No se han llenado todos los campos obligatorios.");
            }

            /*=== Verificar integridad de los datos ===*/
            if (MainModel::verificar_datos("[0-9-]{10,27}", $dni)) {
                $this->enviar_alerta_error("El DNI no coincide con el formato solicitado.");
            }

            if (MainModel::verificar_datos("[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{1,40}", $nombre)) {
                $this->enviar_alerta_error("El Nombre no coincide con el formato solicitado.");
            }

            if (MainModel::verificar_datos("[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{1,40}", $apellido)) {
                $this->enviar_alerta_error("El Apellido no coincide con el formato solicitado.");
            }

            if (MainModel::verificar_datos("[0-9()+]{8,20}", $telefono)) {
                $this->enviar_alerta_error("El Teléfono no coincide con el formato solicitado.");
            }

            if ($direccion != "") {
                if (MainModel::verificar_datos("[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ().,#\- ]{1,150}", $direccion)) {
                    $this->enviar_alerta_error("La Dirección no coincide con el formato solicitado.");
                }
            }

            /*=== Comprobar DNI repetido ===*/
            if ($dni != $campos["cliente_dni"]) {
                $check_dni = MainModel::ejecutar_consulta_simple("SELECT cliente_dni FROM cliente WHERE cliente_dni='$dni'");
                if ($check_dni->rowCount() > 0) {
                    $this->enviar_alerta_error("El DNI ingresado ya se encuentra registrado en el sistema.");
                }
            }

            /*=== Comprobar el privilegio ===*/
            session_start(['name'=> 'SPM']);
            if ($_SESSION['privilegio_spm'] != 1 && $_SESSION['privilegio_spm'] != 2) {
                $this->enviar_alerta_error("No tienes los permisos necesarios para realizar esta acción.");
            }

            /*=== Preparar y enviar datos al modelo ===*/
            $datos_cliente_up = [
                "dni"=> $dni,
                "nombre"=> $nombre,
                "apellido"=> $apellido,
                "telefono"=> $telefono,
                "direccion"=> $direccion,
                "id"=> $id
            ];

            if (clienteModelo::actualizar_cliente_modelo($datos_cliente_up)) {
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