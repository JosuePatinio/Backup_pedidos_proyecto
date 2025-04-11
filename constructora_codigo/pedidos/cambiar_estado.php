<?php
include('../includes/conexion.php');

$data = json_decode(file_get_contents('php://input'), true);

$id_pedido = $data['id_pedido'];
$estado = $data['estado'];

$query = "UPDATE pedidos SET estado = ? WHERE id = ?";
$stmt = $conexion->prepare($query);
$stmt->bind_param("si", $estado, $id_pedido);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}
?>
