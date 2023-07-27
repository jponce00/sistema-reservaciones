<?php
    $peticionAjax = true;
    require_once "../config/App.php";

    if (isset($_POST["empresa_nombre_reg"]) || isset($_POST["empresa_id_up"])) {
        /*=== Instancia al controlador ===*/
        require_once "../controladores/empresaControlador.php";
        $ins_empresa = new empresaControlador();

        /*=== Agregar empresa ===*/
        if (isset($_POST["empresa_nombre_reg"])) {
            $ins_empresa->agregar_empresa_controlador();
        }

        /*=== Actualizar empresa ===*/
        if (isset($_POST["empresa_id_up"])) {
            $ins_empresa->actualizar_empresa_controlador();
        }
    } else {
        session_start(['name'=> 'SPM']);
        session_unset();
        session_destroy();
        header("Location: ".SERVERURL."login/");
        exit();
    }

?>