<?php

/* Válida el tiempo de sesión límite*/
include 'politicas_sesion.php';

$nivel_acceso = $_SESSION['nivel_acceso'];
if ($nivel_acceso != 'administrador' && $nivel_acceso != 'consumo') {
    header('Location: index.php');
    exit();
}

include 'conexion.php';

$scode = $_POST['scode'];
// Obtener usuarios de la base de datos
$sql = "exec mkt_prepago..sp_select_tbl_voucher ?";
$params = array($scode);
$stmt = sqlsrv_query($conn, $sql, $params);
if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}
$voucher = [];
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $voucher[] = $row;
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
    <title>Consulta Voucher</title>
    <link rel="shortcut icon" type="image/x-icon" href="logo_peq_claro.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="css/styles.css" rel="stylesheet" />
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
    <style>
        .search-container {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 10px;
        }
        .search-container input {
            max-width: 300px;
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
                    <!-- Tabla de Usuarios -->
                    <h1 class="mt-4"></h1>
                    <!-- Formulario de Búsqueda -->
                    <form method="POST" class="mb-4">
                        <div class="search-container">
                            <input type="text" name="scode" class="form-control" placeholder="Ingrese Voucher" required value="<?= htmlspecialchars($scode) ?>">
                            <button type="submit" class="btn btn-success">
                            <img src="img/buscar.png" alt="Ícono registrar" style="width: 24px; height: 24px; margin-right: 5px;">
                            Buscar
                            </button> <!-- Botón en verde a la derecha -->
                        </div>
                    </form>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card mb-8">
                                <div class="card-header">Información Tarjetas Raspables</div>
                                    <div class="card-body">
                                        <?php if ($voucher): ?>
                                            <table class="table table-bordered">
                                                <thead>
                                                <tr>
                                                    <th>No. Voucher</th>
                                                    <th>Fecha</th>
                                                    <th>Face Value</th>
                                                    <th>Estatus</th>
                                                    <th>Nombre Comercial</th>
                                                    <th>Teléfono</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <?php foreach ($voucher as $vouchers): ?>
                                                <tr>
                                                    <td><?= $vouchers['VOUCHER'] ?></td>
                                                    <td><?= $vouchers['FECHA_USO']->format('Y-m-d H:i:s') ?></td>
                                                    <td><?= $vouchers['FACEVALUE'] ?></td>
                                                    <td><?= $vouchers['ESTADO'] ?></td>
                                                    <td><?= $vouchers['NOMBRE_COMERCIAL'] ?></td>
                                                    <td><?= $vouchers['USEDBY'] ?></td>
                                                </tr>
                                                <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                    <?php else: ?>
                                        <p>No se encontró información para el voucher ingresado.</p>
                                    <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>                      
            </main>
        </div>
    </div>

    <!-- Bootstrap JS and Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="js/scripts.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>
</body>
</html>
