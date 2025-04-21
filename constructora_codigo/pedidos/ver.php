<?php
include('../includes/conexion.php');

if (!isset($_GET['id'])) {
    header('Location: listar.php');
    exit;
}

$id_pedido = (int)$_GET['id'];
$pedido = $conexion->query("SELECT p.*, e.nombre AS empleado, c.nombre AS chofer 
                           FROM pedidos p
                           JOIN empleados e ON p.id_empleado = e.id
                           JOIN choferes c ON p.id_chofer = c.id
                           WHERE p.id = $id_pedido")->fetch_assoc();

$productos = $conexion->query("SELECT pp.*, pr.nombre_producto, pr.precio 
                              FROM pedido_productos pp
                              JOIN productos pr ON pp.id_producto = pr.id
                              WHERE pp.id_pedido = $id_pedido");

$titulo = "Pedido #" . $pedido['codigo_pedido'];
ob_start();
?>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">📝 Detalle del Pedido</h2>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <p><strong>Código:</strong> <?= $pedido['codigo_pedido'] ?></p>
                <p><strong>Fecha:</strong> <?= date('d/m/Y', strtotime($pedido['fecha_pedido'])) ?></p>
                </div>
                <div class="col-md-6">
                <p><strong>Estado:</strong> 
                    <span class="badge <?php
                        echo match(strtolower($pedido['estado'])) {
                            'pendiente' => 'bg-warning',
                            'en proceso' => 'bg-info',
                            'finalizado' => 'bg-success',
                            'cancelado' => 'bg-danger',
                            default => 'bg-secondary'
                        };
                    ?>">
                        <?= ucfirst($pedido['estado']) ?>
                    </span>
                </p>
                <p><strong>Empleado:</strong> <?= $pedido['empleado'] ?></p>
                <p><strong>Chofer:</strong> <?= $pedido['chofer'] ?></p>
                <p><strong>Observaciones:</strong> <?= $pedido['observaciones'] ?></p>
            </div>
        </div>
        
        <h4 class="mt-4">Productos</h4>
        <table class="table">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Precio Unitario</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php while($prod = $productos->fetch_assoc()): ?>
                <tr>
                    <td><?= $prod['nombre_producto'] ?></td>
                    <td><?= $prod['cantidad'] ?></td>
                    <td>$<?= number_format($prod['precio'], 2) ?></td>
                    <td>$<?= number_format($prod['precio'] * $prod['cantidad'], 2) ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        
        <div class="text-right mt-3">
            <a href="listar.php" class="btn btn-secondary">Volver</a>
            <?php if ($pedido['estado'] === 'pendiente'): ?>
                <a href="editar.php?id=<?= $pedido['id'] ?>" class="btn btn-primary">Editar</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$contenido = ob_get_clean();
include('../includes/layout.php');
?>