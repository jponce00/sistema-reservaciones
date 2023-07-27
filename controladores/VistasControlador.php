<?php
    require_once "./modelos/vistasModelo.php";
    
    class VistasControlador extends VistasModelo {
        /*----------- Controlador para obtener las plantillas -----------*/
        // Esta funcion nos sirve para cargar la plantilla principal del sitio web:
        public function obtener_plantilla_controlador() {
            return require_once "./vistas/plantilla.php";
        }

        /*----------- Controlador para obtener las vistas -----------*/
        // Esta funcion nos sirve para extraer lo que el usuario ingreso en la barra de navegacion 
        //despues del localhost/prestamos/
        public function obtener_vistas_controlador() {
            // Preguntamos si viene el parametro views en el arreglo GET, esto es basicamente 
            //pregunta si el usuario escribio algo despues del localhost/prestamos:
            if (isset($_GET['views'])) {
                // Si si se escribio algo entonces solo almacena lo que esta inmediatamente despues 
                //del localhost/prestamos.
                // Ejemplo: si se mando localhost/prestamos/home/recargar. Con el siguiente codigo se 
                //recoge la palabra home, que es lo mas importante:
                $ruta = explode("/", $_GET["views"]);
                // Luego de obtener esa palabra la mandamos a la funcion obtener_vistas_modelos, 
                //la cual indicara que tipo de pagina mostrar al usuario:
                $respuesta = vistasModelo::obtener_vistas_modelo($ruta[0]);
            } else {
                // Si no se mandan parametros entonces va directamente al login:
                $respuesta = "login";
            }
            return $respuesta;
        }
    }

?>