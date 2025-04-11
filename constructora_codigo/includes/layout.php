<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= $titulo ?? 'Gestión de Pedidos' ?></title>

    <!-- Incluir jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Incluir jQuery UI (para el Datepicker) -->
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

    <!-- Enlace a la hoja de estilos -->
    <link rel="stylesheet" href="/includes/css/estilos.css">

    <!-- Incluir SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            background-color: #f2f2f2;
        }

        .header {
            background-color: #234076;
            color: white;
            padding: 20px 40px;
            font-size: 24px;
            font-weight: bold;
        }

        .contenedor {
            max-width: 1100px;
            margin: 40px auto;
            background-color: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #234076;
            margin-bottom: 20px;
        }

        a.boton {
            display: inline-block;
            background-color: #28a745;
            color: white;
            padding: 10px 15px;
            border-radius: 8px;
            text-decoration: none;
            margin-bottom: 20px;
        }

        a.boton:hover {
            background-color: #218838;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th, td {
            padding: 12px;
            border: 1px solid #ccc;
            text-align: left;
        }

        th {
            background-color: #234076;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        /* Estilos de los estados de los pedidos */
        .estado {
            padding: 5px 12px;
            border-radius: 15px;
            font-weight: 500;
            display: inline-block;
            cursor: pointer;
            transition: 0.3s ease;
            font-size: 14px;
        }

        .estado-pendiente {
            background-color: #f0ad4e;
            color: #fff;
        }

        .estado-finalizado {
            background-color: #5cb85c;
            color: #fff;
        }

        .estado-cancelado {
            background-color: #d9534f;
            color: #fff;
        }

        .estado-select {
            display: none;
            margin-top: 5px;
        }

        /* Estilos del formulario */
        .formulario {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .campo {
            display: flex;
            flex-direction: column;
        }

        .campo label {
            font-weight: bold;
            margin-bottom: 6px;
            color: #234076;
        }

        .campo input,
        .campo select,
        .campo textarea {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
        }

        .campo input:focus,
        .campo select:focus,
        .campo textarea:focus {
            border-color: #234076;
            outline: none;
        }

        textarea {
            resize: vertical;
        }

        .campo.full-width {
            grid-column: 1 / -1;
        }

        button[type="submit"] {
            grid-column: 1 / -1;
            padding: 12px;
            background-color: #234076;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button[type="submit"]:hover {
            background-color: #1a2e57;
        }
    </style>

    <!-- Funciones de la fecha -->
    <script>
    $(function() {
        $("input[name='fecha_pedido']").datepicker({
            dateFormat: 'dd/mm/yy',
            changeMonth: true,
            changeYear: true,
            showAnim: 'slideDown'
        });
    });
    </script>
    
</head>
<body>

<div class="header">
    Sistema de Gestión de Pedidos
</div>

<div class="contenedor">
    <?= $contenido ?>
</div>

</body>
</html>
