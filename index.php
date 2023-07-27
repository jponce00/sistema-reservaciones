<?php
    // Incluimos obligatoriamente al archivo de configuracion del sitio:
    require_once "./config/App.php";
    // Tambien incluimos al archivo controlador que nos permitira acceder a todas paginas desde este index:
    require_once "./controladores/VistasControlador.php";

    // Instanciamos una variable con la clase VistasControlador:
    $plantilla = new VistasControlador();
    // Ejecutamos la funcion para obtener la plantilla que nos provee este controlador:
    $plantilla->obtener_plantilla_controlador();

?>