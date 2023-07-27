<?php
    // Iniciamos la varriable de sesion porque ahi guardaremos la busqueda que haga el usuario para que en caso de recargar la pagina la busqueda permanezca ahi:
    session_start(['name'=> 'SPM']);
    // Incluimos esta configuracion para poder acceder a la variable SERVERURL:
    require_once "../config/App.php";

    // Si en el metodo post se envio datos en algunos de estos inputs de busqueda...
    // busqueda_inicial es para la busqueda, eliminar_busqueda para cuando se elimine la busqueda y lo de las fechas es para la vista de prestamos:
    if (isset($_POST["busqueda_inicial"]) || isset($_POST["eliminar_busqueda"]) || isset($_POST["fecha_inicio"]) || isset($_POST["fecha_final"])) {
        // Guardamos en un array el nombre de las vistas desde donde se pueden realizar busquedas:
        // El indice es basicamente el name que tendra un input type hidden que se mandara desde la vista en que se este:
        $data_url = [
            "usuario"=> "user-search",
            "cliente"=> "client-search",
            "item"=> "item-search",
            "prestamo"=> "reservation-search"
        ];

        // Si efectivamente se manda un valor en el input de name modulo desde la vista de busqueda...
        if (isset($_POST["modulo"])) {
            // Entonces guardara ese valor en una variable modulo
            $modulo = $_POST["modulo"];
            // Si lo que se mando no esta dentro de los valores permitidos por el array data_url...
            if (!isset($data_url[$modulo])) {
                // Error, no se mando el valor correcto:
                $alerta = [
                    "Alerta"=> "simple",
                    "Titulo"=> "Ocurrió un error inesperado",
                    "Texto"=> "No podemos continuar con la búsqueda debido a un error.",
                    "Tipo"=> "error"
                ];
                echo json_encode($alerta);
                exit();
            }
        } else { // Si no se mando el valor del input entonces arrojara error:
            $alerta = [
                "Alerta"=> "simple",
                "Titulo"=> "Ocurrió un error inesperado",
                "Texto"=> "No podemos continuar con la búsqueda debido a un error de configuración.",
                "Tipo"=> "error"
            ];
            echo json_encode($alerta);            
            exit();
        }

        // Si lo que se mando a traves del input es "prestamo" entonces quiere decir que fue desde la vista de reservaciones:
        if ($modulo == "prestamo") {
            // La vista de busqueda de reservaciones tiene como parametros dos fechas:
            $fecha_inicio = "fecha_inicio_".$modulo; // Asi se nombraria a la variable de sesion de la fecha inicial para guardar la busqueda
            $fecha_final = "fecha_final_".$modulo; // Y asi a la del final

            // Iniciar la busqueda:
            if (isset($_POST["fecha_inicio"]) || isset($_POST["fecha_final"])) { // Si se mandan ambas fechas:
                if ($_POST["fecha_inicio"] == "" || $_POST["fecha_final"] == "") { // Si ambas fechas enviadas no estan vacias
                    $alerta = [
                        "Alerta"=> "simple",
                        "Titulo"=> "Ocurrió un error inesperado",
                        "Texto"=> "Por favor introduce una fecha de inicio y una fecha final.",
                        "Tipo"=> "error"
                    ];
                    echo json_encode($alerta);
                    exit();
                }
                // Si si mandaron y no estan vacias entonces almacenara sus valores en las variables de sesion:
                $_SESSION[$fecha_inicio] = $_POST["fecha_inicio"];
                $_SESSION[$fecha_final] = $_POST["fecha_final"];
            }

            // Eliminar busqueda:
            if (isset($_POST["eliminar_busqueda"])) { // Si se manda lo de eliminar busqueda entonces limpiara las variables de sesion:
                unset($_SESSION[$fecha_inicio]);
                unset($_SESSION[$fecha_final]);
            }
        } else {
            // Si lo que se envio desde la vista no fue desde la vista de prestamos (reservaciones)...
            // Crear variable para nombre de variable de sesion la cual se asignara segun desde donde se envie el valor de la variable $modulo:
            $name_var = "busqueda_".$modulo;

            // Iniciar la busqueda:
            if (isset($_POST["busqueda_inicial"])) { // Si si se mando el valor del input busqueda_inicial...
                if ($_POST["busqueda_inicial"] == "") { // Si lo que se mando es una cadena vacia entonces mostrara error
                    $alerta = [
                        "Alerta"=> "simple",
                        "Titulo"=> "Ocurrió un error inesperado",
                        "Texto"=> "Por favor introduce un término de búsqueda para empezar.",
                        "Tipo"=> "error"
                    ];
                    echo json_encode($alerta);
                    exit();
                }
                // Si lo que se mando no es una cadena vacia entonces guardara en la variable de sesion con el nombre asignado el valor que se mando
                // en el input busqueda_inicial
                $_SESSION[$name_var] = $_POST["busqueda_inicial"];
            }

            // Eliminar busqueda:
            if (isset($_POST["eliminar_busqueda"])) { // Si se mando el input eliminar_busqueda
                unset($_SESSION[$name_var]); // Entonces elimina la variable de sesion
            }
        }

        // Redireccionar:
        // Si pasa todos estos filtros entonces al final redireccionara a la misma pagina de busqueda para recergarla y mostrar lo que se busco:
        $url = $data_url[$modulo]; // Recordando que en el modulo se encuentra el nombre de la vista
        $alerta = [
            "Alerta"=> "redireccionar",
            "URL"=> SERVERURL . $url . "/"
        ];
        echo json_encode($alerta);

    } else {
        session_unset();
        session_destroy();
        header("Location: ".SERVERURL."login/");
        exit();
    }

?>