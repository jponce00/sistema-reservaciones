<?php
    if ($peticionAjax) {
        require_once "../modelos/empresaModelo.php";
    } else {
        require_once "./modelos/empresaModelo.php";
    }

    class empresaControlador extends empresaModelo {
        /*----------- Enviar alertas de error -----------*/
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
        
        /*----------- Controlador para datos de empresa -----------*/
        public function datos_empresa_controlador() {
            return empresaModelo::datos_empresa_modelo();
        } /* Fin de controlador */

        /*----------- Controlador para crear empresa -----------*/
        public function agregar_empresa_controlador() {
            /*=== Almacenar en variables lo que se manda desde el formulario ===*/
            $nombre = MainModel::limpiar_cadena($_POST["empresa_nombre_reg"]);
            $email = MainModel::limpiar_cadena($_POST["empresa_email_reg"]);
            $telefono = MainModel::limpiar_cadena($_POST["empresa_telefono_reg"]);
            $direccion = MainModel::limpiar_cadena($_POST["empresa_direccion_reg"]);

            /*=== Comprobar que los campos no vengan vacios ===*/
            if ($nombre == "" || $email == "" || $telefono == "" || $direccion == "") {
                $this->enviar_alerta_error("No ha llenado todos los campos obligatorios");
            }

            /*=== Verificar intregridad de los datos ===*/
            if (MainModel::verificar_datos("[a-zA-z0-9áéíóúÁÉÍÓÚñÑ. ]{1,70}", $nombre)) {
                $this->enviar_alerta_error("El Nombre no coincide con el formato solicitado.");
            }            

            if (MainModel::verificar_datos("[0-9()+]{8,20}", $telefono)) {
                $this->enviar_alerta_error("El Teléfono no coincide con el formato solicitado.");
            }

            if (MainModel::verificar_datos("[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ().,#\- ]{1,190}", $direccion)) {
                $this->enviar_alerta_error("La Dirección no coincide con el formato solicitado.");
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->enviar_alerta_error("El Correo no coincide con el formato solicitado.");
            }

            /*=== Comprobar empresas registradas ===*/
            $check_empresas = MainModel::ejecutar_consulta_simple("SELECT empresa_id FROM empresa");
            if ($check_empresas->rowCount() >= 1) {
                $this->enviar_alerta_error("Ya existe una empresa registrada, ya no puedes registrar más.");
            }

            $datos_empresa_reg = [
                "nombre"=> $nombre,
                "email"=> $email,
                "telefono"=> $telefono,
                "direccion"=> $direccion
            ];

            $agregar_empresa = empresaControlador::agregar_empresa_modelo($datos_empresa_reg);

            if ($agregar_empresa->rowCount() == 1) {
                $alerta = [
                    "Alerta"=> "recargar",
                    "Titulo"=> "Empresa registrada",
                    "Texto"=> "Los datos de la empresa han sido guardados correctamente.",
                    "Tipo"=> 'success'
                ];
                echo json_encode($alerta);
            } else {
                $this->enviar_alerta_error("No hemos podido registrar la empresa, por favor intente nuevamente.");
            }

        } /* Fin de controlador */

        /*----------- Controlador para actualizar empresa -----------*/
        public function actualizar_empresa_controlador() {
            $nombre = MainModel::limpiar_cadena($_POST["empresa_nombre_up"]);
            $email = MainModel::limpiar_cadena($_POST["empresa_email_up"]);
            $telefono = MainModel::limpiar_cadena($_POST["empresa_telefono_up"]);
            $direccion = MainModel::limpiar_cadena($_POST["empresa_direccion_up"]);
            $id = MainModel::limpiar_cadena($_POST["empresa_id_up"]);
            $id = MainModel::decryption($id);

            /*=== Comprobar que los campos no vengan vacios ===*/
            if ($nombre == "" || $email == "" || $telefono == "" || $direccion == "") {
                $this->enviar_alerta_error("No ha llenado todos los campos obligatorios");
            }

            /*=== Verificar intregridad de los datos ===*/
            if (MainModel::verificar_datos("[a-zA-z0-9áéíóúÁÉÍÓÚñÑ. ]{1,70}", $nombre)) {
                $this->enviar_alerta_error("El Nombre no coincide con el formato solicitado.");
            }            

            if (MainModel::verificar_datos("[0-9()+]{8,20}", $telefono)) {
                $this->enviar_alerta_error("El Teléfono no coincide con el formato solicitado.");
            }

            if (MainModel::verificar_datos("[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ().,#\- ]{1,190}", $direccion)) {
                $this->enviar_alerta_error("La Dirección no coincide con el formato solicitado.");
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->enviar_alerta_error("El Correo no coincide con el formato solicitado.");
            }

            /*=== Comprobar que haya una empresa registrada ===*/
            $check_empresa = MainModel::ejecutar_consulta_simple("SELECT empresa_id FROM empresa");
            if ($check_empresa->rowCount() == 0) {
                $this->enviar_alerta_error("Todavía no existe una empresa registrada en el sistema.");
            }

            /*=== Comprobando privilegios ===*/
            session_start(['name'=> 'SPM']);
            if ($_SESSION["privilegio_spm"] != 1 && $_SESSION["privilegio_spm"] != 2) {
                $this->enviar_alerta_error("No tiene los permisos necesarios para ejecutar esta operación.");
            }


            $datos_empresa_up = [
                "nombre"=> $nombre,
                "email"=> $email,
                "telefono"=> $telefono,
                "direccion"=> $direccion,
                "id"=> $id
            ];

            if (empresaModelo::actualizar_empresa_modelo($datos_empresa_up)) {
                $alerta = [
                    "Alerta"=> "recargar",
                    "Titulo"=> "Empresa actualizada",
                    "Texto"=> "Los datos han sido actualizados con éxito.",
                    "Tipo"=> "success"
                ];
                echo json_encode($alerta);
            } else {
                $this->enviar_alerta_error("No se han podido actualizar los datos, por favor intente nuevamente.");
            }
        } /* Fin de controlador */
    }

?>