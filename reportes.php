<?php

/* Válida el tiempo de sesión límite*/
include 'politicas_sesion.php';

$nivel_acceso = $_SESSION['nivel_acceso'];
if ($nivel_acceso != 'administrador') {
    header('Location: index.php');
    exit();
}

require 'conexion.php';

// Consulta para obtener los datos de la tabla
$sql = "exec mkt_prepago.dbo.sp_consulta_reportes";
$resultado = sqlsrv_query($conn, $sql);

if ($resultado === false) {
    die(print_r(sqlsrv_errors(), true));
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Reportes Prepago</title>
    <link rel="shortcut icon" type="image/x-icon" href="logo_peq_claro.png">
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
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
        .cuadro-naranja { background-color: orange; padding: 5px; color: white; border-radius: 5px; }
        .cuadro-verde { background-color: green; padding: 5px; color: white; border-radius: 5px; }
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
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Reporte</th>
                                    <th>Un día atrás</th>
                                    <th>Dos días atrás</th>
                                    <th>Tres días atrás</th>
                                    <th>Estatus</th>
                                    <th>Fecha</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($fila = sqlsrv_fetch_array($resultado, SQLSRV_FETCH_ASSOC)) { 
                                    // Verificar si el estado es 'EJECUTANDO'
                                    $disabled = ($fila['ESTADO'] == 'EJECUTANDO') ? 'disabled' : '';
                                ?>
                                    <tr>
                                        <td><?= htmlspecialchars($fila['REPORTE']) ?></td>
                                        
                                        <!-- Botón 1 -->
                                        <td>
                                            <form action="confirmacion_reproceso.php" method="GET">
                                                <button type="submit" name="sp" value="<?= htmlspecialchars($fila['SP1']) ?>" class="btn btn-warning" <?= $disabled ?>>
                                                    <?= isset($fila['DATE_PR1']) ? $fila['DATE_PR1']->format('Y-m-d') : '' ?>
                                                </button>
                                            </form>
                                        </td>

                                        <!-- Botón 2 -->
                                        <td>
                                            <form action="confirmacion_reproceso.php" method="GET">
                                                <button type="submit" name="sp" value="<?= htmlspecialchars($fila['SP2']) ?>" class="btn btn-warning" <?= $disabled ?>>
                                                    <?= isset($fila['DATE_PR2']) ? $fila['DATE_PR2']->format('Y-m-d') : '' ?>
                                                </button>
                                            </form>
                                        </td>

                                        <!-- Botón 3 -->
                                        <td>
                                            <form action="confirmacion_reproceso.php" method="GET">
                                                <button type="submit" name="sp" value="<?= htmlspecialchars($fila['SP3']) ?>" class="btn btn-warning" <?= $disabled ?>>
                                                    <?= isset($fila['DATE_PR3']) ? $fila['DATE_PR3']->format('Y-m-d') : '' ?>
                                                </button>
                                            </form>
                                        </td>

                                        <td>
                                            <?php if ($fila['ESTADO'] == 'EJECUTANDO') { ?>
                                                <div class="cuadro-naranja"><?= htmlspecialchars($fila['ESTADO']) ?></div>
                                            <?php } elseif ($fila['ESTADO'] == 'FINALIZADO') { ?>
                                                <div class="cuadro-verde"><?= htmlspecialchars($fila['ESTADO']) ?></div>
                                            <?php } else { ?>
                                                <?= htmlspecialchars($fila['ESTADO']) ?>
                                            <?php } ?>
                                        </td>
                                        <td><?= htmlspecialchars($fila['FECHA']) ?></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
</body>

</html>

<?php
sqlsrv_close($conn);
?>
