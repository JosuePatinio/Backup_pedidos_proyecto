<?php
include('../includes/conexion.php');

// Verificamos si se envió el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_empleado = $_POST['id_empleado'];
    $id_chofer = $_POST['id_chofer'];
    $fecha = $_POST['fecha_pedido'];  // Ya viene en el formato correcto
    $observaciones = $_POST['observaciones'];
    $estado = 'pendiente';
    $codigo_pedido = 'PED' . time();

    // Preparar y ejecutar la consulta para insertar el pedido
    $stmt = $conexion->prepare("INSERT INTO pedidos (codigo_pedido, id_empleado, id_chofer, fecha_pedido, estado, observaciones) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("siisss", $codigo_pedido, $id_empleado, $id_chofer, $fecha, $estado, $observaciones);
    $stmt->execute();

    // Mostrar un mensaje de éxito
    echo "<script>
        setTimeout(function() {
            Swal.fire({
                icon: 'success',
                title: 'Pedido agregado',
                text: 'El pedido se registró con éxito.',
                confirmButtonColor: '#28a745'
            }).then(function() {
                window.location.href = 'listar.php?agregado=1';
            });
        }, 100);
    </script>";
}

// Obtener los empleados y choferes
$empleados = $conexion->query("SELECT * FROM empleados");
$choferes = $conexion->query("SELECT * FROM choferes");

$titulo = "Agregar Pedido";

$contenido = '
    <h1>Agregar nuevo pedido</h1>
    <form method="post" class="formulario">
        <div class="campo">
            <label for="id_empleado">Empleado:</label>
            <select name="id_empleado" required>
';
while ($e = $empleados->fetch_assoc()) {
    $contenido .= '<option value="' . $e['id'] . '">' . $e['nombre'] . '</option>';
}
$contenido .= '
            </select>
        </div>

        <div class="campo">
            <label for="id_chofer">Chofer:</label>
            <select name="id_chofer" required>
';
while ($c = $choferes->fetch_assoc()) {
    $contenido .= '<option value="' . $c['id'] . '">' . $c['nombre'] . '</option>';
}
$contenido .= '
            </select>
        </div>

        <div class="campo">
            <label for="fecha_pedido">Fecha del pedido:</label>
            <input type="date" name="fecha_pedido" required>
        </div>

        <div class="campo full-width">
            <label for="observaciones">Observaciones:</label>
            <textarea name="observaciones" rows="3" placeholder="Escribe las observaciones aquí..."></textarea>
        </div>

        <button type="submit">Registrar pedido</button>
    </form>
';

include('../includes/layout.php');
?>
