// Seleccionamos todos los formularios que hayan en el pagina
const formularios_ajax = document.querySelectorAll('.FormularioAjax');

function enviar_formulario_ajax(e) {
    // Con esto evitamos que se recargue la pagina:
    e.preventDefault();

    // Almacenamos en el arreglo data los datos que contiene el formulario:
    let data = new FormData(this);    
    // Obtenemos el atributo method del formulario:
    let method = this.getAttribute('method');
    // Obtener el atributo action:
    let action = this.getAttribute('action');
    // Obtenemos el atributo action:
    let tipo = this.getAttribute('data-form');

    // Obtenemos los encabezados:
    let encabezados = new Headers();

    // Preparamos la configuracion (formato json) de la peticion ajax:
    let config = {
        method: method,
        headers: encabezados,
        mode: 'cors',
        cache: 'no-cache',
        body: data
    };

    let texto_alerta;

    // Evaluamos que tipo de formulario se mando a ejecutar (submit) y en base a eso se seleccionara el tipo de alerta:
    if (tipo === 'save') {
        texto_alerta = 'Los datos quedarán guardados en el sistema';
    } else if (tipo === 'delete') {
        texto_alerta = 'Los datos serán eliminados completamente del sistema';
    } else if (tipo === 'update') {
        texto_alerta = 'Los datos del sistema serán actualizados';
    } else if (tipo === 'search') {
        texto_alerta = 'Se eliminará el término de búsqueda y tendrás que escribir uno nuevo';
    } else if (tipo === 'loans') {
        texto_alerta = 'Desea remover los datos seleccionados para préstamos o reservaciones';
    } else {
        texto_alerta = 'Quieres realizar la operación solicitada';
    }

    // Mandamos a llamar a la alerta (usando la libreria sweet alert):
    Swal.fire({
        title: '¿Estás seguro?', // Titulo de la alerta
        text: texto_alerta, // Texto descriptivo de la alerta
        type: 'question', // Tipo de alerta
        showCancelButton: true, // Para mostrar el boton de Cancelar en la alerta
        confirmButtonColor: '#3085d6', // Color del boton de confirmar o aceptar
        cancelButtonColor: '#d33', // Color del boton de cancelar
        confirmButtonText: 'Aceptar', // Texto que tendra el boton de confirmar
        cancelButtonText: 'Cancelar' // Texto que tendra el boton de cancelar
    }).then((result) => {
        if (result.value) { // Si se presiono el boton de confirmar o aceptar
            fetch(action, config) // Manda una peticion ajax a la direccion url extraida anteriormente con la configuracion hecha anteriormente tambien
            .then(res => res.json()) // Convertimos la respuesta a json
            .then(res => {                
                return alertas_ajax(res); // Mandamos esa respuesta en json a la funcion que ejecutara la alerta de conformacion
            });
        }
    });
}

// A cada uno de los formularios de la pagina les asignaremos la funcion de enviar_formulario_ajax en el evento submit
formularios_ajax.forEach(formularios => {
    formularios.addEventListener('submit', enviar_formulario_ajax);
});

// Funcion que servira para confirmar que se haya registrado una accion del usuario como guardar, elminar, actualizar, etc.
function alertas_ajax(alerta) {
    // Si el tipo de alerta es simple o que se completa una accion:
    if (alerta.Alerta === 'simple') {
        Swal.fire({
            title: alerta.Titulo,
            text: alerta.Texto,
            type: alerta.Tipo,
            confirmButtonText: 'Aceptar'
        });
    } else if (alerta.Alerta === 'recargar') { // Si se piensa recargar la pagina mientras se ingresan datos:
        Swal.fire({
            title: alerta.Titulo,
            text: alerta.Texto,
            type: alerta.Tipo,
            confirmButtonText: 'Aceptar'
        }).then((result) => {
            if (result.value) {
                // Si se presiona aceptar entonces se recarga la pagina
                location.reload();
            }
        });
    } else if (alerta.Alerta === 'limpiar') { // Si se presiona el boton de limpiar cuando todavia no se ha guardado nada
        Swal.fire({
            title: alerta.Titulo,
            text: alerta.Texto,
            type: alerta.Tipo,
            confirmButtonText: 'Aceptar'
        }).then((result) => {
            if (result.value) {
                // Si se presiona que si entonces se vacia todo el formulario:
                document.querySelector('.FormularioAjax').reset();
            }
        });
    } else if (alerta.Alerta === 'redireccionar') {
        window.location.href = alerta.URL;
    }
}