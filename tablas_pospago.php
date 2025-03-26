<?php
// Incluye la conexión a la base de datos
include 'conexion.php';
/* Válida el tiempo de sesión límite*/
include 'politicas_sesion.php';
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
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
    <link href="css/styles.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
        <!-- Archivo CSS adicional -->
    <link href="css/custom-styles.css" rel="stylesheet" /> 
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
                    <div class="card mb-4">
                        <div class="card-header">
                            <i class="fas fa-table me-1"></i>Tablas Pospago
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <?php
                                // Consulta para obtener los datos de la tabla
                                $query = "SELECT * FROM MKT_PREPAGO.DBO.TBL_PRODUCTOS_POSPAGO";
                                $result = sqlsrv_query($conn, $query);

                                // Itera sobre los resultados y genera las tablas por grupo
                                while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
                                    echo '<div class="col-md-6 mb-4">';
                                    echo '<table class="table table-bordered">';
                                    echo '<thead><tr><th colspan="2" class="text-center">Datos Principales</th></tr></thead>';
                                    echo '<tbody>';
                                    echo '<tr><td>SKU</td><td>' . htmlspecialchars($row['SKU']) . '</td></tr>';
                                    echo '<tr><td>CODIGO MATERIAL</td><td>' . htmlspecialchars($row['CODIGO_MATERIAL']) . '</td></tr>';
                                    echo '<tr><td>MARCA</td><td>' . htmlspecialchars($row['MARCA']) . '</td></tr>';
                                    echo '<tr><td>MODELO</td><td>' . htmlspecialchars($row['MODELO']) . '</td></tr>';
                                    echo '<tr><td>NOMBRE COMERCIAL</td><td>' . htmlspecialchars($row['NOMBRE_COMERCIAL']) . '</td></tr>';
                                    echo '<tr><td>GAMA</td><td>' . htmlspecialchars($row['GAMA']) . '</td></tr>';
                                    echo '</tbody></table>';
                                    echo '</div>';

                                    echo '<div class="col-md-6 mb-4">';
                                    echo '<table class="table table-bordered">';
                                    echo '<thead><tr><th colspan="2" class="text-center">Detalles de Precios</th></tr></thead>';
                                    echo '<tbody>';
                                    echo '<tr><td>GARANTIA EXTENDIDA</td><td>' . number_format($row['GARANTIA_EXTENDIDA'], 2) . '</td></tr>';
                                    echo '<tr><td>PRECIO NUEVA (18M)</td><td>' . number_format($row['PRECIO_VENTA_NUEVA_18M'], 2) . '</td></tr>';
                                    echo '<tr><td>PRIMA NUEVA (18M)</td><td>' . number_format($row['PRIMA_VENTA_NUEVA_18M'], 2) . '</td></tr>';
                                    echo '<tr><td>PRECIO CROSS (18M)</td><td>' . number_format($row['PRECIO_VENTA_CROSS_PORTA_18M'], 2) . '</td></tr>';
                                    echo '<tr><td>PRIMA CROSS (18M)</td><td>' . number_format($row['PRIMA_VENTA_CROSS_PORTA_18M'], 2) . '</td></tr>';
                                    echo '<tr><td>PRECIO DE LISTA</td><td>' . number_format($row['PRECIO_DE_LISTA'], 2) . '</td></tr>';
                                    echo '</tbody></table>';
                                    echo '</div>';
                                }
                                ?>
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
    <!-- JavaScript de Bootstrap y DataTables -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>
    <script>
        const dataTable = new simpleDatatables.DataTable("#productosTable");
    </script>
</body>
</html>
