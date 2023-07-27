<?php
    if ($peticionAjax) {
        require_once "../modelos/loginModelo.php";
    } else {
        require_once "./modelos/loginModelo.php";
    }

    class loginControlador extends loginModelo {
        /*----------- Controlador para iniciar sesion -----------*/
        public function iniciar_sesion_controlador() {
            // Limpiar cadenas de posible inyeccion sql:
            $usuario = mainModel::limpiar_cadena($_POST["usuario_log"]);
            $clave = mainModel::limpiar_cadena($_POST["clave_log"]);

            /*=== Comprobar campos vacios ===*/
            if ($usuario == "" || $clave == "") { // Si el usuario y contrasena no se mandan vacios
                // La alerta se imprime asi porque no hacemos uso de peticiones ajax
                echo "
                <script>
                    Swal.fire({
                        title: 'Ocurrio un error inesperado',
                        text: 'No ha llenado todos los campos requeridos.',
                        type: 'error',
                        confirmButtonText: 'Aceptar'
                    });
                </script>
                ";
                exit();
            }

            /*=== Verificar la integridad de los datos ===*/
            if (mainModel::verificar_datos("[a-zA-Z0-9]{1,35}", $usuario)) { // Verificamos que lo que se manda coincide con el formato adecuado
                echo "
                <script>
                    Swal.fire({
                        title: 'Ocurrio un error inesperado',
                        text: 'El Nombre de Usuario no coincide con el formato solicitado..',
                        type: 'error',
                        confirmButtonText: 'Aceptar'
                    });
                </script>
                ";
                exit();
            }

            if (mainModel::verificar_datos("[a-zA-Z0-9$@.-]{7,100}", $clave)) {
                echo "
                <script>
                    Swal.fire({
                        title: 'Ocurrio un error inesperado',
                        text: 'La Clave no coincide con el formato solicitado.',
                        type: 'error',
                        confirmButtonText: 'Aceptar'
                    });
                </script>
                ";
                exit();
            }

            // Mandamos a encriptar la clave porque en la base de datos esta encriptada:
            $clave = mainModel::encryption($clave);

            // Almacenamos los datos en un arreglo para enviarlo al modelo:
            $datos_login = [
                "usuario"=> $usuario,
                "clave"=> $clave
            ];

            // Ejecutamos la consulta a la base de datos haciendo el uso del modelo:
            $datos_cuenta = loginModelo::iniciar_sesion_modelo($datos_login);

            // Si lo anterior devuelve una fila de datos entonces creara las variables de sesion:
            if ($datos_cuenta->rowCount() == 1) {
                // Almacenamos los datos que arroja la consulta en una variable:
                $row = $datos_cuenta->fetch();

                // Iniciamos un nuevo proceso de sesion llamado SPM:
                session_start(['name'=>'SPM']);

                $_SESSION['id_spm'] = $row["usuario_id"]; // Almacenamos el id del usuario
                $_SESSION['nombre_spm'] = $row["usuario_nombre"]; // Almacenamos el nombre del usuario
                $_SESSION['apellido_spm'] = $row["usuario_apellido"]; // Almacenamos el apellido
                $_SESSION['usuario_spm'] = $row["usuario_usuario"]; // Almacenamos el nombre de usuario
                $_SESSION['privilegio_spm'] = $row["usuario_privilegio"]; // Almacenamos los privilegios que posee el usuario
                $_SESSION['token_spm'] = md5(uniqid(mt_rand(), true)); // Almacenamos un token que nos permitira verificar que las sesiones coincidan

                return header("Location: ".SERVERURL."home/"); // Retornamos una redireccion a la vista home
            } else {
                // Si no se devuelve nada entonces mostrar un error de que los datos no coinciden:
                echo "
                <script>
                    Swal.fire({
                        title: 'Ocurrio un error inesperado',
                        text: 'El Usuario o la Clave son incorrectos.',
                        type: 'error',
                        confirmButtonText: 'Aceptar'
                    });
                </script>
                ";
            }
        } /* Fin del controlador */

        /*----------- Controlador para forzar el cierre de sesion -----------*/
        public function forzar_cierre_sesion_controlador() {            
            // Vaciamos los datos de sesion:
            session_unset();
            // Borramos todos los datos de la sesion:
            session_destroy();
            // Esta condicion nos sirve para redireccionar a traves de un script o desde php:
            if (headers_sent()) {
                return "
                <script>
                    window.location.href = '".SERVERURL."login/';
                </script>";
            } else {
                return header("Location: ".SERVERURL."login/");
            }
        } /* Fin controlador */

        /*----------- Controlador para cerrar la sesion -----------*/
        public function cerrar_sesion_controlador() {
            // Inicia el proceso para poder tratar con la variable de sesion SPM:
            session_start(['name'=> 'SPM']);
            // Desencripta lo que se mande a atrves del ajax para ser comparado con los valores almacenados en la variable de sesion:
            $token = mainModel::decryption($_POST["token"]);
            $usuario = mainModel::decryption($_POST["usuario"]);

            // Si las cadenas son respectivamente iguales:
            if ($token == $_SESSION['token_spm'] && $usuario == $_SESSION['usuario_spm']) {
                // Cierra la sesion:
                session_unset();
                session_destroy();
                // Envia un json como respuesta que contendra estos parametros que serviran para redirigir al login:
                $alerta = [
                    "Alerta"=> "redireccionar",
                    "URL"=> SERVERURL."login/"
                ];
            } else {
                // Si no son iguales entonces solamente enviara un json con un mensaje simple de error:
                $alerta = [
                    "Alerta"=> "simple",
                    "Titulo"=> "Ocurrio un error inesperado",
                    "Texto"=> "No se pudo cerrar la sesión en el sistema.",
                    "Tipo"=> "error"
                ];
            }
            echo json_encode($alerta);

        } /* Fin controlador */
    }

?>