<script>
    // Seleccionamos el boton de cerra sesion que contiene la clase btn-exit-system:
    let btn_salir = document.querySelector('.btn-exit-system');
    // Anadimos el evento click a ese boton:
    btn_salir.addEventListener('click', (evento)=>{
        evento.preventDefault();
        // Le aparecera un mensaje sobre si el usuario realmente desea salir del sistema:
        Swal.fire({
			title: '¿Quires salir del sistema?',
			text: "La sesión actual se cerrará y saldrás del sistema",
			type: 'question',
			showCancelButton: true,
			confirmButtonColor: '#3085d6',
			cancelButtonColor: '#d33',
			confirmButtonText: 'Sí, salir',
			cancelButtonText: 'No, cancelar'
		}).then((result) => {
			// Si presiona que si entonces hara lo siguiente:
            if (result.value) {
                // Esta variable almacenara la url que ejecutara la peticion ajax:
				let url = '<?php echo SERVERURL; ?>ajax/loginAjax.php';
                // Esta variable almacena el valor de la variable de sesion token_spm del usuario:
                let token = '<?php echo $lc->encryption($_SESSION['token_spm']); ?>';
                // Esta variable almacena el valor de la variable de sesion usuario_spm del usuario:
                let usuario = '<?php echo $lc->encryption($_SESSION['usuario_spm']); ?>';

                // Almacenamos los datos del token y del usuario en una variable de tipo FormData:
                let datos = new FormData();
                datos.append('token', token);
                datos.append('usuario', usuario);

                // Hacemos la peticion ajax haciendo uso de la funcion nativa de javascript, fetch:
                fetch(url, {
                    method: 'POST',
                    body: datos,
                })
                .then(res => res.json())
                .then(res => {                
                    return alertas_ajax(res);
                });
			}
		});
    });
</script>