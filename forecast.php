<?php

/* Válida el tiempo de sesión límite*/
include 'politicas_sesion.php';

$nivel_acceso = $_SESSION['nivel_acceso'];
if ($nivel_acceso != 'administrador' && $nivel_acceso != 'consumo') {
    header('Location: index.php');
    exit();
}

include 'conexion.php';

// Obtener datos de la tabla
$sql = "SELECT FECHA, 
               CONVERT(VARCHAR(6), FECHA, 112) AS IDPERIODO,
               IDCALENDARIO,
               CONVERT(DECIMAL(22,2),PROY) PROY
        FROM MKT_PREPAGO.DBO.PROYECCION
        WHERE CONVERT(VARCHAR(6), FECHA, 112) = CONVERT(VARCHAR(6),GETDATE(),112)";

$stmt = sqlsrv_query($conn, $sql);
if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

$proyecciones = [];
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $proyecciones[] = $row;
}
sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Proyección de Consumo</title>
    <link rel="shortcut icon" type="image/x-icon" href="logo_peq_claro.png">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUa0VI8Y5Q1o7hDu57jCkCjO19A6KxKzT4lvCQu6lH4FfXtY3E7CqGr41z4p" crossorigin="anonymous">
    <link href="css/styles.css" rel="stylesheet" />
    <link href="css/custom-styles.css" rel="stylesheet" />
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
        .alert {
            background-color: green;
            color: white;
        }
        .no-spin::-webkit-inner-spin-button,
        .no-spin::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        .modal-dialog {
            max-width: 80%;
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
                        <div class="alert alert-info" id="alertMessage"><?= $_SESSION['alertMessage'] ?></div>
                        <?php unset($_SESSION['alertMessage']); ?>
                    <?php endif; ?>
                    <div class="card mb-4">
                        <div class="card-header">
                            <img src="img/icono-proy.png" alt="Icono de proyección" style="width: 20px; height: 20px;">
                            Proyección Consumo
                        </div>
                        <div class="card-body">
                            <table id="datatablesSimple" class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>FECHA</th>
                                        <th>IDPERIODO</th>
                                        <th>IDCALENDARIO</th>
                                        <th>PROY</th>
                                        <th class="text-center">EDITAR</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($proyecciones as $proyeccion): ?>
                                    <tr>
                                        <td><?= $proyeccion['FECHA']->format('Y-m-d') ?></td>
                                        <td><?= $proyeccion['IDPERIODO'] ?></td>
                                        <td><?= $proyeccion['IDCALENDARIO'] ?></td>
                                        <td><?= $proyeccion['PROY'] ?></td>
                                        <td class='text-center'>
                                            <button class='edit-btn btn btn-edit' data-bs-toggle='modal' data-bs-target='#editModal' 
                                                    data-idcalendario='<?= $proyeccion['IDCALENDARIO'] ?>'
                                                    data-fecha='<?= $proyeccion['FECHA']->format('Y-m-d') ?>'
                                                    data-proy='<?= $proyeccion['PROY'] ?>'>
                                                <img src='img/editar.png' alt='Edit' style='width: 20px; height: 20px;'> Editar
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

<!-- Modal para editar proyección -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Editar Proyección</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editForm" method="post" action="update_proy.php">
                    <input type="hidden" id="idCalendario" name="idCalendario">
                    <div class="row mb-3">
                        <div class="col">
                            <label for="fecha" class="form-label">Fecha</label>
                            <input type="text" class="form-control" id="fecha" name="fecha" readonly>
                        </div>
                        <div class="col">
                            <label for="proy" class="form-label">Proyección</label>
                            <input type="number" step="0.01" class="form-control no-spin" id="proy" name="proy" required>
                        </div>
                    </div>
                    <div class="d-flex justify-content-center mt-4 mb-0">
                        <button type="submit" class="btn btn-success">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="js/script_proy_pr.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var editBtns = document.querySelectorAll('.edit-btn');
            editBtns.forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var idCalendario = btn.getAttribute('data-idcalendario');
                    var fecha = btn.getAttribute('data-fecha');
                    var proy = btn.getAttribute('data-proy');

                    document.getElementById('editModalLabel').textContent = 'Proyección del día: ' + fecha;
                    document.getElementById('idCalendario').value = idCalendario;
                    document.getElementById('fecha').value = fecha;
                    document.getElementById('proy').value = proy;
                });
            });
        });

        // Ocultar la alerta después de 2 segundos
        setTimeout(function() {
            var alertMessage = document.getElementById('alertMessage');
            if (alertMessage) {
                alertMessage.style.display = 'none';
            }
        }, 2000);
    </script>
</body>
</html>
