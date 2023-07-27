<?php
    header("Content-Type: application/json");
    $peticionAjax = true;
    require_once "../config/App.php";

    // Si el areglo $_POST contiene al elemento usuario_dni_reg entonces continuara:
    if (isset($_POST['usuario_dni_reg']) || isset($_POST['usuario_id_del']) || isset($_POST['usuario_id_up'])) {
        /*----------- Instancia al controlador -----------*/
        require_once "../controladores/usuarioControlador.php";
        $ins_usuario = new usuarioControlador();

        /*----------- Agregar un usuario -----------*/
        // Si tiene ambos parametros (no necesariamente llenos) entonces contibuara:
        if (isset($_POST['usuario_dni_reg']) && isset($_POST['usuario_nombre_reg'])) {
            echo $ins_usuario->agregar_usuario_controlador();
        }

        /*----------- Eliminar un usuario -----------*/
        if (isset($_POST['usuario_id_del'])) {
            echo $ins_usuario->eliminar_usuario_controlador();
        }

        /*----------- Actualizar un usuario -----------*/
        if (isset($_POST['usuario_id_up'])) {
            echo $ins_usuario->actualizar_usuario_controlador();
        }
    } else {
        // Si no esta el elemento usuario_dni_reg entonces redirigira al login:
        session_start(['name'=> 'SPM']);
        session_unset();
        session_destroy();
        header("Location: ".SERVERURL."login/");
        exit();
    }

?>