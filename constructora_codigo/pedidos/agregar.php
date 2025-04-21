<?php
include('../includes/conexion.php');

$mensaje_exito = '';
$mensaje_error = '';

// Función para generar código de pedido profesional
function generarCodigoPedido($conexion, $id_empleado, $fecha) {
    // Obtener nombre del empleado
    $stmt = $conexion->prepare("SELECT nombre FROM empleados WHERE id = ?");
    $stmt->bind_param("i", $id_empleado);
    $stmt->execute();
    $stmt->bind_result($nombre_empleado);
    $stmt->fetch();
    $stmt->close();
    
    // Extraer primeras 2 consonantes del nombre (o primeras letras si no hay suficientes consonantes)
    $consonantes = preg_replace('/[^BCDFGHJKLMNPQRSTVWXYZ]/i', '', strtoupper($nombre_empleado));
    $iniciales = substr($consonantes, 0, 2);
    if (strlen($iniciales) < 2) {
        $iniciales = substr(strtoupper($nombre_empleado), 0, 2);
    }
    
    // Formatear fecha (YYMMDD)
    $fecha_formato = date('ymd', strtotime($fecha));
    
    // Obtener número consecutivo para el día
    $stmt = $conexion->prepare("SELECT COUNT(*) FROM pedidos WHERE DATE(fecha_pedido) = DATE(?)");
    $stmt->bind_param("s", $fecha);
    $stmt->execute();
    $stmt->bind_result($consecutivo);
    $stmt->fetch();
    $stmt->close();
    
    $consecutivo++; // Incrementamos para el nuevo pedido
    $consecutivo_formato = str_pad($consecutivo, 3, '0', STR_PAD_LEFT);
    
    return "{$iniciales}-{$fecha_formato}-{$consecutivo_formato}";
}



if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Iniciar transacción para integridad de datos
    $conexion->begin_transaction();
    
    try {
        // Validación centralizada de campos obligatorios
        $campos_requeridos = [
            'id_empleado' => 'Empleado',
            'id_chofer' => 'Chofer',
            'fecha_pedido' => 'Fecha del pedido'
        ];
        
        foreach ($campos_requeridos as $campo => $nombre) {
            if (empty($_POST[$campo])) {
                throw new Exception("El campo {$nombre} es obligatorio");
            }
        }

        // Generar código de pedido profesional
        $codigo_pedido = generarCodigoPedido($conexion, $_POST['id_empleado'], $_POST['fecha_pedido']);

        
        // Insertar pedido principal
        $stmt = $conexion->prepare("INSERT INTO pedidos (codigo_pedido, id_empleado, id_chofer, fecha_pedido, observaciones) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("siiss", $codigo_pedido, $_POST['id_empleado'], $_POST['id_chofer'], $_POST['fecha_pedido'], $_POST['observaciones']);
        
        if (!$stmt->execute()) {
            throw new Exception("Error al guardar el pedido: " . $conexion->error);
        }
        
        $pedido_id = $conexion->insert_id;
        $productos_procesados = 0;
        
        // Procesar productos si existen
        if (!empty($_POST['productos'])) {
            $stmt_detalle = $conexion->prepare("INSERT INTO pedido_productos (id_pedido, id_producto, cantidad) VALUES (?, ?, ?)");
            
            foreach ($_POST['productos'] as $index => $id_producto) {
                if (!empty($id_producto)) {
                    $cantidad = $_POST['cantidades'][$index] ?? 1;
                    
                    // Validación de cantidad
                    if (!is_numeric($cantidad) || $cantidad <= 0) {
                        throw new Exception("La cantidad debe ser un número positivo para todos los productos");
                    }
                    
                    $stmt_detalle->bind_param("iii", $pedido_id, $id_producto, $cantidad);
                    if (!$stmt_detalle->execute()) {
                        throw new Exception("Error al guardar los productos: " . $conexion->error);
                    }
                    $productos_procesados++;
                }
            }
            $stmt_detalle->close();
            
            if ($productos_procesados === 0) {
                throw new Exception("Debe agregar al menos un producto válido");
            }
        }
        
        // Confirmar transacción si todo fue bien
        $conexion->commit();
        $mensaje_exito = 'El pedido se ha registrado correctamente con el folio: ' . $codigo_pedido;
        
    } catch (Exception $e) {
        $conexion->rollback();
        $mensaje_error = $e->getMessage();
    }
}

// Obtener datos para los selects de forma separada pero eficiente
$empleados = $conexion->query("SELECT id, nombre FROM empleados")->fetch_all(MYSQLI_ASSOC);
$choferes = $conexion->query("SELECT id, nombre FROM choferes")->fetch_all(MYSQLI_ASSOC);
$productos = $conexion->query("SELECT id, nombre_producto as nombre, precio FROM productos")->fetch_all(MYSQLI_ASSOC);

$titulo = "Nuevo Pedido";
ob_start();
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h2 class="card-title mb-0"><i class="fas fa-plus-circle mr-2"></i>Nuevo Pedido</h2>
        <a href="listar.php" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Volver
        </a>
    </div>
    <div class="card-body">
        <form method="post" id="form-pedido" class="needs-validation" novalidate>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="id_empleado" class="form-label">Empleado *</label>
                        <select name="id_empleado" class="form-control select2" required>
                            <option value="">Seleccionar empleado</option>
                            <?php foreach ($empleados as $e): ?>
                                <option value="<?= $e['id'] ?>" <?= ($_POST['id_empleado'] ?? '') == $e['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($e['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">Seleccione un empleado</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="id_chofer" class="form-label">Chofer *</label>
                        <select name="id_chofer" class="form-control select2" required>
                            <option value="">Seleccionar chofer</option>
                            <?php foreach ($choferes as $c): ?>
                                <option value="<?= $c['id'] ?>" <?= ($_POST['id_chofer'] ?? '') == $c['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($c['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">Seleccione un chofer</div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="fecha_pedido" class="form-label">Fecha *</label>
                        <input type="date" name="fecha_pedido" class="form-control" 
                               value="<?= htmlspecialchars($_POST['fecha_pedido'] ?? '') ?>" required>
                        <div class="invalid-feedback">Ingrese una fecha válida</div>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Productos *</label>
                <div id="productos-container">
                    <?php 
                    $productos_post = $_POST['productos'] ?? [''];
                    $cantidades_post = $_POST['cantidades'] ?? [1];
                    foreach ($productos_post as $index => $producto_id): 
                    ?>
                    <div class="producto-item mb-3">
                        <div class="row g-2">
                            <div class="col-md-8">
                                <select name="productos[]" class="form-control select2-producto" required>
                                    <option value="">Seleccionar producto</option>
                                    <?php foreach ($productos as $p): ?>
                                        <option value="<?= $p['id'] ?>" 
                                            <?= $producto_id == $p['id'] ? 'selected' : '' ?>
                                            data-precio="<?= $p['precio'] ?>">
                                            <?= htmlspecialchars($p['nombre']) ?> ($<?= number_format($p['precio'], 2) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <input type="number" name="cantidades[]" class="form-control" 
                                       min="1" step="1" value="<?= $cantidades_post[$index] ?? 1 ?>" required>
                            </div>
                            <div class="col-md-1">
                                <?php if ($index === 0): ?>
                                    <button type="button" class="btn btn-success btn-add-producto">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                <?php else: ?>
                                    <button type="button" class="btn btn-danger btn-remove-producto">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="text-end mt-2">
                    <small class="text-muted">Mínimo 1 producto requerido</small>
                </div>
            </div>

            <div class="form-group">
                <label for="observaciones" class="form-label">Observaciones</label>
                <textarea name="observaciones" class="form-control" rows="3"><?= 
                    htmlspecialchars($_POST['observaciones'] ?? '') ?></textarea>
            </div>

            <div class="form-actions mt-4">
                <button type="reset" class="btn btn-secondary">
                    <i class="fas fa-undo mr-1"></i> Limpiar
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save mr-1"></i> Guardar Pedido
                </button>
            </div>
        </form>
    </div>
</div>

<script>
$(document).ready(function() {
    // Inicializar Select2 con búsqueda
    $('.select2').select2({
        width: '100%',
        placeholder: 'Seleccione una opción',
        allowClear: true
    });
    
    // Select2 para productos con funcionalidad adicional
    $(document).on('select2:open', '.select2-producto', function() {
        document.querySelector('.select2-search__field').focus();
    });

    // Añadir nuevo producto
    $(document).on('click', '.btn-add-producto', function() {
        const newItem = $('.producto-item:first').clone();
        newItem.find('select').val('').trigger('change');
        newItem.find('input').val('1');
        newItem.find('.btn-add-producto')
            .removeClass('btn-success')
            .addClass('btn-danger')
            .html('<i class="fas fa-minus"></i>')
            .removeClass('btn-add-producto')
            .addClass('btn-remove-producto');
        $('#productos-container').append(newItem);
    });

    // Eliminar producto
    $(document).on('click', '.btn-remove-producto', function() {
        if ($('.producto-item').length > 1) {
            $(this).closest('.producto-item').remove();
        } else {
            Swal.fire('Advertencia', 'Debe haber al menos un producto', 'warning');
        }
    });

    // Validación del formulario
    $('#form-pedido').on('submit', function(e) {
        e.preventDefault();
        
        // Validar campos requeridos
        let isValid = true;
        let hasProducts = false;
        
        $(this).find('[required]').each(function() {
            if (!$(this).val()) {
                $(this).addClass('is-invalid');
                isValid = false;
            } else {
                $(this).removeClass('is-invalid');
                if ($(this).is('select[name="productos[]"]') && $(this).val()) {
                    hasProducts = true;
                }
            }
        });

        if (!hasProducts) {
            Swal.fire('Error', 'Debe agregar al menos un producto', 'error');
            return;
        }

        if (!isValid) {
            Swal.fire('Error', 'Por favor complete todos los campos obligatorios', 'error');
            return;
        }

        // Confirmación antes de enviar
        Swal.fire({
            title: '¿Guardar pedido?',
            text: '¿Está seguro que desea registrar este pedido?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#234076',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, guardar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                this.submit();
            }
        });
    });

    // Mostrar mensajes de PHP
    <?php if (!empty($mensaje_exito)): ?>
        Swal.fire({
            title: '¡Éxito!',
            text: '<?= addslashes($mensaje_exito) ?>',
            icon: 'success',
            confirmButtonText: 'Aceptar'
        }).then(() => {
            window.location.href = 'listar.php';
        });
    <?php elseif (!empty($mensaje_error)): ?>
        Swal.fire({
            title: 'Error',
            text: '<?= addslashes($mensaje_error) ?>',
            icon: 'error',
            confirmButtonText: 'Aceptar'
        });
    <?php endif; ?>
});
</script>

<?php
$contenido = ob_get_clean();
include('../includes/layout.php');
?>