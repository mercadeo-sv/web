<?php

/* Válida el tiempo de sesión límite*/
include 'politicas_sesion.php';

$nivel_acceso = $_SESSION['nivel_acceso'];
if ($nivel_acceso != 'administrador' && $nivel_acceso != 'publicidad') {
    header('Location: index.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Bases para Publicidad</title>
    <link rel="shortcut icon" type="image/x-icon" href="logo_peq_claro.png">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" />
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
</head>
<body class="sb-nav-fixed">
    <?php include 'top-nav.php'; ?>
    <div id="layoutSidenav">
        <?php include 'sidenav-menu.php'; ?>
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4">
                    <h1 class="mt-4"></h1>

                    <form action="exportar_datos.php" method="post">
                        <div class="mb-3">
                            <label for="tipoBase" class="form-label">Seleccionar Base:</label>
                            <select class="form-select form-select-sm" id="tipoBase" name="tipoBase">
                                <option value="Base Superpack Express">Campaña Superpack Express MCEXP</option>
                                <option value="Base Clientes Tarjeteros">Migración Clientes Tarjetas Raspables a MCEXP</option>
                                <option value="Base Migración 3G">Migración Clientes SIM 3G</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-success">Descargar CSV</button>
                    </form>

                    <!-- Mostrar control de versiones -->
                    <h2 class="mt-5"></h2>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Nombre de la Base</th>
                                <th>Nombre del Usuario</th>
                                <th>IP</th>
                                <th>Fecha de Descarga</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Incluir el archivo de conexión
                            include 'conexion.php';

                            $query = "SELECT * FROM MKT_PREPAGO.DBO.CONTROL_VERSIONES A
                            WHERE ID = (SELECT MAX(ID) FROM MKT_PREPAGO.DBO.CONTROL_VERSIONES
                            WHERE NOMBRE_BASE = A.NOMBRE_BASE)
                            ORDER BY FECHA_DESCARGA DESC";
                            $stmt = sqlsrv_query($conn, $query);

                            if ($stmt) {
                                while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                                    echo "<tr>";
                                    echo "<td>" . $row['NOMBRE_BASE'] . "</td>";
                                    echo "<td>" . $row['NOMBRE_USUARIO'] . "</td>";
                                    echo "<td>" . $row['IP'] . "</td>";
                                    echo "<td>" . $row['FECHA_DESCARGA']->format('Y-m-d H:i:s') . "</td>";
                                    echo "</tr>";
                                }
                            }
                            sqlsrv_free_stmt($stmt);
                            ?>
                        </tbody>
                    </table>

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
    <!-- Bootstrap JS and Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="js/scripts.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
    <script src="assets/demo/chart-area-demo.js"></script>
    <script src="assets/demo/chart-bar-demo.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>
    <script src="js/datatables-simple-demo.js"></script>
</body>
</html>
