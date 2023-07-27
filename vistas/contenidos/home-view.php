<!-- Page header -->
<div class="full-box page-header">
<h3 class="text-left">
    <i class="fab fa-dashcube fa-fw"></i> &nbsp; DASHBOARD
</h3>
<p class="text-justify">
    Lorem ipsum dolor sit amet, consectetur adipisicing elit. Suscipit nostrum rerum animi natus beatae ex. Culpa blanditiis tempore amet alias placeat, obcaecati quaerat ullam, sunt est, odio aut veniam ratione.
</p>
</div>

<!-- Content -->
<div class="full-box tile-container">
<a href="<?php echo SERVERURL; ?>client-new/" class="tile">
    <div class="tile-tittle">Clientes</div>
    <div class="tile-icon">
        <i class="fas fa-users fa-fw"></i>
        <p>
            <?php 
                require_once "./controladores/clienteControlador.php";
                $ins_cliente = new clienteControlador();
                $total_clientes = $ins_cliente->datos_cliente_controlador("Conteo", 0);
                echo $total_clientes->rowCount() . " Registrados";
            ?>
        </p>
    </div>
</a>

<a href="<?php echo SERVERURL; ?>item-new/" class="tile">
    <div class="tile-tittle">Items</div>
    <div class="tile-icon">
        <i class="fas fa-pallet fa-fw"></i>
        <p>
            <?php
                require_once "./controladores/itemControlador.php";
                $ins_item = new itemControlador();
                $total_items = $ins_item->datos_item_controlador("Conteo", 0);
                echo $total_items->rowCount() . " Registrados";
            ?>
        </p>
    </div>
</a>

<?php
    require_once "./controladores/prestamoControlador.php";
    $ins_prestamo = new prestamoControlador();
    $total_prestamos = $ins_prestamo->datos_prestamo_controlador("Conteo_Prestamos", 0);
    $total_reservaciones = $ins_prestamo->datos_prestamo_controlador("Conteo_Reservacion", 0);
    $total_finalizados = $ins_prestamo->datos_prestamo_controlador("Conteo_Finalizado", 0);
?>

<a href="<?php echo SERVERURL; ?>reservation-reservation/" class="tile">
    <div class="tile-tittle">Reservaciones</div>
    <div class="tile-icon">
        <i class="far fa-calendar-alt fa-fw"></i>
        <p><?php echo $total_reservaciones->rowCount(); ?> Registradas</p>
    </div>
</a>

<a href="<?php echo SERVERURL; ?>reservation-pending/" class="tile">
    <div class="tile-tittle">Prestamos</div>
    <div class="tile-icon">
        <i class="fas fa-hand-holding-usd fa-fw"></i>
        <p><?php echo $total_prestamos->rowCount(); ?> Registrados</p>
    </div>
</a>

<a href="<?php echo SERVERURL; ?>reservation-list/" class="tile">
    <div class="tile-tittle">Finalizados</div>
    <div class="tile-icon">
        <i class="fas fa-clipboard-list fa-fw"></i>
        <p><?php echo $total_finalizados->rowCount(); ?> Registrados</p>
    </div>
</a>

<?php 
    if ($_SESSION['privilegio_spm'] == 1) { 
        require_once "./controladores/usuarioControlador.php";
        $ins_usuario = new usuarioControlador();
        $total_usuarios = $ins_usuario->datos_usuario_controlador("Conteo", 0);
?>
<a href="<?php echo SERVERURL; ?>user-list/" class="tile">
    <div class="tile-tittle">Usuarios</div>
    <div class="tile-icon">
        <i class="fas fa-user-secret fa-fw"></i>
        <p><?php echo $total_usuarios->rowCount(); ?> Registrados</p>
    </div>
</a>
<?php } ?>

<a href="<?php echo SERVERURL; ?>company/" class="tile">
    <div class="tile-tittle">Empresa</div>
    <div class="tile-icon">
        <i class="fas fa-store-alt fa-fw"></i>
        <p>1 Registrada</p>
    </div>
</a>
</div>