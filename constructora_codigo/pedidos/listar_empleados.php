<?php
include('../includes/conexion.php');

// Paginación
$por_pagina = 5;
$pagina = isset($_GET['pagina']) ? (int) $_GET['pagina'] : 1;
$inicio = ($pagina - 1) * $por_pagina;

// Total de registros
$total_resultado = $conexion->query("SELECT COUNT(*) FROM empleados")->fetch_row()[0];
$total_paginas = ceil($total_resultado / $por_pagina);

// Obtener empleados paginados
$resultado = $conexion->query("SELECT * FROM empleados LIMIT $inicio, $por_pagina");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Lista de Empleados</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f0f2f5;
            padding: 30px;
        }
        .contenedor {
            background-color: #fff;
            padding: 25px;
            border-radius: 12px;
            max-width: 700px;
            margin: auto;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        h2 {
            color: #333;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #28a745;
            color: white;
        }
        .paginacion a {
            margin: 0 5px;
            text-decoration: none;
            padding: 6px 12px;
            background-color: #28a745;
            color: white;
            border-radius: 6px;
        }
        .paginacion a:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>

<div class="contenedor">
    <h2>Lista de Empleados</h2>

    <table>
        <tr>
            <th>ID</th>
            <th>Nombre</th>
        </tr>
        <?php while ($fila = $resultado->fetch_assoc()): ?>
        <tr>
            <td><?= $fila['id'] ?></td>
            <td><?= $fila['nombre'] ?></td>
        </tr>
        <?php endwhile; ?>
    </table>

    <div class="paginacion">
        <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
            <a href="?pagina=<?= $i ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
</div>

</body>
</html>
