<?php
include('../includes/conexion.php');

$titulo = "Lista de Pedidos";

$resultado = $conexion->query("SELECT p.id, p.codigo_pedido, e.nombre AS empleado, 
                              c.nombre AS chofer, p.fecha_pedido, p.estado, p.observaciones
                              FROM pedidos p
                              JOIN empleados e ON p.id_empleado = e.id
                              JOIN choferes c ON p.id_chofer = c.id
                              ORDER BY p.fecha_pedido DESC");

ob_start();
?>

<div class="card">
    <div class="card-header">
        <h2 class="card-title"><i class="fas fa-list"></i> Lista de Pedidos</h2>
        <div>
            <a href="agregar.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nuevo Pedido
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Empleado</th>
                        <th>Chofer</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($fila = $resultado->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($fila['codigo_pedido']) ?></td>
                        <td><?= htmlspecialchars($fila['empleado']) ?></td>
                        <td><?= htmlspecialchars($fila['chofer']) ?></td>
                        <td>
                            <?= (!empty($fila['fecha_pedido']) && $fila['fecha_pedido'] !== '0000-00-00') ? 
                                date('d/m/Y', strtotime($fila['fecha_pedido'])) : 'Sin fecha' ?>
                        </td>
                        
                        <td>
                            <div class="dropdown">
                                <button class="btn btn-sm dropdown-toggle <?php
                                    echo match(strtolower($fila['estado'])) {
                                        'pendiente' => 'btn-warning',
                                        'en proceso' => 'btn-info',
                                        'finalizado' => 'btn-success',
                                        'cancelado' => 'btn-danger',
                                        default => 'btn-secondary'
                                    };
                                ?>" 
                                type="button" id="dropdownEstado<?= $fila['id'] ?>" 
                                data-bs-toggle="dropdown" aria-expanded="false"
                                data-id="<?= $fila['id'] ?>">
                                    <?= ucfirst($fila['estado']) ?>
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="dropdownEstado<?= $fila['id'] ?>">
                                    <li><a class="dropdown-item estado-option" href="#" data-estado="pendiente">Pendiente</a></li>
                                    <li><a class="dropdown-item estado-option" href="#" data-estado="en proceso">En proceso</a></li>
                                    <li><a class="dropdown-item estado-option" href="#" data-estado="finalizado">Finalizado</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item estado-option text-danger" href="#" data-estado="cancelado">Cancelado</a></li>
                                </ul>
                            </div>
                        </td>
                        <td>
                            <a href="ver.php?id=<?= $fila['id'] ?>" class="btn btn-sm btn-secondary">
                                <i class="fas fa-eye"></i> Ver
                            </a>
                        </td>


                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>



<script>
$(document).ready(function() {
    // Manejar cambio de estado
    $(document).on('click', '.estado-option', function(e) {
        e.preventDefault();
        
        const $option = $(this);
        const $dropdown = $option.closest('.dropdown');
        const $button = $dropdown.find('button');
        const pedidoId = $button.data('id');
        const nuevoEstado = $option.data('estado');
        const nuevoEstadoTexto = $option.text().trim();
        
        // Mostrar loading
        const originalHtml = $button.html();
        $button.html('<span class="spinner-border spinner-border-sm" role="status"></span>');
        $button.prop('disabled', true);
        
        $.ajax({
            url: 'cambiar_estado.php',
            method: 'POST',
            data: {
                id_pedido: pedidoId,
                estado: nuevoEstado
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Actualizar el botón visualmente
                    $button.removeClass('btn-warning btn-info btn-success btn-danger')
                           .addClass(response.clase_boton)
                           .text(nuevoEstadoTexto);
                    
                    // Cerrar el dropdown
                    bootstrap.Dropdown.getInstance($button[0]).hide();
                    
                    // Mostrar notificación de éxito
                    Swal.fire({
                        icon: 'success',
                        title: '¡Estado actualizado!',
                        text: 'El estado ha sido cambiado a: ' + nuevoEstadoTexto,
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire('Error', response.error || 'Ocurrió un error', 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'Error de conexión', 'error');
            },
            complete: function() {
                $button.html(nuevoEstadoTexto).prop('disabled', false);
            }
        });
    });
});
</script>


<?php
$contenido = ob_get_clean();
include('../includes/layout.php');
?>