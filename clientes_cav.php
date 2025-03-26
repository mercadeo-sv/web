<?php

/* Válida el tiempo de sesión límite*/
include 'politicas_sesion.php';

$nivel_acceso = $_SESSION['nivel_acceso'];
if ($nivel_acceso != 'administrador') {
    header('Location: index.php');
    exit();
}

include 'conexion.php';

// Consulta a la base de datos para obtener los datos
$query = "exec mkt_prepago..sp_select_tbl_cargas_cav
";
$stmt = sqlsrv_query($conn, $query);
if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

$data = [];
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $data[] = $row;
}

sqlsrv_free_stmt($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Portal Mercadeo</title>
    <link rel="shortcut icon" type="image/x-icon" href="logo_peq_claro.png">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/styles.css" rel="stylesheet" />
    <link href="css/custom-styles.css" rel="stylesheet" /> <!-- Enlace al archivo CSS independiente -->
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
        <!-- Estilos de AlertifyJS -->
    <link href="css/styles-alertify.css" rel="stylesheet" /> 
    <link rel="stylesheet" href="assets/alertifyjs/css/alertify.min.css">
    <link rel="stylesheet" href="assets/alertifyjs/css/themes/default.min.css">
    <script src="assets/alertifyjs/alertify.min.js"></script>
    <script>
    setTimeout(function() {
        alertify.alert('Aviso', 'Su sesión ha expirado.',
        function() {
            window.location.href = 'login.php';}
        );
    }, <?= $tiempoInactividad * 1000 ?>);
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
                            Subir Archivo de MarketShare
                        </div>
                        <div class="card-body">
                            <form action="carga_cav.php" method="POST" enctype="multipart/form-data">
                                <div class="mb-3">
                                    <label for="txtFile" class="form-label">Seleccione el archivo de texto</label>
                                    <input class="form-control" type="file" id="txtFile" name="txtFile" accept=".txt" required>
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
                            Información de Archivos Cargados
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
                                                <td><?= htmlspecialchars($row['MOD_USER']) ?></td>
                                                <td><?= $row['FECHA_ACTUALIZACION']->format('Y-m-d H:i:s') ?></td>
                                                <td>
                                                    <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#confirmDeleteModal" data-fecha="<?= $row['FECHA_ACTUALIZACION']->format('Y-m-d H:i:s') ?>">Eliminar</button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php endif; ?>
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
                                    <form id="deleteForm" action="eliminar_cav.php" method="POST">
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
