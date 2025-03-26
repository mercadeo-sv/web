<?php

/* Válida el tiempo de sesión límite*/
include 'politicas_sesion.php';

$nivel_acceso = $_SESSION['nivel_acceso'];
if ($_SESSION['nivel_acceso'] != 'administrador' && $_SESSION['nivel_acceso'] != 'adquisicion') {
    header('Location: index.php');
    exit();
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
    <title>Catálogo KIT</title>
    <link rel="shortcut icon" type="image/x-icon" href="logo_peq_claro.png">
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
    <link href="css/styles.css" rel="stylesheet" />
    <link href="css/custom-styles.css" rel="stylesheet" /> <!-- Enlace al archivo CSS independiente -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
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
                    <div class="card mb-4">
                        <div class="card-header">
                            <img src="img/telefono.png" alt="Icono de teléfono" style="width: 20px; height: 20px;">
                            Modelos sin Categorizar
                        </div>
                        <div class="card-body">
                            <form action="register_article.php" method="POST" class="row g-3">
                                <div class="col-md-4">
                                    <label for="articuloDespacho" class="form-label">Artículo Despacho</label>
                                    <select class="form-select" id="articuloDespacho" name="articuloDespacho" required>
                                        <?php
                                        // Conexión a la base de datos
                                        include 'conexion.php';
                                        $sql = "SELECT ARTICULO_DESPACHO FROM OPENQUERY([172.23.21.93],'EXEC PREPAGO.DBO.SP_CONSULTA_ART_DESPACHO')";
                                        $stmt = sqlsrv_query($conn, $sql);
                                        if ($stmt) {
                                            while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                                                echo "<option value='" . $row['ARTICULO_DESPACHO'] . "'>" . $row['ARTICULO_DESPACHO'] . "</option>";
                                            }
                                            sqlsrv_free_stmt($stmt);
                                        }
                                        sqlsrv_close($conn);
                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="marca" class="form-label">Marca</label>
                                    <input type="text" class="form-control" id="marca" name="marca" required>
                                </div>
                                <div class="col-md-4">
                                    <label for="modelo" class="form-label">Modelo</label>
                                    <input type="text" class="form-control" id="modelo" name="modelo" required>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-success d-flex align-items-center">
                                        <img src="img/registrar_modelo.png" alt="Ícono registrar" style="width: 24px; height: 24px; margin-right: 8px;">
                                        Registrar
                                    </button>
                                </div>
                            </form>
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
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/5.3.0/js/bootstrap.min.js"></script>
    <script src="js/scripts.js"></script>
</body>
</html>
