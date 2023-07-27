<?php
    class VistasModelo {
        /*----------- Modelo para obtener las vistas -----------*/
        protected static function obtener_vistas_modelo($vistas) {
            // En este arreglo se guardaran los nombre de las paginas que el usuario podra visualizar
            // Ejemplo: localhost/prestamos/home, localhost/prestamos/cliente-list, etc.
            // Gracias a esta lista podremos saber si se ingreso el nombre de una pagina correcta:
            $listaBlanca = [
                "home", "client-list", "client-new", "client-search", "client-update",
                "company", "item-list", "item-new", "item-search", "item-update",
                "reservation-list", "reservation-new", "reservation-pending",
                "reservation-reservation", "reservation-search", "reservation-update",
                "user-list", "user-new", "user-search", "user-update"
            ];
            // Verificamos que el nombre de la pagina que se mando en la variable $vistas concuerde con 
            //algun elemento del arreglo:
            if (in_array($vistas, $listaBlanca)) {
                // Verificamos que en la carpeta en donde tenemos guardadas las paginas exista la que 
                //se mando en $vistas:
                if (is_file("./vistas/contenidos/".$vistas."-view.php")) {
                    // Si si existe entonces almacena esa ruta en una variable:
                    $contenido = "./vistas/contenidos/".$vistas."-view.php";
                } else {
                    // Si todavia no existe entonces almacena en la variable $contenido el valor 
                    //404 que llevara al usuario a esa pagina:
                    $contenido = "404";
                }
            } elseif ($vistas == "login" || $vistas == "index") { // Si el nombre de la vista no esta en el arreglo entonces se preguntara si el nombre es login o index
                // Si es cualquiera de los dos entonces redireccionara al login:
                $contenido = "login";
            } else {
                // Si tampoco es ninguno de los dos entonces redireccionara a 404:
                $contenido = "404";
            }
            // Devuleve el valor de la pagina a la que redirigira al usuario:
            return $contenido;
        }
    }

?>