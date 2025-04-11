<?php
include('../includes/conexion.php');

$titulo = "Lista de Pedidos";

// Consulta
$resultado = $conexion->query("SELECT p.codigo_pedido, e.nombre AS empleado, c.nombre AS chofer, 
                             p.fecha_pedido, p.estado, p.observaciones, p.id AS id_pedido
FROM pedidos p
JOIN empleados e ON p.id_empleado = e.id
JOIN choferes c ON p.id_chofer = c.id");

ob_start();
?>

<h1>Lista de Pedidos</h1>
<a class="boton" href="agregar.php">+ Agregar nuevo pedido</a>

<table>
    <thead>
        <tr>
            <th>Código Pedido</th>
            <th>Empleado</th>
            <th>Chofer</th>
            <th>Fecha</th>
            <th>Estado</th>
            <th>Observaciones</th>
        </tr>
    </thead>
    <tbody>
        <?php while($fila = $resultado->fetch_assoc()): ?>
        <tr>
            <td><?= $fila['codigo_pedido'] ?></td>
            <td><?= $fila['empleado'] ?></td>
            <td><?= $fila['chofer'] ?></td>

            <!-- Formato de fecha: de Y-m-d a d/m/Y -->
            <td>
                <?php 
                    $fecha = $fila['fecha_pedido'];
                    // Verificar si la fecha es válida
                    if ($fecha && $fecha !== '0000-00-00') {
                        $fecha_formateada = date('Y-m-d', strtotime($fecha)); // Mostrar en formato yyyy-mm-dd
                    } else {
                        $fecha_formateada = 'Sin fecha';
                    }
                    echo $fecha_formateada;
                ?>
            </td>


            <!-- Estado con estilos -->
            <td>
                <?php
                    $estado = strtolower($fila['estado']);
                    $clase_estado = 'estado';
                    if ($estado === 'pendiente') $clase_estado .= ' estado-pendiente';
                    elseif ($estado === 'finalizado') $clase_estado .= ' estado-finalizado';
                    elseif ($estado === 'cancelado') $clase_estado .= ' estado-cancelado';
                ?>
                <span class="<?= $clase_estado ?>"><?= ucfirst($estado) ?></span>
            </td>

            <td><?= $fila['observaciones'] ?></td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<?php
$contenido = ob_get_clean();
include('../includes/layout.php');
?>
