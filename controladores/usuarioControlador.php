<?php
    if ($peticionAjax) {
        require_once "../modelos/usuarioModelo.php";
    } else {
        require_once "./modelos/usuarioModelo.php";
    }

    class usuarioControlador extends usuarioModelo {
        /*----------- Controlador para agregar usuarios -----------*/
        public function agregar_usuario_controlador() {
            // Almacenamos los datos del formulario en variables despues de ser "limpiados":
            $dni = mainModel::limpiar_cadena($_POST['usuario_dni_reg']);
            $nombre = mainModel::limpiar_cadena($_POST['usuario_nombre_reg']);
            $apellido = mainModel::limpiar_cadena($_POST['usuario_apellido_reg']);
            $telefono = mainModel::limpiar_cadena($_POST['usuario_telefono_reg']);
            $direccion = mainModel::limpiar_cadena($_POST['usuario_direccion_reg']);

            $usuario = mainModel::limpiar_cadena($_POST['usuario_usuario_reg']);
            $email = mainModel::limpiar_cadena($_POST['usuario_email_reg']);
            $clave1 = mainModel::limpiar_cadena($_POST['usuario_clave_1_reg']);
            $clave2 = mainModel::limpiar_cadena($_POST['usuario_clave_2_reg']);

            $privilegio = mainModel::limpiar_cadena($_POST['usuario_privilegio_reg']);

            /*----------- Comprobar campos vacios -----------*/
            // Si alguno de estos datos se encuentra vacio entonces mandara un json con los datos de la alerta:
            if ($dni == "" || $nombre == "" || $apellido == "" || $usuario == "" || $clave1 == "" || $clave2 == "") {
                $alerta = [ // Esto lo recibira la funcion de enviar_formulario_ajax y este lo enviara a la funcion alertas_ajax en el archivo alertas.js
                    "Alerta"=> "simple",
                    "Titulo"=> "Ocurrio un error inesperado",
                    "Texto"=> "No ha llenado todos los campos obligatorios.",
                    "Tipo"=> "error"
                ];
                echo json_encode($alerta);
                // Detemos la ejecucion del codigo php:
                exit();
            }

            /*----------- Verificando integridad de los datos -----------*/
            if (mainModel::verificar_datos("[0-9-]{10,20}", $dni)) {
                $alerta = [ // Esto lo recibira la funcion de enviar_formulario_ajax y este lo enviara a la funcion alertas_ajax en el archivo alertas.js
                    "Alerta"=> "simple",
                    "Titulo"=> "Ocurrió un error inesperado",
                    "Texto"=> "El DNI no coincide con el formato solicitado.",
                    "Tipo"=> "error"
                ];
                echo json_encode($alerta);
                // Detemos la ejecucion del codigo php:
                exit();
            }

            if (mainModel::verificar_datos("[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{5,35}", $nombre)) {
                $alerta = [ // Esto lo recibira la funcion de enviar_formulario_ajax y este lo enviara a la funcion alertas_ajax en el archivo alertas.js
                    "Alerta"=> "simple",
                    "Titulo"=> "Ocurrió un error inesperado",
                    "Texto"=> "El Nombre no coincide con el formato solicitado.",
                    "Tipo"=> "error"
                ];
                echo json_encode($alerta);
                // Detemos la ejecucion del codigo php:
                exit();
            }

            if (mainModel::verificar_datos("[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{5,35}", $apellido)) {
                $alerta = [ // Esto lo recibira la funcion de enviar_formulario_ajax y este lo enviara a la funcion alertas_ajax en el archivo alertas.js
                    "Alerta"=> "simple",
                    "Titulo"=> "Ocurrió un error inesperado",
                    "Texto"=> "El Apellido no coincide con el formato solicitado.",
                    "Tipo"=> "error"
                ];
                echo json_encode($alerta);
                // Detemos la ejecucion del codigo php:
                exit();
            }

            if ($telefono != "") {
                if (mainModel::verificar_datos("[0-9()+]{8,20}", $telefono)) {
                    $alerta = [ // Esto lo recibira la funcion de enviar_formulario_ajax y este lo enviara a la funcion alertas_ajax en el archivo alertas.js
                        "Alerta"=> "simple",
                        "Titulo"=> "Ocurrió un error inesperado",
                        "Texto"=> "El Telefono no coincide con el formato solicitado.",
                        "Tipo"=> "error"
                    ];
                    echo json_encode($alerta);
                    // Detemos la ejecucion del codigo php:
                    exit();
                }
            }

            if ($direccion != "") {
                if (mainModel::verificar_datos("[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ().,#\- ]{1,190}", $direccion)) {
                    $alerta = [ // Esto lo recibira la funcion de enviar_formulario_ajax y este lo enviara a la funcion alertas_ajax en el archivo alertas.js
                        "Alerta"=> "simple",
                        "Titulo"=> "Ocurrió un error inesperado",
                        "Texto"=> "La Dirección no coincide con el formato solicitado.",
                        "Tipo"=> "error"
                    ];
                    echo json_encode($alerta);
                    // Detemos la ejecucion del codigo php:
                    exit();
                }
            }

            if (mainModel::verificar_datos("[a-zA-Z0-9]{1,35}", $usuario)) {
                $alerta = [ // Esto lo recibira la funcion de enviar_formulario_ajax y este lo enviara a la funcion alertas_ajax en el archivo alertas.js
                    "Alerta"=> "simple",
                    "Titulo"=> "Ocurrió un error inesperado",
                    "Texto"=> "El Nombre de Usuario no coincide con el formato solicitado.",
                    "Tipo"=> "error"
                ];
                echo json_encode($alerta);
                // Detemos la ejecucion del codigo php:
                exit();
            }

            if (mainModel::verificar_datos("[a-zA-Z0-9$@.-]{7,100}", $clave1) || mainModel::verificar_datos("[a-zA-Z0-9$@.-]{7,100}", $clave2)) {
                $alerta = [ // Esto lo recibira la funcion de enviar_formulario_ajax y este lo enviara a la funcion alertas_ajax en el archivo alertas.js
                    "Alerta"=> "simple",
                    "Titulo"=> "Ocurrió un error inesperado",
                    "Texto"=> "Las Claves no coinciden con el formato solicitado.",
                    "Tipo"=> "error"
                ];
                echo json_encode($alerta);
                // Detemos la ejecucion del codigo php:
                exit();
            }

            /*----------- Comprobando DNI repetido -----------*/
            $check_dni = mainModel::ejecutar_consulta_simple("SELECT usuario_dni FROM usuario WHERE usuario_dni = '".$dni."'");
            if ($check_dni->rowCount() > 0) {
                $alerta = [ // Esto lo recibira la funcion de enviar_formulario_ajax y este lo enviara a la funcion alertas_ajax en el archivo alertas.js
                    "Alerta"=> "simple",
                    "Titulo"=> "Ocurrió un error inesperado",
                    "Texto"=> "El DNI ingresado ya se encuentra registrado en el sistema.",
                    "Tipo"=> "error"
                ];
                echo json_encode($alerta);
                // Detemos la ejecucion del codigo php:
                exit();
            }

            /*----------- Comprobando nombre de usuario repetido -----------*/
            $check_usuario = mainModel::ejecutar_consulta_simple("SELECT usuario_usuario FROM usuario WHERE usuario_usuario = '".$usuario."'");
            if ($check_usuario->rowCount() > 0) {
                $alerta = [ // Esto lo recibira la funcion de enviar_formulario_ajax y este lo enviara a la funcion alertas_ajax en el archivo alertas.js
                    "Alerta"=> "simple",
                    "Titulo"=> "Ocurrió un error inesperado",
                    "Texto"=> "El Nombre de Usuario ingresado ya se encuentra registrado en el sistema.",
                    "Tipo"=> "error"
                ];
                echo json_encode($alerta);
                // Detemos la ejecucion del codigo php:
                exit();
            }

            /*----------- Comprobando email repetido -----------*/
            if ($email != "") {                
                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $check_email = mainModel::ejecutar_consulta_simple("SELECT usuario_email FROM usuario WHERE usuario_email = '".$email."'");
                    if ($check_email->rowCount() > 0) {
                        $alerta = [ // Esto lo recibira la funcion de enviar_formulario_ajax y este lo enviara a la funcion alertas_ajax en el archivo alertas.js
                            "Alerta"=> "simple",
                            "Titulo"=> "Ocurrió un error inesperado",
                            "Texto"=> "El Correo ingresado ya se encuentra registrado en el sistema.",
                            "Tipo"=> "error"
                        ];
                        echo json_encode($alerta);
                        // Detemos la ejecucion del codigo php:
                        exit();
                    }
                } else {
                    $alerta = [ // Esto lo recibira la funcion de enviar_formulario_ajax y este lo enviara a la funcion alertas_ajax en el archivo alertas.js
                        "Alerta"=> "simple",
                        "Titulo"=> "Ocurrió un error inesperado",
                        "Texto"=> "Ha ingresado un correo no válido.",
                        "Tipo"=> "error"
                    ];
                    echo json_encode($alerta);
                    // Detemos la ejecucion del codigo php:
                    exit();
                }
            }

            /*----------- Comprobando que las claves sean las mismas -----------*/
            if ($clave1 != $clave2) {
                $alerta = [ // Esto lo recibira la funcion de enviar_formulario_ajax y este lo enviara a la funcion alertas_ajax en el archivo alertas.js
                    "Alerta"=> "simple",
                    "Titulo"=> "Ocurrió un error inesperado",
                    "Texto"=> "Las claves que acaba de ingresar no coinciden.",
                    "Tipo"=> "error"
                ];
                echo json_encode($alerta);
                // Detemos la ejecucion del codigo php:
                exit();
            } else {
                $clave = mainModel::encryption($clave1);
            }

            /*----------- Comprobando el privilegio -----------*/
            if ($privilegio < 1 || $privilegio > 3) {
                $alerta = [ // Esto lo recibira la funcion de enviar_formulario_ajax y este lo enviara a la funcion alertas_ajax en el archivo alertas.js
                    "Alerta"=> "simple",
                    "Titulo"=> "Ocurrió un error inesperado",
                    "Texto"=> "El privilegio seleccionado no es válido.",
                    "Tipo"=> "error"
                ];
                echo json_encode($alerta);
                // Detemos la ejecucion del codigo php:
                exit();
            }

            /*----------- Datos que se enviaran al modelo -----------*/
            $datos_usuario_reg = [
                "dni"=> $dni,
                "nombre"=> $nombre,
                "apellido"=> $apellido,
                "telefono"=> $telefono,
                "direccion"=> $direccion,
                "usuario"=> $usuario,
                "email"=> $email,
                "clave"=> $clave,
                "estado"=> "Activa",
                "privilegio"=> $privilegio
            ];

            $agregar_usuario = usuarioModelo::agregar_usuario_modelo($datos_usuario_reg);

            if ($agregar_usuario->rowCount() == 1) {
                $alerta = [ // Esto lo recibira la funcion de enviar_formulario_ajax y este lo enviara a la funcion alertas_ajax en el archivo alertas.js
                    "Alerta"=> "limpiar",
                    "Titulo"=> "Usuario registrado",
                    "Texto"=> "Los datos del usuario han sido registrados con éxito.",
                    "Tipo"=> "success"
                ];
            } else {
                $alerta = [ // Esto lo recibira la funcion de enviar_formulario_ajax y este lo enviara a la funcion alertas_ajax en el archivo alertas.js
                    "Alerta"=> "simple",
                    "Titulo"=> "Ocurrió un error inesperado",
                    "Texto"=> "No hemos podido registrar el usuario.",
                    "Tipo"=> "error"
                ];
            }

            echo json_encode($alerta);

        } /* Fin del controlador */

        /*----------- Controlador para paginar usuarios -----------*/
        public function paginador_usuario_controlador($pagina, $registros, $privilegio, $id, $url, $busqueda) {
            // Limpiar cadenas:
            $pagina = mainModel::limpiar_cadena($pagina); // Pagina actual
            $registros = mainModel::limpiar_cadena($registros); // Numero de registros a mostrar en una sola pagina
            $privilegio = mainModel::limpiar_cadena($privilegio); // Privilegios con que cuenta el usuario
            $id = mainModel::limpiar_cadena($id); // id del usuario
            $url = mainModel::limpiar_cadena($url); // Esta url que se manda es de la vista en especifico
            $url = SERVERURL .$url. "/"; // Es por lo anterior que debemos agregarle al principio la direccion principal y concatenar lo demas
            $busqueda = mainModel::limpiar_cadena($busqueda); // cadena con la busqueda hecha por el usuario

            $tabla = ""; // Inicializamos la variable tabla

            // La variable pagina sera igual al valor entero que se mande en este parametro si se manda este parametro y si es mayor a cero,
            // Si no cumple lo anterior entonces su valor sera de 1
            $pagina = (isset($pagina) && $pagina>0) ? (int) $pagina : 1;
            // Esta variable servira para llevar el registro de donde se tiene que empezar a contar en cada pagina. Por ejemplo:
            // Si queremos que todas las paginas contengan 5 registros de un total de 20, entonces para la primera pagina $inicio sera 0, para la segunda 5,
            // Para la tercera 10 y para la ultima 15, dandonos asi cierto orden
            $inicio = ($pagina>0) ? (($pagina*$registros)-$registros) : 0;
                        
            if (isset($busqueda) && $busqueda != "") {
                // Si se manda una busqueda y esta no esta vacia:
                $consulta = "SELECT SQL_CALC_FOUND_ROWS * FROM usuario 
                    WHERE ((usuario_id!='$id' AND usuario_id!='1') AND 
                    (usuario_dni LIKE '%$busqueda%' OR usuario_nombre LIKE '%$busqueda%' 
                        OR usuario_apellido LIKE '%$busqueda%' OR usuario_telefono LIKE '%$busqueda%'
                        OR usuario_email LIKE '%$busqueda%' OR usuario_usuario LIKE '%$busqueda%'))
                    ORDER BY usuario_nombre ASC LIMIT $inicio, $registros";
            } else {
                // Si lo anterior no se cumple entonces solo muestra los datos sin filtros:
                $consulta = "SELECT SQL_CALC_FOUND_ROWS * FROM usuario WHERE usuario_id!='$id' 
                    AND usuario_id!='1' ORDER BY usuario_nombre ASC LIMIT $inicio, $registros";
            }

            $conexion = mainModel::conectar(); // Conectamos
            $datos = $conexion->query($consulta); // Hacemos la consulta
            $datos = $datos->fetchAll(); // Insertamos todos los registros de la consulta

            $total = $conexion->query("SELECT FOUND_ROWS()"); // Consultamos el numero total de registros
            $total = (int) $total->fetchColumn(); // Convertimos ese valor a entero

            $Npaginas = ceil($total / $registros); // Numero total de paginas. ceil() sirve para redondear

            $tabla .= '
            <div class="table-responsive">
                <table class="table table-dark table-sm">
                    <thead>
                        <tr class="text-center roboto-medium">
                            <th>#</th>
                            <th>DNI</th>
                            <th>NOMBRE</th>                            
                            <th>TELÉFONO</th>
                            <th>USUARIO</th>
                            <th>EMAIL</th>
                            <th>ACTUALIZAR</th>
                            <th>ELIMINAR</th>
                        </tr>
                    </thead>
                    <tbody>
            ';
            // Si el total de registros es mayor o igual a 1 (es decir que efectivamente extrajo algo) y la pagina actual es menor o igual al total de paginas:
            if ($total >= 1 && $pagina <= $Npaginas) {
                $contador = $inicio + 1; // Nos servira para mostrar en la vista el numero de item (no empezando de cero)
                $reg_inicio = $inicio + 1; // Esto nos servira para mostrarle al usuario de que numero de registro se esta empezando a mostrar en la pagina
                foreach($datos as $rows) { // Para cada registro almacenado en $datos (que se llamara $rows)
                    $tabla .= '
                    <tr class="text-center" >
                        <td>'.$contador.'</td>
                        <td>'.$rows['usuario_dni'].'</td>
                        <td>' . $rows['usuario_nombre'] . ' ' . $rows['usuario_apellido'] . '</td>
                        <td>'.$rows['usuario_telefono'].'</td>
                        <td>'.$rows['usuario_usuario'].'</td>
                        <td>'.$rows['usuario_email'].'</td>
                        <td>
                            <a href="'.SERVERURL.'user-update/'.mainModel::encryption($rows['usuario_id']).'/" class="btn btn-success">
                                <i class="fas fa-sync-alt"></i>	
                            </a>
                        </td>
                        <td>
                            <form class="FormularioAjax" action="'.SERVERURL.'ajax/usuarioAjax.php" method="POST" data-form="delete" autocomplete="off">
                                <input type="hidden" name="usuario_id_del" value="'.mainModel::encryption($rows['usuario_id']).'">
                                <button type="submit" class="btn btn-warning">
                                    <i class="far fa-trash-alt"></i>
                                </button>
                            </form>
                        </td>
                    </tr>  
                    ';
                    $contador ++;
                }
                $reg_final = $contador - 1; // Esta variable sirve para mostrarle al usuario el numero final del registro que le esta mostrando
            } else {
                // Si la anterior condicion no se cumple entonces solamente pregunta si el total de registros es mayor o igual que 1
                // Esto es por si el usuario trata de acceder a otra pagina a traves de la url. Ejemplo: localhost/prestamos/user-list/7/
                // En caso de que no hubiera una pagina 7, entonces se le mostraria un boton con un mensaje.
                if ($total >= 1) {
                    $tabla .= '<tr class="text-center" ><td colspan="8">
                                <a href="'.$url.'" class="btn btn-raised btn-primary btn-sm">Haga clic aquí para recargar el listado</a>
                                </td></tr>';
                } else {
                    // Si en definitiva no hay registros entonces se le mostrar un mensaje de que no se encontraron registros.
                    $tabla .= '<tr class="text-center" > <td colspan="8">No hay registros en el sistema</td></tr>';
                }              
            }
            $tabla .= '</tbody></table></div>'; // Concatenamos el final de la tabla

            if ($total >= 1 && $pagina <= $Npaginas) {
                // Si existen registros entonces se le mostrara al usuario desde que numero item hasta que numero de item se le esta mostrando de toda la listas de items:
                // Ejemplo: si existen 20 items y en cada pagina solo se muestran 5, entonces con esto se mostraria Mostrando usuario 1 al 5 de un total de 20
                $tabla .= '<p class="text-right">Mostrando usuario '.$reg_inicio.' al '.$reg_final.' de un total de '.$total.'</p>';

                // Si al final la primer condicion se cumple entonces mandara a llamar al metodo para paginar listas, el cual recibe como parametros la
                // pagina actual, el numero total de paginas, la url o nombre de la vista y el numero total de botones que se quiere que aparezcan
                $tabla .= mainModel::paginador_tablas($pagina, $Npaginas, $url, 7);
            }

            // Retornamos la tabla para que se muestre:
            return $tabla;
            
        } /* Fin del controlador */

        /*----------- Controlador para eliminar usuarios -----------*/
        public function eliminar_usuario_controlador() {
            /* Recibiendo id del usuario */
            $id = mainModel::decryption($_POST['usuario_id_del']);
            $id = mainModel::limpiar_cadena($id);

            /* Comprobando el usuario */
            if ($id == 1) { // Solo el usuario administrador puede eliminar otros usuarios
                $alerta = [
                    "Alerta"=> "simple",
                    "Titulo"=> "Ocurrio un error inesperado",
                    "Texto"=> "No se puede eliminar el usuario principal del sistema.",
                    "Tipo"=> "error"
                ];
                echo json_encode($alerta);                
                exit();
            }

            /* Comprobando que el usuario exista en la bd */
            $check_usuario = mainModel::ejecutar_consulta_simple("SELECT usuario_id FROM usuario WHERE usuario_id = '$id'");
            if ($check_usuario->rowCount() <= 0) {
                $alerta = [
                    "Alerta"=> "simple",
                    "Titulo"=> "Ocurrio un error inesperado",
                    "Texto"=> "El usuario que intenta eliminar no existe en el sistema.",
                    "Tipo"=> "error"
                ];
                echo json_encode($alerta);                
                exit();
            }

            /* Comprobando que el usuario no este relacionado con un prestamo */
            $check_prestamos = mainModel::ejecutar_consulta_simple("SELECT usuario_id FROM prestamo WHERE usuario_id = '$id' LIMIT 1");
            if ($check_prestamos->rowCount() > 0) {
                $alerta = [
                    "Alerta"=> "simple",
                    "Titulo"=> "Ocurrio un error inesperado",
                    "Texto"=> "No se puede eliminar este usuario debido a que está relacionado con un préstamo. Se recomienda deshabilitarlo si ya no será utilizado.",
                    "Tipo"=> "error"
                ];
                echo json_encode($alerta);                
                exit();
            }

            /* Comprobando los privilegios del usuario que esta eliminando */
            session_start(['name'=>'SPM']);
            if ($_SESSION['privilegio_spm'] != 1) { // Si no es la sesion del administrador entonces no podra eliminar
                $alerta = [
                    "Alerta"=> "simple",
                    "Titulo"=> "Ocurrio un error inesperado",
                    "Texto"=> "No tiene los permisos necesarios para realizar esta operación.",
                    "Tipo"=> "error"
                ];
                echo json_encode($alerta);                
                exit();
            }

            $eliminar_usuario = usuarioModelo::eliminar_usuario_modelo($id);

            if ($eliminar_usuario->rowCount() == 1) {
                $alerta = [
                    "Alerta"=> "recargar",
                    "Titulo"=> "Usuario eliminado",
                    "Texto"=> "El usuario ha sido eliminado del sistema correctamente.",
                    "Tipo"=> "success"
                ];
            } else {
                $alerta = [
                    "Alerta"=> "simple",
                    "Titulo"=> "Ocurrio un error inesperado",
                    "Texto"=> "No hemos podido eliminar el usuario, por favor intente nuevamente.",
                    "Tipo"=> "error"
                ];                                
            }
            echo json_encode($alerta);
        } /* Fin del controlador */

        /*----------- Controlador para datos usuarios -----------*/
        public function datos_usuario_controlador($tipo, $id) {
            $tipo = mainModel::limpiar_cadena($tipo);
            $id = mainModel::decryption($id);
            $id = mainModel::limpiar_cadena($id);

            return usuarioModelo::datos_usuario_modelo($tipo, $id);

        } /* Fin del controlador */

        /*----------- Controlador actualizar usuario -----------*/
        public function actualizar_usuario_controlador() {
            // Recibiendo el id:
            $id = mainModel::decryption($_POST['usuario_id_up']);
            $id = mainModel::limpiar_cadena($id);

            // Comprobar que el usuario existe en la base de datos:
            $check_user = mainModel::ejecutar_consulta_simple("SELECT * FROM usuario WHERE usuario_id = '$id'");
            if ($check_user->rowCount() <= 0) {
                $alerta = [
                    "Alerta"=> "simple",
                    "Titulo"=> "Ocurrio un error inesperado",
                    "Texto"=> "No hemos encontrado el usuario en el sistema.",
                    "Tipo"=> "error"
                ];
                echo json_encode($alerta);
                exit();
            } else {
                $campos = $check_user->fetch();
            }

            $dni = mainModel::limpiar_cadena($_POST["usuario_dni_up"]);
            $nombre = mainModel::limpiar_cadena($_POST["usuario_nombre_up"]);
            $apellido = mainModel::limpiar_cadena($_POST["usuario_apellido_up"]);
            $telefono = mainModel::limpiar_cadena($_POST["usuario_telefono_up"]);
            $direccion = mainModel::limpiar_cadena($_POST["usuario_direccion_up"]);
            $usuario = mainModel::limpiar_cadena($_POST["usuario_usuario_up"]);
            $email = mainModel::limpiar_cadena($_POST["usuario_email_up"]);

            if (isset($_POST["usuario_estado_up"])) {
                $estado = mainModel::limpiar_cadena($_POST["usuario_estado_up"]);
            } else {
                $estado = $campos["usuario_estado"];
            }

            if (isset($_POST["usuario_privilegio_up"])) {
                $privilegio = mainModel::limpiar_cadena($_POST["usuario_privilegio_up"]);
            } else {
                $privilegio = $campos["usuario_privilegio"];
            }
            
            $admin_usuario = mainModel::limpiar_cadena($_POST["usuario_admin"]);

            $admin_clave = mainModel::limpiar_cadena($_POST["clave_admin"]);            

            $tipo_cuenta = mainModel::limpiar_cadena($_POST["tipo_cuenta"]);

            /*----------- Comprobar campos vacios -----------*/
            // Si alguno de estos datos se encuentra vacio entonces mandara un json con los datos de la alerta:
            if ($dni == "" || $nombre == "" || $apellido == "" || $usuario == "" || $admin_usuario == "" || $admin_clave == "") {
                $alerta = [ // Esto lo recibira la funcion de enviar_formulario_ajax y este lo enviara a la funcion alertas_ajax en el archivo alertas.js
                    "Alerta"=> "simple",
                    "Titulo"=> "Ocurrio un error inesperado",
                    "Texto"=> "No ha llenado todos los campos obligatorios.",
                    "Tipo"=> "error"
                ];
                echo json_encode($alerta);
                // Detemos la ejecucion del codigo php:
                exit();
            }

            /*----------- Verificando integridad de los datos -----------*/
            if (mainModel::verificar_datos("[0-9-]{10,20}", $dni)) {
                $alerta = [ // Esto lo recibira la funcion de enviar_formulario_ajax y este lo enviara a la funcion alertas_ajax en el archivo alertas.js
                    "Alerta"=> "simple",
                    "Titulo"=> "Ocurrió un error inesperado",
                    "Texto"=> "El DNI no coincide con el formato solicitado.",
                    "Tipo"=> "error"
                ];
                echo json_encode($alerta);
                // Detemos la ejecucion del codigo php:
                exit();
            }

            if (mainModel::verificar_datos("[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{5,35}", $nombre)) {
                $alerta = [ // Esto lo recibira la funcion de enviar_formulario_ajax y este lo enviara a la funcion alertas_ajax en el archivo alertas.js
                    "Alerta"=> "simple",
                    "Titulo"=> "Ocurrió un error inesperado",
                    "Texto"=> "El Nombre no coincide con el formato solicitado.",
                    "Tipo"=> "error"
                ];
                echo json_encode($alerta);
                // Detemos la ejecucion del codigo php:
                exit();
            }

            if (mainModel::verificar_datos("[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{5,35}", $apellido)) {
                $alerta = [ // Esto lo recibira la funcion de enviar_formulario_ajax y este lo enviara a la funcion alertas_ajax en el archivo alertas.js
                    "Alerta"=> "simple",
                    "Titulo"=> "Ocurrió un error inesperado",
                    "Texto"=> "El Apellido no coincide con el formato solicitado.",
                    "Tipo"=> "error"
                ];
                echo json_encode($alerta);
                // Detemos la ejecucion del codigo php:
                exit();
            }

            if ($telefono != "") {
                if (mainModel::verificar_datos("[0-9()+]{8,20}", $telefono)) {
                    $alerta = [ // Esto lo recibira la funcion de enviar_formulario_ajax y este lo enviara a la funcion alertas_ajax en el archivo alertas.js
                        "Alerta"=> "simple",
                        "Titulo"=> "Ocurrió un error inesperado",
                        "Texto"=> "El Telefono no coincide con el formato solicitado.",
                        "Tipo"=> "error"
                    ];
                    echo json_encode($alerta);
                    // Detemos la ejecucion del codigo php:
                    exit();
                }
            }

            if ($direccion != "") {
                if (mainModel::verificar_datos("[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ().,#\- ]{1,190}", $direccion)) {
                    $alerta = [ // Esto lo recibira la funcion de enviar_formulario_ajax y este lo enviara a la funcion alertas_ajax en el archivo alertas.js
                        "Alerta"=> "simple",
                        "Titulo"=> "Ocurrió un error inesperado",
                        "Texto"=> "La Dirección no coincide con el formato solicitado.",
                        "Tipo"=> "error"
                    ];
                    echo json_encode($alerta);
                    // Detemos la ejecucion del codigo php:
                    exit();
                }
            }

            if (mainModel::verificar_datos("[a-zA-Z0-9]{1,35}", $usuario)) {
                $alerta = [ // Esto lo recibira la funcion de enviar_formulario_ajax y este lo enviara a la funcion alertas_ajax en el archivo alertas.js
                    "Alerta"=> "simple",
                    "Titulo"=> "Ocurrió un error inesperado",
                    "Texto"=> "El Nombre de Usuario no coincide con el formato solicitado.",
                    "Tipo"=> "error"
                ];
                echo json_encode($alerta);
                // Detemos la ejecucion del codigo php:
                exit();
            }

            if (mainModel::verificar_datos("[a-zA-Z0-9]{1,35}", $admin_usuario)) {
                $alerta = [ // Esto lo recibira la funcion de enviar_formulario_ajax y este lo enviara a la funcion alertas_ajax en el archivo alertas.js
                    "Alerta"=> "simple",
                    "Titulo"=> "Ocurrió un error inesperado",
                    "Texto"=> "Tu Nombre de Usuario no coincide con el formato solicitado.",
                    "Tipo"=> "error"
                ];
                echo json_encode($alerta);
                // Detemos la ejecucion del codigo php:
                exit();
            }

            if (mainModel::verificar_datos("[a-zA-Z0-9$@.-]{7,100}", $admin_clave)) {
                $alerta = [ // Esto lo recibira la funcion de enviar_formulario_ajax y este lo enviara a la funcion alertas_ajax en el archivo alertas.js
                    "Alerta"=> "simple",
                    "Titulo"=> "Ocurrió un error inesperado",
                    "Texto"=> "Tu Clave no coincide con el formato solicitado.",
                    "Tipo"=> "error"
                ];
                echo json_encode($alerta);
                // Detemos la ejecucion del codigo php:
                exit();
            }
            $admin_clave = mainModel::encryption($admin_clave);

            if ($privilegio < 1 || $privilegio > 3) {
                $alerta = [ // Esto lo recibira la funcion de enviar_formulario_ajax y este lo enviara a la funcion alertas_ajax en el archivo alertas.js
                    "Alerta"=> "simple",
                    "Titulo"=> "Ocurrió un error inesperado",
                    "Texto"=> "El Privilegio no corresponde con un valor válido.",
                    "Tipo"=> "error"
                ];
                echo json_encode($alerta);
                // Detemos la ejecucion del codigo php:
                exit();
            }

            if ($estado != "Activa" && $estado != "Deshabilitada") {
                $alerta = [ // Esto lo recibira la funcion de enviar_formulario_ajax y este lo enviara a la funcion alertas_ajax en el archivo alertas.js
                    "Alerta"=> "simple",
                    "Titulo"=> "Ocurrió un error inesperado",
                    "Texto"=> "El Estado no corresponde con un valor válido.",
                    "Tipo"=> "error"
                ];
                echo json_encode($alerta);
                // Detemos la ejecucion del codigo php:
                exit();
            }

            /*----------- Comprobando DNI repetido -----------*/
            if ($dni != $campos["usuario_dni"]) {
                $check_dni = mainModel::ejecutar_consulta_simple("SELECT usuario_dni FROM usuario WHERE usuario_dni = '".$dni."'");
                if ($check_dni->rowCount() > 0) {
                    $alerta = [ // Esto lo recibira la funcion de enviar_formulario_ajax y este lo enviara a la funcion alertas_ajax en el archivo alertas.js
                        "Alerta"=> "simple",
                        "Titulo"=> "Ocurrió un error inesperado",
                        "Texto"=> "El DNI ingresado ya se encuentra registrado en el sistema.",
                        "Tipo"=> "error"
                    ];
                    echo json_encode($alerta);
                    // Detemos la ejecucion del codigo php:
                    exit();
                }
            }
            
            /*----------- Comprobando nombre de usuario repetido -----------*/
            if ($usuario != $campos["usuario_usuario"]) {
                $check_usuario = mainModel::ejecutar_consulta_simple("SELECT usuario_usuario FROM usuario WHERE usuario_usuario = '".$usuario."'");
                if ($check_usuario->rowCount() > 0) {
                    $alerta = [ // Esto lo recibira la funcion de enviar_formulario_ajax y este lo enviara a la funcion alertas_ajax en el archivo alertas.js
                        "Alerta"=> "simple",
                        "Titulo"=> "Ocurrió un error inesperado",
                        "Texto"=> "El Nombre de Usuario ingresado ya se encuentra registrado en el sistema.",
                        "Tipo"=> "error"
                    ];
                    echo json_encode($alerta);
                    // Detemos la ejecucion del codigo php:
                    exit();
                }
            }

            /*=== Comprobar el email ===*/
            if ($email != $campos["usuario_email"] && $email != "") {
                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $check_email = mainModel::ejecutar_consulta_simple("SELECT usuario_email FROM usuario WHERE usuario_email='$email'");
                    if ($check_email->rowCount() > 0) {
                        $alerta = [ // Esto lo recibira la funcion de enviar_formulario_ajax y este lo enviara a la funcion alertas_ajax en el archivo alertas.js
                            "Alerta"=> "simple",
                            "Titulo"=> "Ocurrió un error inesperado",
                            "Texto"=> "El nuevo Email ingresado ya se encuentra registrado en el sistema.",
                            "Tipo"=> "error"
                        ];
                        echo json_encode($alerta);
                        // Detemos la ejecucion del codigo php:
                        exit();
                    }
                } else {
                    $alerta = [ // Esto lo recibira la funcion de enviar_formulario_ajax y este lo enviara a la funcion alertas_ajax en el archivo alertas.js
                        "Alerta"=> "simple",
                        "Titulo"=> "Ocurrió un error inesperado",
                        "Texto"=> "Ha ingreso un correo no válido.",
                        "Tipo"=> "error"
                    ];
                    echo json_encode($alerta);
                    // Detemos la ejecucion del codigo php:
                    exit();
                }
            }

            /*=== Comprobar claves ===*/
            if ($_POST["usuario_clave_nueva_1"] != "" || $_POST["usuario_clave_nueva_2"] != "") {
                if ($_POST["usuario_clave_nueva_1"] != $_POST["usuario_clave_nueva_2"]) {
                    $alerta = [ // Esto lo recibira la funcion de enviar_formulario_ajax y este lo enviara a la funcion alertas_ajax en el archivo alertas.js
                        "Alerta"=> "simple",
                        "Titulo"=> "Ocurrió un error inesperado",
                        "Texto"=> "Las nuevas Claves ingresadas no coinciden.",
                        "Tipo"=> "error"
                    ];
                    echo json_encode($alerta);
                    // Detemos la ejecucion del codigo php:
                    exit();
                } else {
                    if (mainModel::verificar_datos("[a-zA-Z0-9$@.-]{7,100}", $_POST["usuario_clave_nueva_1"]) || mainModel::verificar_datos("[a-zA-Z0-9$@.-]{7,100}", $_POST["usuario_clave_nueva_2"])) {
                        $alerta = [ // Esto lo recibira la funcion de enviar_formulario_ajax y este lo enviara a la funcion alertas_ajax en el archivo alertas.js
                            "Alerta"=> "simple",
                            "Titulo"=> "Ocurrió un error inesperado",
                            "Texto"=> "Las nuevas Claves ingresadas no coinciden con el formato solicitado.",
                            "Tipo"=> "error"
                        ];
                        echo json_encode($alerta);
                        // Detemos la ejecucion del codigo php:
                        exit();
                    }
                    $clave = mainModel::encryption($_POST["usuario_clave_nueva_1"]);
                }
            } else {
                $clave = $campos["usuario_clave"];
            }

            /*=== Comprobar credenciales para actualizar datos ===*/
            if ($tipo_cuenta == "Propia") {
                $check_cuenta = mainModel::ejecutar_consulta_simple("SELECT usuario_id FROM usuario WHERE usuario_usuario='$admin_usuario' AND usuario_clave='$admin_clave' AND usuario_id='$id'");
            } else {
                session_start(['name'=>'SPM']);
                if ($_SESSION['privilegio_spm'] != 1) {
                    $alerta = [ // Esto lo recibira la funcion de enviar_formulario_ajax y este lo enviara a la funcion alertas_ajax en el archivo alertas.js
                        "Alerta"=> "simple",
                        "Titulo"=> "Ocurrió un error inesperado",
                        "Texto"=> "No tienes los permisos necesarios para realizar esta acción.",
                        "Tipo"=> "error"
                    ];
                    echo json_encode($alerta);
                    // Detemos la ejecucion del codigo php:
                    exit();
                }
                $check_cuenta = mainModel::ejecutar_consulta_simple("SELECT usuario_id FROM usuario WHERE usuario_usuario='$admin_usuario' AND usuario_clave='$admin_clave'");
            }

            if ($check_cuenta->rowCount() <= 0) {
                $alerta = [ // Esto lo recibira la funcion de enviar_formulario_ajax y este lo enviara a la funcion alertas_ajax en el archivo alertas.js
                    "Alerta"=> "simple",
                    "Titulo"=> "Ocurrió un error inesperado",
                    "Texto"=> "Nombre y Clave de administrador no válidos.",
                    "Tipo"=> "error"
                ];
                echo json_encode($alerta);
                // Detemos la ejecucion del codigo php:
                exit();
            }

            /*=== Preparando los datos para enviarlos al modelo ===*/
            $datos_usuario_up = [
                "dni"=> $dni,
                "nombre"=> $nombre,
                "apellido"=> $apellido,
                "telefono"=> $telefono,
                "direccion"=> $direccion,
                "email"=> $email,
                "usuario"=> $usuario,
                "clave"=> $clave,
                "estado"=> $estado,
                "privilegio"=> $privilegio,
                "id"=> $id
            ];

            if (usuarioModelo::actualizar_usuario_modelo($datos_usuario_up)) {
                $alerta = [ // Esto lo recibira la funcion de enviar_formulario_ajax y este lo enviara a la funcion alertas_ajax en el archivo alertas.js
                    "Alerta"=> "recargar",
                    "Titulo"=> "Datos actualizados",
                    "Texto"=> "Los datos han sido actualizados con éxito.",
                    "Tipo"=> "success"
                ];
            } else {
                $alerta = [ // Esto lo recibira la funcion de enviar_formulario_ajax y este lo enviara a la funcion alertas_ajax en el archivo alertas.js
                    "Alerta"=> "simple",
                    "Titulo"=> "Ocurrió un error inesperado",
                    "Texto"=> "No hemos podido actualizar los datos, por favor intente nuevamente.",
                    "Tipo"=> "error"
                ];
            }
            echo json_encode($alerta);

        } /* Fin del controlador */
    }

?>