<?php

session_start();
require 'conexion.php';

if (!isset($_SESSION['nombre_usuario'])) {
    header('Location: login.php');
    exit();
}

// Tiempo de inactividad en segundos
$tiempoInactividad = 900;

if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > $tiempoInactividad)) {
    // El último acceso fue hace más de $tiempoInactividad segundos
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit();
}

$_SESSION['LAST_ACTIVITY'] = time(); // actualizar el tiempo de última actividad

$nivel_acceso = $_SESSION['nivel_acceso'];

if ($nivel_acceso != 'administrador') {
    header('Location: index.php');
    exit();
}

// Consulta a la base de datos para obtener los datos
$query = "SELECT COUNT(*) AS cantidad_lineas, USER_MOD, FECHA_ACTUALIZACION, NOM_ARCHIVO 
          FROM MKT_PREPAGO.DBO.TBL_EXISTENCIAS 
          WHERE FECHA_ACTUALIZACION >= (SELECT MIN(FECHA_ACTUALIZACION) FROM MKT_PREPAGO.DBO.TBL_EXISTENCIAS
          WHERE CONVERT(VARCHAR(10),FECHA_ACTUALIZACION,112) = CONVERT(vARCHAR(10),GETDATE(),112))
          GROUP BY USER_MOD, FECHA_ACTUALIZACION, NOM_ARCHIVO";
$stmt = sqlsrv_query($conn, $query);
if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

$data = [];
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $data[] = $row;
}

sqlsrv_free_stmt($stmt);

// Consulta para la tabla de correos
$queryCorreos = "exec mkt_prepago..sp_select_mail";
$stmtCorreos = sqlsrv_query($conn, $queryCorreos);
$correos = [];
if ($stmtCorreos) {
    while ($row = sqlsrv_fetch_array($stmtCorreos, SQLSRV_FETCH_ASSOC)) {
        $correos[] = $row;
    }
    sqlsrv_free_stmt($stmtCorreos);
} else {
    die(print_r(sqlsrv_errors(), true));
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Existencias SAP</title>
    <link rel="shortcut icon" type="image/x-icon" href="logo_peq_claro.png">
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
    <link href="css/styles.css" rel="stylesheet" />
    <link href="css/custom-styles.css" rel="stylesheet" /> <!-- Enlace al archivo CSS independiente -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <script>
        // Redirigir al usuario después del tiempo de inactividad
        var tiempoInactividad = <?= $tiempoInactividad ?>; // tiempo en segundos
        var tiempoInactividadMs = tiempoInactividad * 1000; // convertir a milisegundos

        setTimeout(function() {
            alert('Su sesión ha expirado.');
            window.location.href = 'login.php';
        }, tiempoInactividadMs);
    </script>
    <style>
        .alert-success {
            background-color: green;
            color: white;
        }
        .alert-error {
            background-color: red;
            color: white;
        }
        .btn-correos { background-color: #00aaff; color: white; } /* Botón celeste para "Correos" */
        .btn-comunicar { background-color: #28a745; color: white; } /* Botón verde para "Comunicar" */
        .btn-bloquear { background-color: red; color: white; } /* Botón rojo para bloquear */
        .btn-desbloquear { background-color: orange; color: white; } /* Botón naranja para desbloquear */
    </style>
</head>
<body class="sb-nav-fixed">
    <?php include 'top-nav.php'; ?>
    <div id="layoutSidenav">
        <?php include 'sidenav-menu.php'; ?>
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4">
                    <h1 class="mt-4"></h1>
                    <?php if (isset($_SESSION['alertMessage'])): ?>
                        <div class="alert alert-dismissible fade show <?= $_SESSION['alertClass'] ?>" role="alert" id="alertMessage">
                            <?= htmlspecialchars($_SESSION['alertMessage']) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php unset($_SESSION['alertMessage']); ?>
                        <?php unset($_SESSION['alertClass']); ?>
                    <?php endif; ?>
                    <div class="card mb-4">
                        <div class="card-header">
                            <img src="img/excel.png" alt="Icono de Excel" style="width: 25px; height: 25px;">
                            Subir Archivo de Existencias
                        </div>
                        <div class="card-body">
                            <form action="upload_existencias.php" method="POST" enctype="multipart/form-data">
                                <div class="mb-3">
                                    <label for="excelFile" class="form-label">Seleccione el archivo Excel</label>
                                    <input class="form-control" type="file" id="excelFile" name="excelFile" accept=".xls,.xlsx" required>
                                </div>
                                <button class="btn btn-success d-flex align-items-center" type="submit">
                                    <img src="img/cargar_excel.png" alt="Ícono subir" style="width: 24px; height: 24px; margin-right: 12px;">
                                    Subir
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Nueva Tabla -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <i class="fas fa-table me-1"></i>
                            Información de Cargas de Existencias
                        </div>
                        <div class="card-body">
                            <?php if (empty($data)): ?>
                                <p>No hay registros disponibles.</p>
                            <?php else: ?>
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Nombre del Archivo</th>
                                            <th>Registros Ingresados</th>
                                            <th>Usuario</th>
                                            <th>Fecha de Carga</th>
                                            <th>Eliminar</th> <!-- Nuevo encabezado para la columna de eliminar -->
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($data as $row): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($row['NOM_ARCHIVO']) ?></td>
                                                <td><?= $row['cantidad_lineas'] ?></td>
                                                <td><?= htmlspecialchars($row['USER_MOD']) ?></td>
                                                <td><?= $row['FECHA_ACTUALIZACION']->format('Y-m-d H:i:s') ?></td>
                                                <td>
                                                    <button class="btn btn-correos" data-bs-toggle="modal" data-bs-target="#correosModal">Correos</button>
                                                    <?php
                                                    if ($_SERVER["REQUEST_METHOD"] == "POST") {
                                                        // Ejecutar el Programador de Tareas de Windows para iniciar el flujo de Power Automate
                                                        shell_exec('schtasks /run /tn "mailExistenciasPortal"');
                                                    }
                                                    ?>
                                                    <form method="post">
                                                        <button type="submit" class="btn btn-comunicar">Comunicar</button>
                                                    </form>
                                                    <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#confirmDeleteModal" data-fecha="<?= $row['FECHA_ACTUALIZACION']->format('Y-m-d H:i:s') ?>">Eliminar</button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php endif; ?>
                        </div>
                    </div>
                    <!-- Modal Correos -->
                    <div class="modal fade" id="correosModal" tabindex="-1" aria-labelledby="correosModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="correosModalLabel">Lista de Correos</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Nombre Completo</th>
                                                <th>Correo</th>
                                                <th>Acción</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($correos as $correo): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($correo['NOMBRE_COMPLETO']) ?></td>
                                                    <td><?= htmlspecialchars($correo['CORREO']) ?></td>
                                                    <td>
                                                        <button class="btn <?= $correo['ESTADO'] == 1 ? 'btn-bloquear' : 'btn-desbloquear' ?>">
                                                            <?= $correo['ESTADO'] == 1 ? 'Bloquear' : 'Desbloquear' ?>
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                    <!-- Formulario para agregar un nuevo correo 
                                    <form action="agregar_correo.php" method="POST">
                                        <div class="mb-3">
                                            <label for="nombreCompleto" class="form-label">Nombre Completo</label>
                                            <input type="text" class="form-control" id="nombreCompleto" name="nombre_completo" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="correo" class="form-label">Correo</label>
                                            <input type="email" class="form-control" id="correo" name="correo" required>
                                        </div>
                                        <button type="submit" class="btn btn-success">Agregar</button>
                                    </form-->
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Modal de Confirmación -->
                    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="confirmDeleteModalLabel">Confirmar Eliminación</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    ¿Estás seguro que deseas eliminar este registro?
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                    <form id="deleteForm" action="eliminar_existencias.php" method="POST">
                                        <input type="hidden" name="fecha_carga" id="fechaCarga">
                                        <button type="submit" class="btn btn-danger">Eliminar</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </main>
            <footer class="py-4 bg-light mt-auto">
                <div class="container-fluid px-4">
                    <div class="d-flex align-items-center justify-content-between small">
                        <div class="text-muted">Mercadeo Prepago - Claro El Salvador</div>
                    </div>
                </div>
            </footer>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://stackpath/bootstrap/5.3.0/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/scripts.js"></script>
    <script>
        // Ocultar la alerta después de 30 segundos
        setTimeout(function() {
            var alertMessage = document.getElementById('alertMessage');
            if (alertMessage) {
                alertMessage.style.display = 'none';
            }
        }, 30000);

        // Manejar el evento cuando se muestra el modal
        var confirmDeleteModal = document.getElementById('confirmDeleteModal');
        confirmDeleteModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget; // Botón que disparó el modal
            var fechaCarga = button.getAttribute('data-fecha'); // Extraer la información de los atributos data-*
            var modalBodyInput = document.getElementById('fechaCarga'); // Asignar a un input hidden dentro del modal
            modalBodyInput.value = fechaCarga;
        });
    </script>
</body>
</html>